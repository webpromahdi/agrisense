-- ============================================================
-- AgriSense - Smart Market Recommendation Query
-- SQL-Based Market Ranking (NOT AI/ML)
-- ============================================================
-- Logic:
-- 1. Join markets, market_prices, and market_supply tables
-- 2. Calculate average price per market (higher = better)
-- 3. Calculate saturation index (supply/farmers - lower = better)
-- 4. Rank markets using ORDER BY
-- 5. Return top 3 markets for selling a specific crop
-- ============================================================

-- Query: Get best markets for a specific crop
-- Parameters: :crop_id (the crop to analyze)

SELECT 
    m.market_id,
    m.market_name,
    r.region_name,
    ROUND(AVG(mp.current_price), 2) AS avg_price,
    COALESCE(
        ROUND(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 2), 
        0
    ) AS saturation_index,
    COUNT(DISTINCT ms.farmer_id) AS active_farmers,
    COALESCE(SUM(ms.quantity), 0) AS total_supply,
    -- Recommendation score: Higher price and lower saturation = better
    -- Score = (avg_price / 100) - (saturation_index / 10)
    ROUND(
        (AVG(mp.current_price) / 100) - (COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) / 10),
        2
    ) AS recommendation_score,
    CASE 
        WHEN COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) < 50 
            AND AVG(mp.current_price) > (
                SELECT AVG(mp2.current_price) 
                FROM market_prices mp2 
                WHERE mp2.crop_id = mp.crop_id
            )
        THEN 'Highly Recommended'
        WHEN COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) < 100
        THEN 'Recommended'
        WHEN COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) < 150
        THEN 'Consider'
        ELSE 'Saturated'
    END AS recommendation_note
FROM 
    markets m
    JOIN regions r ON m.region_id = r.region_id
    LEFT JOIN market_prices mp ON m.market_id = mp.market_id AND mp.crop_id = :crop_id
    LEFT JOIN market_supply ms ON m.market_id = ms.market_id AND ms.crop_id = :crop_id
WHERE 
    mp.crop_id = :crop_id
GROUP BY 
    m.market_id, 
    m.market_name, 
    r.region_name,
    mp.crop_id
ORDER BY 
    recommendation_score DESC,
    avg_price DESC,
    saturation_index ASC
LIMIT 3;

-- ============================================================
-- Alternative: Get all markets ranked (without LIMIT)
-- ============================================================
/*
SELECT 
    m.market_id,
    m.market_name,
    r.region_name,
    c.crop_name,
    ROUND(AVG(mp.current_price), 2) AS avg_price,
    COALESCE(
        ROUND(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 2), 
        0
    ) AS saturation_index,
    ROW_NUMBER() OVER (
        ORDER BY 
            (AVG(mp.current_price) / 100) - (COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) / 10) DESC
    ) AS market_rank
FROM 
    markets m
    JOIN regions r ON m.region_id = r.region_id
    JOIN crops c ON c.crop_id = :crop_id
    LEFT JOIN market_prices mp ON m.market_id = mp.market_id AND mp.crop_id = :crop_id
    LEFT JOIN market_supply ms ON m.market_id = ms.market_id AND ms.crop_id = :crop_id
WHERE 
    mp.crop_id IS NOT NULL
GROUP BY 
    m.market_id, m.market_name, r.region_name, c.crop_name
ORDER BY 
    market_rank;
*/
