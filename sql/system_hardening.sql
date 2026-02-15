-- =========================================================
-- AGRISENSE UPGRADE: FINAL SYSTEM HARDENING & POLISH
-- =========================================================

DELIMITER //

-- ---------------------------------------------------------
-- PROCEDURE: sp_cancel_deal
-- Handles safe cancellation and supply release
-- ---------------------------------------------------------
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

    -- Validate Deal
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

    -- Update Deal Status
    UPDATE virtual_coop_deals 
    SET status = 'cancelled' 
    WHERE deal_id = p_deal_id;

    -- Restore Supply Status
    UPDATE market_supply ms
    JOIN deal_participants dp ON ms.supply_id = dp.supply_id
    SET ms.status = 'available'
    WHERE dp.deal_id = p_deal_id;

    -- Reset Cost Share (Optional Cleanup)
    UPDATE deal_participants 
    SET cost_share = 0 
    WHERE deal_id = p_deal_id;

    COMMIT;
END //

-- ---------------------------------------------------------
-- PROCEDURE: sp_finalize_deal
-- Marks deal and supply as SOLD
-- ---------------------------------------------------------
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

    -- Validate Deal
    SELECT status INTO v_current_status 
    FROM virtual_coop_deals 
    WHERE deal_id = p_deal_id FOR UPDATE;

    IF v_current_status != 'fulfilled' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only fulfilled deals can be finalized';
    END IF;

    -- Update Deal Status
    UPDATE virtual_coop_deals 
    SET status = 'sold' 
    WHERE deal_id = p_deal_id;

    -- Update Supply Status
    UPDATE market_supply ms
    JOIN deal_participants dp ON ms.supply_id = dp.supply_id
    SET ms.status = 'sold'
    WHERE dp.deal_id = p_deal_id;

    COMMIT;
END //

DELIMITER ;

-- ---------------------------------------------------------
-- PERFORMANCE INDEXES (IF NOT EXISTS)
-- ---------------------------------------------------------

-- Note: MariaDB 10.4 supports IF NOT EXISTS for indexes
CREATE INDEX IF NOT EXISTS idx_market_supply_composite 
ON market_supply(crop_id, market_id, status, supply_date);

CREATE INDEX IF NOT EXISTS idx_participants_deal 
ON deal_participants(deal_id);

CREATE INDEX IF NOT EXISTS idx_deals_status_date 
ON virtual_coop_deals(status, created_at);

-- ---------------------------------------------------------
-- UPDATE STORED PROCEDURE CURSORS (FOR UPDATE)
-- ---------------------------------------------------------
-- We do not automatically recreate the main sp_auto_aggregate_order here 
-- to avoid overwriting recent custom logic unless explicitly requested.
-- Ideally, the cursor in sp_auto_aggregate_order should use "FOR UPDATE" 
-- in the SELECT statement within the cursor definition for strict row locking.
