-- ============================================================
-- AgriSense - DACE-Lite Feature Removal Cleanup Script
-- Run this ONCE on your existing database to remove all DACE objects
-- ============================================================

-- 1. Drop Stored Procedures
DROP PROCEDURE IF EXISTS sp_allocate_costs;
DROP PROCEDURE IF EXISTS sp_auto_aggregate_order;
DROP PROCEDURE IF EXISTS sp_cancel_deal;
DROP PROCEDURE IF EXISTS sp_finalize_deal;

-- 2. Drop DACE-related Indexes (suppress errors if not exist)
DROP INDEX IF EXISTS idx_market_supply_aggregation ON market_supply;
DROP INDEX IF EXISTS idx_market_supply_composite ON market_supply;
DROP INDEX IF EXISTS idx_participants_deal ON deal_participants;
DROP INDEX IF EXISTS idx_deals_region_status ON virtual_coop_deals;
DROP INDEX IF EXISTS idx_deals_status_date ON virtual_coop_deals;

-- 3. Drop Tables (order matters due to foreign keys)
DROP TABLE IF EXISTS deal_participants;
DROP TABLE IF EXISTS virtual_coop_deals;
DROP TABLE IF EXISTS system_constants;

-- 4. Remove 'status' column from market_supply (added by DACE)
ALTER TABLE market_supply DROP COLUMN IF EXISTS status;

-- ============================================================
-- DONE! The DACE-Lite feature has been fully removed from the database.
-- ============================================================
