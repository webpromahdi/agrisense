-- ============================================================
-- AGRISENSE - SCHEMA UPDATE PATCH (SAFE MIGRATION)
-- Run this on an EXISTING database to sync with schema.sql
-- Compatible with: MariaDB 10.4+
-- Date: February 16, 2026
-- ============================================================

-- ============================================================
-- STEP 1: UPDATE ENUM VALUE FOR virtual_coop_deals.status
-- Add 'sold' value for finalized deals
-- ============================================================
ALTER TABLE virtual_coop_deals 
MODIFY COLUMN status ENUM('pending','fulfilled','partial','cancelled','sold') 
DEFAULT 'pending';

-- ============================================================
-- STEP 2: UPDATE DEFAULT VALUE FOR deal_participants.cost_share
-- Change from NULL to 0 for consistency
-- ============================================================
ALTER TABLE deal_participants 
MODIFY COLUMN cost_share DECIMAL(12,2) DEFAULT 0;

-- ============================================================
-- STEP 3: ADD MISSING COLUMNS (IF NOT EXISTS)
-- These use MariaDB 10.4+ syntax with IF NOT EXISTS
-- ============================================================

-- Add status column to market_supply if missing
ALTER TABLE market_supply 
ADD COLUMN IF NOT EXISTS status ENUM('available','reserved','sold') 
DEFAULT 'available' 
AFTER supply_date;

-- Add logistics_cost to virtual_coop_deals if missing
ALTER TABLE virtual_coop_deals
ADD COLUMN IF NOT EXISTS logistics_cost DECIMAL(12,2) DEFAULT 0;

-- Add carbon_saved to virtual_coop_deals if missing
ALTER TABLE virtual_coop_deals
ADD COLUMN IF NOT EXISTS carbon_saved DECIMAL(12,2) DEFAULT 0;

-- ============================================================
-- STEP 4: CREATE SYSTEM_CONSTANTS TABLE IF NOT EXISTS
-- ============================================================
CREATE TABLE IF NOT EXISTS system_constants (
    constant_key VARCHAR(100) PRIMARY KEY,
    constant_value DECIMAL(12,4)
);

-- Insert default constants (IGNORE if exists)
INSERT IGNORE INTO system_constants (constant_key, constant_value) VALUES 
('diesel_emission_factor', 2.64),
('logistics_rate_per_unit', 2.50),
('individual_trip_km', 10.00),
('aggregated_trip_km', 15.00);

-- ============================================================
-- STEP 5: ENSURE INDEXES EXIST
-- Note: CREATE INDEX will fail if index already exists
-- These are wrapped in individual statements for safety
-- ============================================================

-- Index for aggregation queries (may already exist)
-- CREATE INDEX idx_market_supply_aggregation ON market_supply(crop_id, supply_date, status);

-- Index for deal filtering (may already exist)
-- CREATE INDEX idx_deals_region_status ON virtual_coop_deals(region_id, status);

-- Safe index creation with IF NOT EXISTS (MariaDB 10.4+)
CREATE INDEX IF NOT EXISTS idx_market_supply_composite 
ON market_supply(crop_id, market_id, status, supply_date);

CREATE INDEX IF NOT EXISTS idx_participants_deal 
ON deal_participants(deal_id);

CREATE INDEX IF NOT EXISTS idx_deals_status_date 
ON virtual_coop_deals(status, created_at);

-- ============================================================
-- STEP 6: RECREATE STORED PROCEDURES
-- These DROP and CREATE to ensure latest version
-- ============================================================

DELIMITER //

