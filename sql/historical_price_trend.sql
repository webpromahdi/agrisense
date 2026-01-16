-- ============================================================
-- AgriSense - Historical Price Trend Analysis
-- ============================================================
-- Goal: Analyze month-wise price trends of a crop using 
--       historical price data.
--
-- SQL Concepts Used:
--   - price_history table for historical data
--   - DATE_FORMAT() for month extraction and grouping
--   - GROUP BY MONTH for time-series aggregation
--   - AVG() aggregate function for average monthly price
--   - MIN(), MAX() for price range per month
--   - SUM() for total quantity sold
--   - ORDER BY month for chronological sorting
--
-- Input Parameter:
--   :crop_id - The crop to analyze trends for
--
-- Output Columns:
--   - Month (YYYY-MM format)
--   - Month Name (e.g., "January 2025")
--   - Average Price
--   - Min Price, Max Price
--   - Total Quantity Sold
-- ============================================================

SELECT 
    DATE_FORMAT(ph.record_date, '%Y-%m') AS 'Month',
    DATE_FORMAT(ph.record_date, '%M %Y') AS 'Month Name',
    ROUND(AVG(ph.price), 2) AS 'Average Price (৳)',
    MIN(ph.price) AS 'Min Price (৳)',
    MAX(ph.price) AS 'Max Price (৳)',
    SUM(ph.quantity_sold) AS 'Total Quantity Sold'
FROM 
    price_history ph
    JOIN crops c ON ph.crop_id = c.crop_id
WHERE 
    ph.crop_id = :crop_id
GROUP BY 
    DATE_FORMAT(ph.record_date, '%Y-%m'),
    DATE_FORMAT(ph.record_date, '%M %Y')
ORDER BY 
    DATE_FORMAT(ph.record_date, '%Y-%m') ASC;

-- ============================================================
-- END OF HISTORICAL PRICE TREND ANALYSIS QUERY
-- ============================================================
