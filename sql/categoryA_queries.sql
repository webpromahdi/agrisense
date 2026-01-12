-- ============================================================
-- AgriSense - Category A: Market Intelligence SQL Queries
-- All analytical queries demonstrating SQL as intelligence engine
-- For Bangladesh Agricultural Markets
-- Prices in Bangladeshi Taka (BDT), Quantities in Maund
-- ============================================================

-- ============================================================
-- FEATURE A1: PRICE ANOMALY DETECTION
-- ============================================================
-- Goal: Detect crops whose current market price deviates more 
--       than ±20% from the average price across all markets.
--
-- SQL Concepts Used:
--   - Subquery for calculating average price per crop
--   - AVG() aggregate function
--   - GROUP BY for crop-wise aggregation
--   - Calculated deviation percentage
--   - HAVING clause alternative using WHERE with subquery
--
-- Output Columns:
--   - Crop Name, Market Name, Current Price, Average Price, Deviation %
-- ============================================================

SELECT 
    c.crop_name AS 'Crop Name',
    m.market_name AS 'Market Name',
    mp.current_price AS 'Current Price (₹)',
    ROUND(avg_prices.avg_price, 2) AS 'Average Price (₹)',
    ROUND(
        ((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) * 100, 
        2
    ) AS 'Deviation (%)'
FROM 
    market_prices mp
    JOIN crops c ON mp.crop_id = c.crop_id
    JOIN markets m ON mp.market_id = m.market_id
    JOIN (
        -- Subquery: Calculate average price for each crop across all markets
        SELECT 
            crop_id,
            AVG(current_price) AS avg_price
        FROM 
            market_prices
        GROUP BY 
            crop_id
    ) avg_prices ON mp.crop_id = avg_prices.crop_id
WHERE 
    -- Filter: Deviation exceeds ±20%
    ABS((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) > 0.20
ORDER BY 
    ABS((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) DESC;


-- ============================================================
-- FEATURE A2: INTER-MARKET PRICE GAP ANALYSIS
-- ============================================================
-- Goal: Identify crops where price difference between two 
--       selected markets is significant.
--
-- SQL Concepts Used:
--   - Self JOIN on market_prices table
--   - Multiple JOINs with crops and markets tables
--   - ABS() for absolute price difference
--   - Parameterized query (uses :market_a_id and :market_b_id)
--
-- Output Columns:
--   - Crop Name, Market A Name, Market A Price, 
--     Market B Name, Market B Price, Price Gap
-- ============================================================

SELECT 
    c.crop_name AS 'Crop Name',
    ma.market_name AS 'Market A',
    mp_a.current_price AS 'Market A Price (₹)',
    mb.market_name AS 'Market B',
    mp_b.current_price AS 'Market B Price (₹)',
    ABS(mp_a.current_price - mp_b.current_price) AS 'Price Gap (₹)',
    ROUND(
        (ABS(mp_a.current_price - mp_b.current_price) / 
         LEAST(mp_a.current_price, mp_b.current_price)) * 100,
        2
    ) AS 'Gap Percentage (%)'
FROM 
    market_prices mp_a
    -- Self JOIN: Same crop in different market
    JOIN market_prices mp_b 
        ON mp_a.crop_id = mp_b.crop_id 
        AND mp_a.market_id != mp_b.market_id
    JOIN crops c ON mp_a.crop_id = c.crop_id
    JOIN markets ma ON mp_a.market_id = ma.market_id
    JOIN markets mb ON mp_b.market_id = mb.market_id
WHERE 
    mp_a.market_id = :market_a_id
    AND mp_b.market_id = :market_b_id
ORDER BY 
    ABS(mp_a.current_price - mp_b.current_price) DESC;


-- ============================================================
-- FEATURE A3: HISTORICAL PRICE TREND ANALYSIS
-- ============================================================
-- Goal: Show month-wise price trends for a selected crop 
--       using historical data.
--
-- SQL Concepts Used:
--   - DATE_FORMAT() for month extraction
--   - GROUP BY month for time-series aggregation
--   - AVG() for average monthly price
--   - ORDER BY for chronological sorting
--   - Parameterized query (uses :crop_id)
--
-- Output Columns:
--   - Month, Average Price, Total Quantity Sold
-- ============================================================

SELECT 
    DATE_FORMAT(ph.record_date, '%Y-%m') AS 'Month',
    DATE_FORMAT(ph.record_date, '%M %Y') AS 'Month Name',
    ROUND(AVG(ph.price), 2) AS 'Average Price (₹)',
    SUM(ph.quantity_sold) AS 'Total Quantity Sold',
    MIN(ph.price) AS 'Min Price (₹)',
    MAX(ph.price) AS 'Max Price (₹)'
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
-- FEATURE A4: MARKET SATURATION INDEX
-- ============================================================
-- Goal: Identify markets where supply is too high, 
--       indicating possible price drops.
--
-- SQL Concepts Used:
--   - SUM() for total supply quantity
--   - COUNT(DISTINCT) for unique farmer count
--   - Calculated saturation index (supply per farmer ratio)
--   - GROUP BY market for aggregation
--   - JOIN with markets and regions
--
-- Saturation Index Formula:
--   Saturation Index = Total Supply / Number of Unique Farmers
--   Higher index = More concentrated supply = Higher saturation
--
-- Output Columns:
--   - Market Name, Region, Total Supply, Number of Farmers, 
--     Saturation Index
-- ============================================================

SELECT 
    m.market_name AS 'Market Name',
    r.region_name AS 'Region',
    c.crop_name AS 'Crop',
    SUM(ms.quantity) AS 'Total Supply (Quintals)',
    COUNT(DISTINCT ms.farmer_id) AS 'Number of Farmers',
    ROUND(
        SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id), 
        2
    ) AS 'Saturation Index',
    CASE 
        WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 150 THEN 'HIGH'
        WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 100 THEN 'MEDIUM'
        ELSE 'LOW'
    END AS 'Saturation Level'
FROM 
    market_supply ms
    JOIN markets m ON ms.market_id = m.market_id
    JOIN regions r ON m.region_id = r.region_id
    JOIN crops c ON ms.crop_id = c.crop_id
GROUP BY 
    m.market_id, 
    m.market_name, 
    r.region_name,
    c.crop_id,
    c.crop_name
ORDER BY 
    SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) DESC;


-- ============================================================
-- FEATURE A5: MOST PROFITABLE CROP BY REGION
-- ============================================================
-- Goal: Determine which crop generated the highest revenue 
--       in each region.
--
-- SQL Concepts Used:
--   - SUM(quantity × price) for revenue calculation
--   - GROUP BY region for regional aggregation
--   - Subquery with MAX() for finding top revenue per region
--   - Multiple JOINs across regions, markets, crops
--
-- Output Columns:
--   - Region, Crop Name, Total Revenue
-- ============================================================

-- Method: Using subquery to find max revenue per region
SELECT 
    region_revenue.region_name AS 'Region',
    region_revenue.state AS 'State',
    region_revenue.crop_name AS 'Top Crop',
    region_revenue.total_revenue AS 'Total Revenue (₹)',
    region_revenue.total_quantity AS 'Quantity Sold (Quintals)'
FROM (
    -- Subquery: Calculate revenue for each crop in each region
    SELECT 
        r.region_id,
        r.region_name,
        r.state,
        c.crop_id,
        c.crop_name,
        SUM(ms.quantity * ms.price_per_unit) AS total_revenue,
        SUM(ms.quantity) AS total_quantity
    FROM 
        market_supply ms
        JOIN markets m ON ms.market_id = m.market_id
        JOIN regions r ON m.region_id = r.region_id
        JOIN crops c ON ms.crop_id = c.crop_id
    GROUP BY 
        r.region_id, 
        r.region_name,
        r.state,
        c.crop_id, 
        c.crop_name
) region_revenue
WHERE 
    region_revenue.total_revenue = (
        -- Subquery: Find maximum revenue for this region
        SELECT MAX(inner_rev.total_revenue)
        FROM (
            SELECT 
                r2.region_id,
                SUM(ms2.quantity * ms2.price_per_unit) AS total_revenue
            FROM 
                market_supply ms2
                JOIN markets m2 ON ms2.market_id = m2.market_id
                JOIN regions r2 ON m2.region_id = r2.region_id
            GROUP BY 
                r2.region_id, 
                ms2.crop_id
        ) inner_rev
        WHERE inner_rev.region_id = region_revenue.region_id
    )
ORDER BY 
    region_revenue.total_revenue DESC;


-- ============================================================
-- END OF CATEGORY A QUERIES
-- ============================================================
