-- =========================================================
-- AGRISENSE UPGRADE SCRIPT: ADVANCED AGGREGATION FEATURES
-- =========================================================

-- ---------------------------------------------------------
-- PHASE 1: DATABASE SCHEMA EXTENSION
-- ---------------------------------------------------------

-- Add new columns to virtual_coop_deals
ALTER TABLE virtual_coop_deals
ADD COLUMN logistics_cost DECIMAL(12,2) DEFAULT 0,
ADD COLUMN carbon_saved DECIMAL(12,2) DEFAULT 0;

-- Add cost_share to deal_participants
ALTER TABLE deal_participants
ADD COLUMN cost_share DECIMAL(12,2) DEFAULT 0;

-- Create System Constants Table
CREATE TABLE IF NOT EXISTS system_constants (
    constant_key VARCHAR(100) PRIMARY KEY,
    constant_value DECIMAL(12,4)
);

-- Insert Default Values
INSERT IGNORE INTO system_constants (constant_key, constant_value) VALUES 
('diesel_emission_factor', 2.64),
('logistics_rate_per_unit', 2.50),
('individual_trip_km', 10.00),
('aggregated_trip_km', 15.00);

-- ---------------------------------------------------------
-- PHASE 2 & 3: STORED PROCEDURES
-- ---------------------------------------------------------

DELIMITER //

-- Drop existing procedures to avoid conflicts
DROP PROCEDURE IF EXISTS sp_allocate_costs //
DROP PROCEDURE IF EXISTS sp_auto_aggregate_order //

-- ---------------------------------------------------------
-- PROCEDURE: sp_allocate_costs
-- Calculates and distributes logistics costs among participants
-- ---------------------------------------------------------
CREATE PROCEDURE sp_allocate_costs(IN p_deal_id INT)
BEGIN
    DECLARE v_total_qty DECIMAL(10,2);
    DECLARE v_total_farmers INT;
    DECLARE v_logistics_cost DECIMAL(12,2);
    DECLARE v_rate DECIMAL(10,2);

    -- Exit handler for errors
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- 1. Get Deal Stats
    SELECT aggregated_quantity INTO v_total_qty
    FROM virtual_coop_deals WHERE deal_id = p_deal_id;

    SELECT COUNT(*) INTO v_total_farmers
    FROM deal_participants WHERE deal_id = p_deal_id;

    -- Fetch Rate (Default 2.5 if missing)
    SELECT COALESCE(MAX(constant_value), 2.50) INTO v_rate 
    FROM system_constants WHERE constant_key = 'logistics_rate_per_unit';

    -- 2. Calculate Total Logistics Cost
    IF v_total_qty > 0 THEN
        SET v_logistics_cost = v_total_qty * v_rate;
    ELSE
        SET v_logistics_cost = 0;
    END IF;

    -- Update Deal Record
    UPDATE virtual_coop_deals 
    SET logistics_cost = v_logistics_cost 
    WHERE deal_id = p_deal_id;

    -- 3. Distribute Costs (50% Volume Weighted, 50% Equal Split)
    IF v_total_farmers > 0 AND v_total_qty > 0 THEN
        UPDATE deal_participants
        SET cost_share = (
            (0.5 * (quantity_allocated / v_total_qty) * v_logistics_cost) + 
            (0.5 * (1 / v_total_farmers) * v_logistics_cost)
        )
        WHERE deal_id = p_deal_id;
    END IF;

    COMMIT;
END //

