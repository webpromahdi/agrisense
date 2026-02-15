-- ============================================================
-- PHASE 1: DACE-Lite Aggregation Module Migration
-- Target Database: agrisense
-- Database Version Compatibility: MariaDB 10.4
-- ============================================================

-- ============================================================
-- STEP 1: Add Status Column to market_supply
-- ============================================================
-- Adds 'status' enum to track supply lifecycle.
-- Uses IF NOT EXISTS to prevent errors if the column was already added.
ALTER TABLE market_supply 
ADD COLUMN IF NOT EXISTS status ENUM('available','reserved','sold') 
DEFAULT 'available' 
AFTER supply_date;

-- ============================================================
-- STEP 2: Create Table virtual_coop_deals
-- ============================================================
-- Stores aggregated bulk order deals.
CREATE TABLE IF NOT EXISTS virtual_coop_deals (
    deal_id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    market_id INT NOT NULL,
    region_id INT NOT NULL,
    target_quantity DECIMAL(12,2) NOT NULL,
    aggregated_quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','fulfilled','partial','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    CONSTRAINT fk_deals_crop FOREIGN KEY (crop_id) REFERENCES crops(crop_id),
    CONSTRAINT fk_deals_market FOREIGN KEY (market_id) REFERENCES markets(market_id),
    CONSTRAINT fk_deals_region FOREIGN KEY (region_id) REFERENCES regions(region_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STEP 3: Create Table deal_participants
-- ============================================================
-- Tracks farmers contributing to each deal.
CREATE TABLE IF NOT EXISTS deal_participants (
    participant_id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    farmer_id INT NOT NULL,
    supply_id INT NOT NULL,
    quantity_allocated DECIMAL(12,2) NOT NULL,
    cost_share DECIMAL(12,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    -- ON DELETE CASCADE for deal_id ensures participants are removed if the deal is deleted.
    CONSTRAINT fk_participants_deal FOREIGN KEY (deal_id) REFERENCES virtual_coop_deals(deal_id) ON DELETE CASCADE,
    CONSTRAINT fk_participants_farmer FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id),
    CONSTRAINT fk_participants_supply FOREIGN KEY (supply_id) REFERENCES market_supply(supply_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STEP 4: Index Optimization
-- ============================================================

-- 1. Index on market_supply (crop_id, supply_date, status)
-- Optimizes queries filtering by crop, date, and status.
CREATE INDEX IF NOT EXISTS idx_market_supply_aggregation 
ON market_supply(crop_id, supply_date, status);

-- 2. Index on virtual_coop_deals (region_id, status)
-- Optimizes filtering deals by region and status.
CREATE INDEX IF NOT EXISTS idx_deals_region_status 
ON virtual_coop_deals(region_id, status);

-- NOTE: Index on farmers (region_id)
-- The 'farmers' table already has a foreign key on 'region_id', which automatically
-- creates an index in InnoDB. Creating another index on 'region_id' would be redundant.
-- If explicitly required: CREATE INDEX IF NOT EXISTS idx_farmers_region ON farmers(region_id);
