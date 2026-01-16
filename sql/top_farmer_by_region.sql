-- ============================================================
-- AgriSense - Top Performing Farmer by Region
-- SQL Query for identifying highest revenue farmer per region
-- ============================================================

-- ============================================================
-- QUERY: Top Performing Farmer by Region
-- ============================================================
-- Business Logic:
--   Revenue = SUM(quantity × price_per_unit)
--   For each region, select the farmer with highest total revenue
--
-- Tables Used:
--   - farmers (farmer_id, farmer_name, region_id)
--   - regions (region_id, region_name, state)
--   - market_supply (farmer_id, quantity, price_per_unit)
--
-- Approach:
--   1. Calculate total revenue per farmer
--   2. Rank farmers within each region by revenue
--   3. Select only the top farmer (rank = 1) per region
-- ============================================================

SELECT 
    farmer_revenue.region_name,
    farmer_revenue.state,
    farmer_revenue.farmer_name,
    farmer_revenue.total_revenue,
    farmer_revenue.total_quantity,
    farmer_revenue.supply_count
FROM (
    -- Subquery: Calculate revenue per farmer with their region
    SELECT 
        r.region_id,
        r.region_name,
        r.state,
        f.farmer_id,
        f.farmer_name,
        SUM(ms.quantity * ms.price_per_unit) AS total_revenue,
        SUM(ms.quantity) AS total_quantity,
        COUNT(ms.supply_id) AS supply_count
    FROM 
        market_supply ms
        JOIN farmers f ON ms.farmer_id = f.farmer_id
        JOIN regions r ON f.region_id = r.region_id
    GROUP BY 
        r.region_id,
        r.region_name,
        r.state,
        f.farmer_id,
        f.farmer_name
) farmer_revenue
WHERE 
    -- Select only farmers with maximum revenue in their region
    farmer_revenue.total_revenue = (
        SELECT MAX(inner_rev.total_revenue)
        FROM (
            SELECT 
                f2.region_id,
                f2.farmer_id,
                SUM(ms2.quantity * ms2.price_per_unit) AS total_revenue
            FROM 
                market_supply ms2
                JOIN farmers f2 ON ms2.farmer_id = f2.farmer_id
            GROUP BY 
                f2.region_id,
                f2.farmer_id
        ) inner_rev
        WHERE inner_rev.region_id = farmer_revenue.region_id
    )
ORDER BY 
    farmer_revenue.total_revenue DESC;

-- ============================================================
-- END OF QUERY
-- ============================================================
