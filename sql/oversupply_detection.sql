-- ============================================================
-- AgriSense - Crop Over-Supply Detection Query
-- Abnormal Supply Growth Detection (NOT Prediction)
-- ============================================================
-- Logic:
-- 1. Calculate total supply in last 30 days per crop
-- 2. Calculate historical average supply per crop
-- 3. Compare and flag crops where recent supply > avg by threshold
-- 4. This is DETECTION of current anomalies, NOT future prediction
-- ============================================================

-- Query: Detect crops with abnormal recent supply growth
-- Parameters: :threshold_percent (e.g., 40 for 40% above average)

SELECT 
    c.crop_id,
    c.crop_name,
    c.category,
    -- Recent 30-day supply
    COALESCE(recent.recent_supply, 0) AS recent_supply,
    -- Historical average (excluding recent 30 days)
    COALESCE(ROUND(historical.avg_monthly_supply, 2), 0) AS avg_supply,
    -- Growth percentage
    ROUND(
        ((COALESCE(recent.recent_supply, 0) - COALESCE(historical.avg_monthly_supply, 0)) 
         / NULLIF(historical.avg_monthly_supply, 0)) * 100,
        2
    ) AS growth_percent,
    -- Farmer count
    COALESCE(recent.farmer_count, 0) AS recent_farmers,
    -- Risk label based on threshold (default 40%)
    CASE 
        WHEN ((COALESCE(recent.recent_supply, 0) - COALESCE(historical.avg_monthly_supply, 0)) 
              / NULLIF(historical.avg_monthly_supply, 0)) * 100 > 40 
        THEN 'HIGH'
        WHEN ((COALESCE(recent.recent_supply, 0) - COALESCE(historical.avg_monthly_supply, 0)) 
              / NULLIF(historical.avg_monthly_supply, 0)) * 100 > 20 
        THEN 'ELEVATED'
        ELSE 'NORMAL'
    END AS risk_label
FROM 
    crops c
    -- Recent 30-day supply subquery
    LEFT JOIN (
        SELECT 
            crop_id,
            SUM(quantity) AS recent_supply,
            COUNT(DISTINCT farmer_id) AS farmer_count
        FROM 
            market_supply
        WHERE 
            supply_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY 
            crop_id
    ) recent ON c.crop_id = recent.crop_id
    -- Historical average subquery (monthly average before last 30 days)
    LEFT JOIN (
        SELECT 
            crop_id,
            AVG(monthly_total) AS avg_monthly_supply
        FROM (
            SELECT 
                crop_id,
                DATE_FORMAT(supply_date, '%Y-%m') AS month_key,
                SUM(quantity) AS monthly_total
            FROM 
                market_supply
            WHERE 
                supply_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY 
                crop_id, DATE_FORMAT(supply_date, '%Y-%m')
        ) monthly_data
        GROUP BY 
            crop_id
    ) historical ON c.crop_id = historical.crop_id
WHERE 
    recent.recent_supply IS NOT NULL
    OR historical.avg_monthly_supply IS NOT NULL
HAVING 
    growth_percent IS NOT NULL
ORDER BY 
    growth_percent DESC;

-- ============================================================
-- Simpler version with configurable threshold
-- ============================================================
/*
SELECT 
    c.crop_name,
    SUM(CASE WHEN ms.supply_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
             THEN ms.quantity ELSE 0 END) AS recent_supply,
    AVG(ms.quantity) AS avg_supply,
    COUNT(DISTINCT ms.farmer_id) AS farmer_count
FROM 
    crops c
    JOIN market_supply ms ON c.crop_id = ms.crop_id
GROUP BY 
    c.crop_id, c.crop_name
HAVING 
    recent_supply > avg_supply * (1 + :threshold_percent / 100)
ORDER BY 
    recent_supply DESC;
*/
