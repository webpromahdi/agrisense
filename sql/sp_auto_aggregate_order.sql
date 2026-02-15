DELIMITER //

DROP PROCEDURE IF EXISTS sp_auto_aggregate_order //

CREATE PROCEDURE sp_auto_aggregate_order(
    IN p_crop_id INT,
    IN p_market_id INT,
    IN p_target_quantity DECIMAL(12,2)
)
BEGIN
    -- =============================================
    -- Variable Declarations
    -- =============================================
    DECLARE v_region_id INT;
    DECLARE v_deal_id INT;
    DECLARE v_running_total DECIMAL(12,2) DEFAULT 0;
    DECLARE v_supply_id INT;
    DECLARE v_farmer_id INT;
    DECLARE v_supply_quantity DECIMAL(12,2);
    DECLARE done INT DEFAULT 0;

    -- =============================================
    -- Cursor Declaration
    -- =============================================
    -- Cursor for fetching available supply (oldest first - FIFO)
    DECLARE cur_supply CURSOR FOR
        SELECT supply_id, farmer_id, quantity
        FROM market_supply
        WHERE crop_id = p_crop_id
          AND market_id = p_market_id
          AND status = 'available'
        ORDER BY supply_date ASC;

    -- =============================================
    -- Handler Declarations
    -- =============================================
    -- Continue handler for cursor NOT FOUND
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- EXIT Handler for SQLEXCEPTION
    -- Rolls back transaction and raises a standardized error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Aggregation failed. Transaction rolled back.';
    END;

    -- =============================================
    -- 1. Validation
    -- =============================================
    IF p_target_quantity <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Target quantity must be greater than 0';
    END IF;

    -- Start Transaction Scope
    START TRANSACTION;

    -- =============================================
    -- 2. Get Region ID & Validate Market
    -- =============================================
    SELECT region_id INTO v_region_id
    FROM markets
    WHERE market_id = p_market_id
    LIMIT 1;

    IF v_region_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Market not found';
    END IF;

    -- =============================================
    -- 3. Create Initial Virtual Deal
    -- =============================================
    INSERT INTO virtual_coop_deals (
        crop_id, 
        market_id, 
        region_id, 
        target_quantity, 
        aggregated_quantity, 
        status
    ) VALUES (
        p_crop_id, 
        p_market_id, 
        v_region_id, 
        p_target_quantity, 
        0, 
        'pending'
    );
    
    SET v_deal_id = LAST_INSERT_ID();

    -- =============================================
    -- 4-7. Aggregation Loop
    -- =============================================
    OPEN cur_supply;

    read_loop: LOOP
        FETCH cur_supply INTO v_supply_id, v_farmer_id, v_supply_quantity;

        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Check if target is met (Whole-batch allocation logic)
        IF v_running_total >= p_target_quantity THEN
            LEAVE read_loop;
        END IF;

        -- Process Supply
        INSERT INTO deal_participants (
            deal_id, 
            farmer_id, 
            supply_id, 
            quantity_allocated
        ) VALUES (
            v_deal_id, 
            v_farmer_id, 
            v_supply_id, 
            v_supply_quantity
        );

        -- Mark as Reserved
        UPDATE market_supply 
        SET status = 'reserved' 
        WHERE supply_id = v_supply_id;

        -- Update Total
        SET v_running_total = v_running_total + v_supply_quantity;

    END LOOP;

    CLOSE cur_supply;

    -- =============================================
    -- 8. Finalize Deal Status
    -- =============================================
    UPDATE virtual_coop_deals
    SET aggregated_quantity = v_running_total,
        status = CASE 
            WHEN v_running_total >= p_target_quantity THEN 'fulfilled'
            ELSE 'partial'
        END
    WHERE deal_id = v_deal_id;

    -- Commit Changes
    COMMIT;

END //

DELIMITER ;