-- ---------------------------------------------------------
-- PROCEDURE: sp_auto_aggregate_order
-- Main Aggregation Logic + Carbon Calculation Integration
-- ---------------------------------------------------------
CREATE PROCEDURE sp_auto_aggregate_order(
    IN p_crop_id INT,
    IN p_market_id INT,
    IN p_target_quantity DECIMAL(10,2)
)
BEGIN
    DECLARE v_deal_id INT;
    DECLARE v_remaining_qty DECIMAL(10,2);
    DECLARE v_farmer_id INT;
    DECLARE v_supply_qty DECIMAL(10,2);
    DECLARE v_take_qty DECIMAL(10,2);
    DECLARE v_aggregated_qty DECIMAL(10,2) DEFAULT 0;
    
    -- Variables for Carbon Calculation
    DECLARE v_total_farmers INT DEFAULT 0;
    DECLARE v_individual_dist_per_farmer DECIMAL(10,2);
    DECLARE v_aggregated_dist DECIMAL(10,2);
    DECLARE v_emission_factor DECIMAL(10,2);
    DECLARE v_total_individual_dist DECIMAL(10,2);
    DECLARE v_carbon_saved DECIMAL(12,2);
    
    DECLARE done INT DEFAULT FALSE;
    
    -- Cursor to fetch available supply
    DECLARE supply_cursor CURSOR FOR 
        SELECT farmer_id, quantity 
        FROM market_supply 
        WHERE crop_id = p_crop_id AND market_id = p_market_id AND quantity > 0
        ORDER BY created_at ASC;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- 1. Create New Deal Record
    INSERT INTO virtual_coop_deals (crop_id, market_id, target_quantity, status)
    VALUES (p_crop_id, p_market_id, p_target_quantity, 'pending');
    
    SET v_deal_id = LAST_INSERT_ID();
    SET v_remaining_qty = p_target_quantity;
    
    -- 2. Process Supply
    OPEN supply_cursor;
    
    read_loop: LOOP
        FETCH supply_cursor INTO v_farmer_id, v_supply_qty;
        IF done OR v_remaining_qty <= 0 THEN
            LEAVE read_loop;
        END IF;
        
        -- Determine how much to take
        IF v_supply_qty >= v_remaining_qty THEN
            SET v_take_qty = v_remaining_qty;
        ELSE
            SET v_take_qty = v_supply_qty;
        END IF;
        
        -- Add to participants
        INSERT INTO deal_participants (deal_id, farmer_id, quantity_allocated)
        VALUES (v_deal_id, v_farmer_id, v_take_qty);
        
        -- Deduct from market supply
        UPDATE market_supply 
        SET quantity = quantity - v_take_qty 
        WHERE farmer_id = v_farmer_id AND crop_id = p_crop_id AND market_id = p_market_id AND quantity = v_supply_qty
        LIMIT 1;
        
        SET v_remaining_qty = v_remaining_qty - v_take_qty;
        SET v_aggregated_qty = v_aggregated_qty + v_take_qty;
        
    END LOOP;
    
    CLOSE supply_cursor;
    
    -- 3. Update Deal Status
    IF v_aggregated_qty >= p_target_quantity THEN
        UPDATE virtual_coop_deals SET status = 'fulfilled', aggregated_quantity = v_aggregated_qty WHERE deal_id = v_deal_id;
    ELSE
        UPDATE virtual_coop_deals SET status = 'partial', aggregated_quantity = v_aggregated_qty WHERE deal_id = v_deal_id;
    END IF;

    -- -----------------------------------------------------
    -- CARBON IMPACT MODULE
    -- -----------------------------------------------------
    
    -- Get Constants (with defaults)
    SELECT COALESCE(MAX(constant_value), 10.00) INTO v_individual_dist_per_farmer 
    FROM system_constants WHERE constant_key = 'individual_trip_km';

    SELECT COALESCE(MAX(constant_value), 15.00) INTO v_aggregated_dist 
    FROM system_constants WHERE constant_key = 'aggregated_trip_km';

    SELECT COALESCE(MAX(constant_value), 2.64) INTO v_emission_factor 
    FROM system_constants WHERE constant_key = 'diesel_emission_factor';

    -- Get Participant Count
    SELECT COUNT(*) INTO v_total_farmers FROM deal_participants WHERE deal_id = v_deal_id;

    -- Compute Carbon Saved
    -- Logic: (Farmers * 10km) - 15km = Distance Saved
    -- Carbon = Distance Saved * Factor * 0.1 (conversion scaling)
    
    SET v_total_individual_dist = v_total_farmers * v_individual_dist_per_farmer;
    
    IF v_total_individual_dist > v_aggregated_dist THEN
        SET v_carbon_saved = (v_total_individual_dist - v_aggregated_dist) * v_emission_factor * 0.1;
    ELSE
        SET v_carbon_saved = 0;
    END IF;

    UPDATE virtual_coop_deals 
    SET carbon_saved = v_carbon_saved 
    WHERE deal_id = v_deal_id;

    COMMIT;

    -- -----------------------------------------------------
    -- TRIGGER FAIR COST ALLOCATION
    -- -----------------------------------------------------
    -- We call this AFTER commit of the main transaction to ensure data is visible, 
    -- but since we are in the same session, we can call it directly.
    -- Ideally, keep it in the transaction or call it after.
    -- Calling it here is fine as sp_allocate_costs has its own transaction handling.
    
    CALL sp_allocate_costs(v_deal_id);

END //

DELIMITER ;