DROP PROCEDURE IF EXISTS sp_allocate_costs //
CREATE PROCEDURE sp_allocate_costs(IN p_deal_id INT)
BEGIN
    DECLARE v_total_qty DECIMAL(12,2);
    DECLARE v_total_farmers INT;
    DECLARE v_logistics_cost DECIMAL(12,2);
    DECLARE v_rate DECIMAL(10,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT COALESCE(aggregated_quantity, 0) INTO v_total_qty
    FROM virtual_coop_deals WHERE deal_id = p_deal_id;

    SELECT COUNT(*) INTO v_total_farmers
    FROM deal_participants WHERE deal_id = p_deal_id;

    SELECT COALESCE(MAX(constant_value), 2.50) INTO v_rate 
    FROM system_constants WHERE constant_key = 'logistics_rate_per_unit';

    IF v_total_qty > 0 THEN
        SET v_logistics_cost = v_total_qty * v_rate;
    ELSE
        SET v_logistics_cost = 0;
    END IF;

    UPDATE virtual_coop_deals 
    SET logistics_cost = v_logistics_cost 
    WHERE deal_id = p_deal_id;

    IF v_total_farmers > 0 AND v_total_qty > 0 THEN
        UPDATE deal_participants
        SET cost_share = ROUND(
            (0.5 * (quantity_allocated / v_total_qty) * v_logistics_cost) + 
            (0.5 * (1.0 / v_total_farmers) * v_logistics_cost)
        , 2)
        WHERE deal_id = p_deal_id;
    END IF;

    COMMIT;
END //

DROP PROCEDURE IF EXISTS sp_auto_aggregate_order //
CREATE PROCEDURE sp_auto_aggregate_order(
    IN p_crop_id INT,
    IN p_market_id INT,
    IN p_target_quantity DECIMAL(12,2)
)
BEGIN
    DECLARE v_region_id INT;
    DECLARE v_deal_id INT;
    DECLARE v_aggregated_qty DECIMAL(12,2) DEFAULT 0;
    DECLARE v_supply_id INT;
    DECLARE v_farmer_id INT;
    DECLARE v_supply_quantity DECIMAL(12,2);
    
    DECLARE v_total_farmers INT DEFAULT 0;
    DECLARE v_individual_dist_per_farmer DECIMAL(10,2);
    DECLARE v_aggregated_dist DECIMAL(10,2);
    DECLARE v_emission_factor DECIMAL(10,2);
    DECLARE v_total_individual_dist DECIMAL(10,2);
    DECLARE v_carbon_saved DECIMAL(12,2);

    DECLARE done INT DEFAULT 0;

    DECLARE cur_supply CURSOR FOR
        SELECT supply_id, farmer_id, quantity
        FROM market_supply
        WHERE crop_id = p_crop_id
          AND market_id = p_market_id
          AND status = 'available'
        ORDER BY supply_date ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT region_id INTO v_region_id
    FROM markets
    WHERE market_id = p_market_id
    LIMIT 1;

    IF v_region_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Market not found';
    END IF;

    INSERT INTO virtual_coop_deals (
        crop_id, market_id, region_id, target_quantity, aggregated_quantity, status, logistics_cost, carbon_saved
    ) VALUES (
        p_crop_id, p_market_id, v_region_id, p_target_quantity, 0, 'pending', 0, 0
    );
    
    SET v_deal_id = LAST_INSERT_ID();

    OPEN cur_supply;

    read_loop: LOOP
        FETCH cur_supply INTO v_supply_id, v_farmer_id, v_supply_quantity;

        IF done THEN
            LEAVE read_loop;
        END IF;

        IF v_aggregated_qty >= p_target_quantity THEN
            LEAVE read_loop;
        END IF;

        INSERT INTO deal_participants (
            deal_id, farmer_id, supply_id, quantity_allocated, cost_share
        ) VALUES (
            v_deal_id, v_farmer_id, v_supply_id, v_supply_quantity, 0
        );

        UPDATE market_supply 
        SET status = 'reserved' 
        WHERE supply_id = v_supply_id;

        SET v_aggregated_qty = v_aggregated_qty + v_supply_quantity;

    END LOOP;

    CLOSE cur_supply;

    UPDATE virtual_coop_deals
    SET aggregated_quantity = v_aggregated_qty,
        status = CASE 
            WHEN v_aggregated_qty >= p_target_quantity THEN 'fulfilled'
            ELSE 'partial'
        END
    WHERE deal_id = v_deal_id;

    SELECT COALESCE(MAX(constant_value), 10.00) INTO v_individual_dist_per_farmer 
    FROM system_constants WHERE constant_key = 'individual_trip_km';

    SELECT COALESCE(MAX(constant_value), 15.00) INTO v_aggregated_dist 
    FROM system_constants WHERE constant_key = 'aggregated_trip_km';

    SELECT COALESCE(MAX(constant_value), 2.64) INTO v_emission_factor 
    FROM system_constants WHERE constant_key = 'diesel_emission_factor';

    SELECT COUNT(*) INTO v_total_farmers FROM deal_participants WHERE deal_id = v_deal_id;

    SET v_total_individual_dist = v_total_farmers * v_individual_dist_per_farmer;
    
    IF v_total_individual_dist > v_aggregated_dist THEN
        SET v_carbon_saved = ROUND((v_total_individual_dist - v_aggregated_dist) * v_emission_factor * 0.1, 2);
    ELSE
        SET v_carbon_saved = 0;
    END IF;

    UPDATE virtual_coop_deals 
    SET carbon_saved = v_carbon_saved 
    WHERE deal_id = v_deal_id;

    COMMIT;

    CALL sp_allocate_costs(v_deal_id);

END //

DROP PROCEDURE IF EXISTS sp_cancel_deal //
CREATE PROCEDURE sp_cancel_deal(IN p_deal_id INT)
BEGIN
    DECLARE v_current_status VARCHAR(20);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT status INTO v_current_status 
    FROM virtual_coop_deals 
    WHERE deal_id = p_deal_id FOR UPDATE;

    IF v_current_status IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deal not found';
    END IF;

    IF v_current_status = 'cancelled' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deal already cancelled';
    END IF;

    IF v_current_status = 'sold' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot cancel sold deal';
    END IF;

    UPDATE virtual_coop_deals 
    SET status = 'cancelled' 
    WHERE deal_id = p_deal_id;

    UPDATE market_supply ms
    JOIN deal_participants dp ON ms.supply_id = dp.supply_id
    SET ms.status = 'available'
    WHERE dp.deal_id = p_deal_id;

    UPDATE deal_participants 
    SET cost_share = 0 
    WHERE deal_id = p_deal_id;

    COMMIT;
END //

DROP PROCEDURE IF EXISTS sp_finalize_deal //
CREATE PROCEDURE sp_finalize_deal(IN p_deal_id INT)
BEGIN
    DECLARE v_current_status VARCHAR(20);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT status INTO v_current_status 
    FROM virtual_coop_deals 
    WHERE deal_id = p_deal_id FOR UPDATE;

    IF v_current_status != 'fulfilled' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only fulfilled deals can be finalized';
    END IF;

    UPDATE virtual_coop_deals 
    SET status = 'sold' 
    WHERE deal_id = p_deal_id;

    UPDATE market_supply ms
    JOIN deal_participants dp ON ms.supply_id = dp.supply_id
    SET ms.status = 'sold'
    WHERE dp.deal_id = p_deal_id;

    COMMIT;
END //

DELIMITER ;

-- ============================================================
-- VERIFICATION QUERIES (Run these to confirm migration)
-- ============================================================

-- Check virtual_coop_deals ENUM includes 'sold':
-- SHOW COLUMNS FROM virtual_coop_deals LIKE 'status';

-- Check deal_participants.cost_share default:
-- SHOW COLUMNS FROM deal_participants LIKE 'cost_share';

-- Check procedures exist:
-- SHOW PROCEDURE STATUS WHERE Db = 'agrisense';

-- ============================================================
-- END OF PATCH
-- ============================================================
