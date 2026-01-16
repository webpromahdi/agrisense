-- ============================================================
-- AgriSense - Update Crop Units to KG
-- Run this script to update existing crops from 'maund' to 'kg'
-- ============================================================

USE agrisense;

-- Update all crops to use 'kg' as unit
UPDATE crops SET unit = 'kg' WHERE unit IN ('maund', 'quintal');

-- Verify the update
SELECT crop_id, crop_name, unit FROM crops;
