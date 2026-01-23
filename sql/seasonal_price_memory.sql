-- ============================================================
-- AgriSense - Seasonal Price Memory Query
-- One-Season Reminder (Year-over-Year Comparison)
-- ============================================================
-- Logic:
-- 1. Get current period price from price_history
-- 2. Get same period last year using self-join
-- 3. Calculate percentage change
-- 4. Show direction indicator (Up/Down/Stable)
-- ============================================================

-- Query: Compare current month prices with same month last year
-- Parameters: :market_id (optional)

SELECT 
    c.crop_id,
    c.crop_name,
    c.category,
    m.market_name,
    r.region_name,
    -- Current period (this year, same month)
    ROUND(AVG(current_period.price), 2) AS current_price,
    -- Last year same period
    ROUND(AVG(last_year.price), 2) AS last_year_price,
    -- Percentage change
    ROUND(
        ((AVG(current_period.price) - AVG(last_year.price)) / NULLIF(AVG(last_year.price), 0)) * 100,
        2
    ) AS percent_change,
    -- Direction indicator
    CASE 
        WHEN AVG(current_period.price) > AVG(last_year.price) * 1.05 THEN 'UP'
        WHEN AVG(current_period.price) < AVG(last_year.price) * 0.95 THEN 'DOWN'
        ELSE 'STABLE'
    END AS direction,
    -- Additional context
    COUNT(DISTINCT current_period.history_id) AS current_records,
    COUNT(DISTINCT last_year.history_id) AS last_year_records
FROM 
    crops c
    JOIN price_history current_period ON c.crop_id = current_period.crop_id
    JOIN markets m ON current_period.market_id = m.market_id
    JOIN regions r ON m.region_id = r.region_id
    LEFT JOIN price_history last_year 
        ON current_period.crop_id = last_year.crop_id
        AND current_period.market_id = last_year.market_id
        AND MONTH(current_period.record_date) = MONTH(last_year.record_date)
        AND YEAR(current_period.record_date) = YEAR(last_year.record_date) + 1
WHERE 
    -- Current year data
    YEAR(current_period.record_date) = YEAR(CURDATE())
    AND MONTH(current_period.record_date) = MONTH(CURDATE())
    -- Optional market filter
    -- AND m.market_id = :market_id
GROUP BY 
    c.crop_id,
    c.crop_name,
    c.category,
    m.market_id,
    m.market_name,
    r.region_name
HAVING 
    last_year_price IS NOT NULL
ORDER BY 
    ABS(percent_change) DESC;

-- ============================================================
-- Alternative: Compare with same week last year
-- ============================================================
/*
SELECT 
    c.crop_name,
    m.market_name,
    ROUND(AVG(current_period.price), 2) AS current_price,
    ROUND(AVG(last_year.price), 2) AS last_year_price,
    ROUND(
        ((AVG(current_period.price) - AVG(last_year.price)) / NULLIF(AVG(last_year.price), 0)) * 100,
        2
    ) AS percent_change
FROM 
    crops c
    JOIN price_history current_period ON c.crop_id = current_period.crop_id
    JOIN markets m ON current_period.market_id = m.market_id
    LEFT JOIN price_history last_year 
        ON current_period.crop_id = last_year.crop_id
        AND current_period.market_id = last_year.market_id
        AND WEEK(current_period.record_date) = WEEK(last_year.record_date)
        AND YEAR(current_period.record_date) = YEAR(last_year.record_date) + 1
WHERE 
    YEAR(current_period.record_date) = YEAR(CURDATE())
    AND WEEK(current_period.record_date) = WEEK(CURDATE())
GROUP BY 
    c.crop_id, c.crop_name, m.market_id, m.market_name
HAVING 
    last_year_price IS NOT NULL;
*/
