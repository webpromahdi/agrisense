-- ============================================================
-- AgriSense - Farmer Code Schema Update
-- Adds unique 6-digit verification code to farmers table
-- ============================================================

USE agrisense;

-- ============================================================
-- Add farmer_code column to existing farmers table
-- ============================================================
ALTER TABLE farmers 
ADD COLUMN farmer_code VARCHAR(6) UNIQUE AFTER contact_number;

-- ============================================================
-- Generate unique 6-digit codes for existing farmers
-- Using farmer_id + 100000 to ensure 6-digit codes
-- ============================================================
UPDATE farmers 
SET farmer_code = LPAD(farmer_id + 100000, 6, '0')
WHERE farmer_code IS NULL;

-- ============================================================
-- Make farmer_code NOT NULL after populating existing records
-- ============================================================
ALTER TABLE farmers 
MODIFY COLUMN farmer_code VARCHAR(6) NOT NULL UNIQUE;

-- ============================================================
-- END OF FARMER CODE SCHEMA UPDATE
-- ============================================================
