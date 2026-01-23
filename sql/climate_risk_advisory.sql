-- ============================================================
-- AgriSense - Climate Risk Advisory
-- Static Dashboard Information (NO API, NO Prediction)
-- ============================================================
-- This is informational data ONLY
-- No real-time data, no weather API, no predictions
-- Just static region-wise climate risk information
-- ============================================================

-- Create the climate_risk table
CREATE TABLE IF NOT EXISTS climate_risk (
    risk_id INT PRIMARY KEY AUTO_INCREMENT,
    region_id INT NOT NULL,
    risk_type ENUM('Flood', 'Salinity', 'Drought', 'Cyclone', 'Waterlogging') NOT NULL,
    severity ENUM('Low', 'Moderate', 'High', 'Critical') DEFAULT 'Moderate',
    advisory_text VARCHAR(500) NOT NULL,
    season VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

-- Insert sample climate risk data for Bangladesh regions
-- Based on known geographical and historical climate patterns

INSERT INTO climate_risk (region_id, risk_type, severity, advisory_text, season) VALUES
-- Dhaka North (region_id = 1)
(1, 'Flood', 'Moderate', 'Urban flooding possible during monsoon. Ensure proper drainage for stored crops. Consider elevated storage.', 'Monsoon (Jun-Sep)'),
(1, 'Waterlogging', 'Low', 'Low-lying areas may experience temporary waterlogging. Plan harvest timing accordingly.', 'Monsoon (Jun-Sep)'),

-- Dhaka South (region_id = 2)
(2, 'Flood', 'High', 'High flood risk during peak monsoon. Prioritize early harvest of standing crops. Avoid low-lying market storage.', 'Monsoon (Jul-Aug)'),
(2, 'Waterlogging', 'Moderate', 'Extended waterlogging common in monsoon. Consider flood-resistant crop varieties.', 'Monsoon (Jun-Sep)'),

-- Chittagong (region_id = 3)
(3, 'Cyclone', 'High', 'Coastal cyclone risk during pre-monsoon and post-monsoon. Secure crop storage. Monitor weather warnings.', 'Apr-May, Oct-Nov'),
(3, 'Salinity', 'Moderate', 'Soil salinity increasing in coastal areas. Affects rice and vegetable yields. Consider salt-tolerant varieties.', 'Year-round'),
(3, 'Flood', 'Moderate', 'Flash floods possible in hilly areas. Ensure drainage systems are functional.', 'Monsoon (Jun-Sep)'),

-- Rajshahi (region_id = 4)
(4, 'Drought', 'High', 'High drought risk during dry season. Implement water conservation. Consider drought-resistant Boro rice.', 'Dec-Apr'),
(4, 'Flood', 'Low', 'Localized flooding possible near Padma river. Monitor river levels.', 'Monsoon (Jul-Sep)'),

-- Khulna (region_id = 5)
(5, 'Salinity', 'Critical', 'Critical soil salinity issue. Major impact on agriculture. Use salinity-tolerant crop varieties only.', 'Year-round'),
(5, 'Cyclone', 'High', 'High cyclone vulnerability. Sundarbans provides some protection. Secure all storage facilities.', 'Apr-May, Oct-Nov'),
(5, 'Waterlogging', 'High', 'Severe waterlogging in low-lying areas. Plan crop cycles around water conditions.', 'Monsoon (Jun-Oct)'),

-- Sylhet (region_id = 6)
(6, 'Flood', 'Critical', 'Flash flood prone region. Very high risk during monsoon. Early harvest recommended. Avoid late planting.', 'Monsoon (May-Sep)'),
(6, 'Waterlogging', 'High', 'Extended waterlogging common in haor areas. Major impact on Boro rice cultivation.', 'Pre-monsoon (Apr-May)'),

-- Rangpur (region_id = 7)
(7, 'Drought', 'Moderate', 'Monga-prone region with periodic drought. Diversify crops. Consider alternative income sources.', 'Sep-Nov'),
(7, 'Flood', 'Low', 'Minor flood risk from Teesta river. Monitor upstream conditions.', 'Monsoon (Jul-Aug)'),

-- Barishal (region_id = 8)
(8, 'Cyclone', 'High', 'High cyclone risk in coastal areas. Prepare cyclone shelters for crop storage.', 'Apr-May, Oct-Nov'),
(8, 'Flood', 'High', 'Tidal flooding and river flooding common. Elevate storage. Monitor tide schedules.', 'Monsoon (Jun-Sep)'),
(8, 'Salinity', 'Moderate', 'Increasing salinity intrusion. Affects freshwater availability for irrigation.', 'Dry Season (Dec-Apr)'),

-- Mymensingh (region_id = 9)
(9, 'Flood', 'High', 'Brahmaputra basin flooding affects large areas. Plan early harvest for monsoon crops.', 'Monsoon (Jul-Sep)'),
(9, 'Waterlogging', 'Moderate', 'Flash floods from hilly areas. Ensure proper field drainage.', 'Monsoon (Jun-Sep)'),

-- Comilla (region_id = 10)
(10, 'Flood', 'Moderate', 'Meghna basin flooding possible. Monitor river levels during peak monsoon.', 'Monsoon (Jul-Aug)'),
(10, 'Drought', 'Low', 'Minor drought stress possible in dry season. Irrigation recommended.', 'Dec-Mar');

-- ============================================================
-- Query: Get all climate risks with region information
-- ============================================================

SELECT 
    r.region_id,
    r.region_name,
    r.state,
    cr.risk_type,
    cr.severity,
    cr.advisory_text,
    cr.season
FROM 
    climate_risk cr
    JOIN regions r ON cr.region_id = r.region_id
ORDER BY 
    FIELD(cr.severity, 'Critical', 'High', 'Moderate', 'Low'),
    r.region_name,
    cr.risk_type;

-- ============================================================
-- Query: Get regions with their highest severity risk
-- ============================================================
/*
SELECT 
    r.region_name,
    cr.risk_type,
    cr.severity,
    cr.advisory_text
FROM 
    climate_risk cr
    JOIN regions r ON cr.region_id = r.region_id
WHERE 
    cr.severity = (
        SELECT MAX(cr2.severity)
        FROM climate_risk cr2
        WHERE cr2.region_id = cr.region_id
    )
ORDER BY 
    FIELD(cr.severity, 'Critical', 'High', 'Moderate', 'Low');
*/
