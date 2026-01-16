-- ============================================================
-- AgriSense - Inter-Market Price Gap Analysis
-- ============================================================
-- Goal: Compare prices of the same crop across different markets
--       and identify significant price differences.
--
-- SQL Concepts Used:
--   - Self JOIN on market_prices table (mp_a and mp_b)
--   - Multiple JOINs with crops and markets tables
--   - ABS() for absolute price difference calculation
--   - Calculated field for price gap percentage
--   - ORDER BY price gap (descending) to show largest gaps first
--
-- Input Parameter:
--   :crop_id - The crop to analyze across markets
--
-- Output Columns:
--   - Crop Name
--   - Market A Name, Market A Price
--   - Market B Name, Market B Price
--   - Price Gap (absolute difference)
--   - Gap Percentage
-- ============================================================

SELECT 
    c.crop_name AS 'Crop Name',
    ma.market_name AS 'Market A',
    mp_a.current_price AS 'Market A Price (৳)',
    mb.market_name AS 'Market B',
    mp_b.current_price AS 'Market B Price (৳)',
    ABS(mp_a.current_price - mp_b.current_price) AS 'Price Gap (৳)',
    ROUND(
        (ABS(mp_a.current_price - mp_b.current_price) / 
         LEAST(mp_a.current_price, mp_b.current_price)) * 100,
        2
    ) AS 'Gap Percentage (%)'
FROM 
    market_prices mp_a
    -- Self JOIN: Compare same crop across different markets
    JOIN market_prices mp_b 
        ON mp_a.crop_id = mp_b.crop_id 
        AND mp_a.market_id < mp_b.market_id  -- Avoid duplicates (A-B, not B-A)
    JOIN crops c ON mp_a.crop_id = c.crop_id
    JOIN markets ma ON mp_a.market_id = ma.market_id
    JOIN markets mb ON mp_b.market_id = mb.market_id
WHERE 
    mp_a.crop_id = :crop_id
ORDER BY 
    ABS(mp_a.current_price - mp_b.current_price) DESC;

-- ============================================================
-- END OF INTER-MARKET PRICE GAP ANALYSIS QUERY
-- ============================================================
