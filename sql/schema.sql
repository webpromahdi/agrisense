-- ============================================================
-- AgriSense - Agricultural Market Intelligence Database
-- Database Schema (Structure Only)
-- ============================================================
-- Usage:
--   1. Run this file first to create tables
--   2. Run seed_data.sql to populate with sample data
-- ============================================================
DROP DATABASE agrisense;
-- Create Database
CREATE DATABASE IF NOT EXISTS agrisense;
USE agrisense;

-- ============================================================
-- TABLE: regions
-- Stores geographical regions for market analysis
-- ============================================================
CREATE TABLE IF NOT EXISTS regions (
    region_id INT PRIMARY KEY AUTO_INCREMENT,
    region_name VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: markets
-- Stores market/mandi information
-- ============================================================
CREATE TABLE IF NOT EXISTS markets (
    market_id INT PRIMARY KEY AUTO_INCREMENT,
    market_name VARCHAR(150) NOT NULL,
    region_id INT NOT NULL,
    location VARCHAR(200),
    market_type ENUM('wholesale', 'retail', 'both') DEFAULT 'wholesale',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

-- ============================================================
-- TABLE: crops
-- Stores crop/commodity information
-- ============================================================
CREATE TABLE IF NOT EXISTS crops (
    crop_id INT PRIMARY KEY AUTO_INCREMENT,
    crop_name VARCHAR(100) NOT NULL,
    category ENUM('grain', 'vegetable', 'fruit', 'pulse', 'oilseed', 'spice') NOT NULL,
    unit VARCHAR(20) DEFAULT 'kg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: farmers
-- Stores farmer information for supply tracking
-- ============================================================
CREATE TABLE IF NOT EXISTS farmers (
    farmer_id INT PRIMARY KEY AUTO_INCREMENT,
    farmer_name VARCHAR(150) NOT NULL,
    region_id INT NOT NULL,
    contact_number VARCHAR(15),
    farmer_code VARCHAR(6) NOT NULL UNIQUE,
    farm_size_acres DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

-- ============================================================
-- TABLE: market_prices
-- Current market prices for crops (main price table)
-- ============================================================
CREATE TABLE IF NOT EXISTS market_prices (
    price_id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    market_id INT NOT NULL,
    current_price DECIMAL(10,2) NOT NULL,
    min_price DECIMAL(10,2),
    max_price DECIMAL(10,2),
    price_date DATE NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id)
);

-- ============================================================
-- TABLE: price_history
-- Historical price data for trend analysis
-- ============================================================
CREATE TABLE IF NOT EXISTS price_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    market_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity_sold DECIMAL(12,2),
    record_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id)
);

-- ============================================================
-- TABLE: market_supply
-- Tracks supply from farmers to markets
-- ============================================================
CREATE TABLE IF NOT EXISTS market_supply (
    supply_id INT PRIMARY KEY AUTO_INCREMENT,
    farmer_id INT NOT NULL,
    market_id INT NOT NULL,
    crop_id INT NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    supply_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id),
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id)
);

-- ============================================================
-- TABLE: users
-- Stores user authentication and profile data
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: climate_risk
-- Static climate risk advisory information per region
-- ============================================================
CREATE TABLE IF NOT EXISTS climate_risk (
    risk_id INT PRIMARY KEY AUTO_INCREMENT,
    region_id INT NOT NULL,
    risk_type ENUM('Flood', 'Salinity', 'Drought', 'Cyclone', 'Waterlogging') NOT NULL,
    severity ENUM('Low', 'Moderate', 'High', 'Critical') DEFAULT 'Moderate',
    advisory_text VARCHAR(500) NOT NULL,
    season VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

-- ============================================================
-- DACE-Lite Advanced Aggregation Module (PHASE 1)
-- ============================================================

-- 1. Add Status Column to market_supply
ALTER TABLE market_supply 
ADD COLUMN IF NOT EXISTS status ENUM('available','reserved','sold') 
DEFAULT 'available' 
AFTER supply_date;

-- 2. Create Table: virtual_coop_deals
CREATE TABLE IF NOT EXISTS virtual_coop_deals (
    deal_id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    market_id INT NOT NULL,
    region_id INT NOT NULL,
    target_quantity DECIMAL(12,2) NOT NULL,
    aggregated_quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','fulfilled','partial','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id),
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Table: deal_participants
CREATE TABLE IF NOT EXISTS deal_participants (
    participant_id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    farmer_id INT NOT NULL,
    supply_id INT NOT NULL,
    quantity_allocated DECIMAL(12,2) NOT NULL,
    cost_share DECIMAL(12,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deal_id) REFERENCES virtual_coop_deals(deal_id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id),
    FOREIGN KEY (supply_id) REFERENCES market_supply(supply_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Indexes
CREATE INDEX idx_market_supply_aggregation ON market_supply(crop_id, supply_date, status);
CREATE INDEX idx_deals_region_status ON virtual_coop_deals(region_id, status);

-- ============================================================
-- DACE-Lite Advanced Aggregation Module (PHASE 2 - Procedure)
-- ============================================================

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

-- ============================================================
-- END OF SCHEMA
-- ============================================================
