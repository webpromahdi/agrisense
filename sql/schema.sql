-- ============================================================
-- AgriSense - Agricultural Market Intelligence Database
-- Database Schema and Sample Data
-- ============================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS agrisense;
USE agrisense;

-- ============================================================
-- TABLE: regions
-- Stores geographical regions for market analysis
-- ============================================================
CREATE TABLE IF NOT EXISTS regions (
    region_id INT PRIMARY KEY AUTO_INCREMENT,
    region_name VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: markets
-- Stores market/mandi information
-- ============================================================
CREATE TABLE IF NOT EXISTS markets (
    market_id INT PRIMARY KEY AUTO_INCREMENT,
    market_name VARCHAR(150) NOT NULL,
    region_id INT NOT NULL,
    location VARCHAR(200),
    market_type ENUM('wholesale', 'retail', 'both') DEFAULT 'wholesale',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

-- ============================================================
-- TABLE: crops
-- Stores crop/commodity information
-- ============================================================
CREATE TABLE IF NOT EXISTS crops (
    crop_id INT PRIMARY KEY AUTO_INCREMENT,
    crop_name VARCHAR(100) NOT NULL,
    category ENUM('grain', 'vegetable', 'fruit', 'pulse', 'oilseed', 'spice') NOT NULL,
    unit VARCHAR(20) DEFAULT 'kg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: farmers
-- Stores farmer information for supply tracking
-- ============================================================
CREATE TABLE IF NOT EXISTS farmers (
    farmer_id INT PRIMARY KEY AUTO_INCREMENT,
    farmer_name VARCHAR(150) NOT NULL,
    region_id INT NOT NULL,
    contact_number VARCHAR(15),
    farmer_code VARCHAR(6) NOT NULL UNIQUE,
    farm_size_acres DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

-- ============================================================
-- TABLE: market_prices
-- Current market prices for crops (main price table)
-- ============================================================
CREATE TABLE IF NOT EXISTS market_prices (
    price_id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    market_id INT NOT NULL,
    current_price DECIMAL(10,2) NOT NULL,
    min_price DECIMAL(10,2),
    max_price DECIMAL(10,2),
    price_date DATE NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id)
);

-- ============================================================
-- TABLE: price_history
-- Historical price data for trend analysis
-- ============================================================
CREATE TABLE IF NOT EXISTS price_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    market_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity_sold DECIMAL(12,2),
    record_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id)
);

-- ============================================================
-- TABLE: market_supply
-- Tracks supply from farmers to markets
-- ============================================================
CREATE TABLE IF NOT EXISTS market_supply (
    supply_id INT PRIMARY KEY AUTO_INCREMENT,
    farmer_id INT NOT NULL,
    market_id INT NOT NULL,
    crop_id INT NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    supply_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id),
    FOREIGN KEY (market_id) REFERENCES markets(market_id),
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id)
);

-- ============================================================
-- TABLE: users
-- Stores user authentication and profile data
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SAMPLE DATA INSERTION (BANGLADESH)
-- ============================================================

-- Insert Regions (Bangladesh Divisions)
INSERT INTO regions (region_name, state) VALUES
('Dhaka North', 'Dhaka'),
('Dhaka South', 'Dhaka'),
('Chittagong', 'Chattogram'),
('Rajshahi', 'Rajshahi'),
('Khulna', 'Khulna'),
('Sylhet', 'Sylhet'),
('Rangpur', 'Rangpur'),
('Barishal', 'Barishal'),
('Mymensingh', 'Mymensingh'),
('Comilla', 'Chattogram');

-- Insert Markets (Bangladesh Agricultural Markets/Haats)
INSERT INTO markets (market_name, region_id, location, market_type) VALUES
('Kawran Bazar', 1, 'Dhaka City', 'wholesale'),
('Mohammadpur Krishi Market', 1, 'Mohammadpur', 'wholesale'),
('Karwan Bazar Arot', 2, 'Motijheel', 'wholesale'),
('Khatunganj Market', 3, 'Chittagong City', 'wholesale'),
('Rajshahi Krishi Market', 4, 'Rajshahi City', 'both'),
('Khulna Bazar', 5, 'Khulna City', 'wholesale'),
('Sylhet Bazar', 6, 'Sylhet City', 'both'),
('Rangpur Krishi Haat', 7, 'Rangpur City', 'wholesale'),
('Barishal Bazar', 8, 'Barishal City', 'wholesale'),
('Bogra Haat', 4, 'Bogra', 'wholesale'),
('Mymensingh Bazar', 9, 'Mymensingh City', 'both'),
('Comilla Krishi Market', 10, 'Comilla City', 'wholesale');

-- Insert Crops (Bangladesh Major Crops)
INSERT INTO crops (crop_name, category, unit) VALUES
('Rice (Aman)', 'grain', 'kg'),
('Rice (Boro)', 'grain', 'kg'),
('Rice (Aus)', 'grain', 'kg'),
('Wheat', 'grain', 'kg'),
('Potato', 'vegetable', 'kg'),
('Onion', 'vegetable', 'kg'),
('Tomato', 'vegetable', 'kg'),
('Jute', 'oilseed', 'kg'),
('Mustard', 'oilseed', 'kg'),
('Lentil (Masur)', 'pulse', 'kg'),
('Chickpea (Chola)', 'pulse', 'kg'),
('Sugarcane', 'grain', 'kg'),
('Brinjal (Begun)', 'vegetable', 'kg'),
('Chili (Morich)', 'spice', 'kg'),
('Garlic (Rosun)', 'spice', 'kg');

-- Insert Farmers (Bangladeshi Names)
INSERT INTO farmers (farmer_name, region_id, contact_number, farmer_code, farm_size_acres) VALUES
('Abdul Karim', 1, '01712345678', '100001', 4.5),
('Mohammad Rahim', 1, '01712345679', '100002', 3.0),
('Fazlur Rahman', 2, '01812345680', '100003', 5.0),
('Jamal Uddin', 2, '01812345681', '100004', 6.5),
('Kamal Hossain', 3, '01912345682', '100005', 4.0),
('Rafiqul Islam', 3, '01912345683', '100006', 3.5),
('Shahidul Alam', 4, '01612345684', '100007', 7.0),
('Nurul Haque', 5, '01512345685', '100008', 2.5),
('Aminul Haque', 6, '01312345686', '100009', 5.5),
('Mizanur Rahman', 7, '01412345687', '100010', 8.0),
('Habibur Rahman', 8, '01712345688', '100011', 3.0),
('Shafiqul Islam', 9, '01812345689', '100012', 6.0),
('Mostafa Kamal', 10, '01912345690', '100013', 4.5),
('Ataur Rahman', 1, '01612345691', '100014', 7.5),
('Nazrul Islam', 2, '01512345692', '100015', 2.0),
('Sohel Rana', 3, '01312345693', '100016', 5.5),
('Alamgir Hossain', 4, '01412345694', '100017', 3.5),
('Babul Mia', 5, '01712345695', '100018', 4.0),
('Delwar Hossain', 6, '01812345696', '100019', 6.5),
('Enamul Haque', 7, '01912345697', '100020', 8.5);

-- Insert Current Market Prices (with some anomalies for testing)
-- Prices in Bangladeshi Taka (BDT) per kg
INSERT INTO market_prices (crop_id, market_id, current_price, min_price, max_price, price_date) VALUES
-- Rice (Aman) prices (normal avg ~2200 BDT, anomaly in market 3)
(1, 1, 2150, 2100, 2200, '2026-01-10'),
(1, 2, 2180, 2150, 2220, '2026-01-10'),
(1, 3, 2850, 2800, 2900, '2026-01-10'),  -- Anomaly: +29%
(1, 4, 2200, 2150, 2250, '2026-01-10'),
(1, 5, 2100, 2050, 2150, '2026-01-10'),
(1, 6, 2250, 2200, 2300, '2026-01-10'),
(1, 7, 2180, 2130, 2230, '2026-01-10'),
(1, 8, 2300, 2250, 2350, '2026-01-10'),
(1, 9, 2220, 2170, 2270, '2026-01-10'),
(1, 10, 2190, 2140, 2240, '2026-01-10'),

-- Rice (Boro) prices (normal avg ~2400 BDT, anomaly in market 6)
(2, 1, 2450, 2400, 2500, '2026-01-10'),
(2, 2, 2420, 2370, 2470, '2026-01-10'),
(2, 3, 2380, 2330, 2430, '2026-01-10'),
(2, 4, 2400, 2350, 2450, '2026-01-10'),
(2, 5, 2350, 2300, 2400, '2026-01-10'),
(2, 6, 1800, 1750, 1850, '2026-01-10'),  -- Anomaly: -25%
(2, 7, 2430, 2380, 2480, '2026-01-10'),
(2, 8, 2460, 2410, 2510, '2026-01-10'),

-- Potato prices (normal avg ~1000 BDT, anomaly in market 8)
(5, 1, 950, 900, 1000, '2026-01-10'),
(5, 2, 980, 930, 1030, '2026-01-10'),
(5, 3, 1020, 970, 1070, '2026-01-10'),
(5, 4, 1050, 1000, 1100, '2026-01-10'),
(5, 5, 900, 850, 950, '2026-01-10'),
(5, 6, 1080, 1030, 1130, '2026-01-10'),
(5, 7, 990, 940, 1040, '2026-01-10'),
(5, 8, 1450, 1400, 1500, '2026-01-10'),  -- Anomaly: +45%
(5, 9, 960, 910, 1010, '2026-01-10'),
(5, 10, 1000, 950, 1050, '2026-01-10'),

-- Onion prices (volatile)
(6, 1, 1600, 1500, 1700, '2026-01-10'),
(6, 2, 1550, 1450, 1650, '2026-01-10'),
(6, 3, 2200, 2100, 2300, '2026-01-10'),  -- Anomaly: +37%
(6, 4, 1620, 1520, 1720, '2026-01-10'),
(6, 5, 1580, 1480, 1680, '2026-01-10'),
(6, 6, 1650, 1550, 1750, '2026-01-10'),
(6, 7, 1590, 1490, 1690, '2026-01-10'),
(6, 8, 1500, 1400, 1600, '2026-01-10'),

-- Jute prices (Bangladesh specialty)
(8, 1, 3200, 3100, 3300, '2026-01-10'),
(8, 2, 3150, 3050, 3250, '2026-01-10'),
(8, 7, 3300, 3200, 3400, '2026-01-10'),
(8, 8, 3180, 3080, 3280, '2026-01-10'),
(8, 9, 3250, 3150, 3350, '2026-01-10'),
(8, 10, 3100, 3000, 3200, '2026-01-10'),

-- Mustard prices
(9, 7, 5200, 5100, 5300, '2026-01-10'),
(9, 8, 5150, 5050, 5250, '2026-01-10'),
(9, 9, 5300, 5200, 5400, '2026-01-10'),
(9, 10, 5100, 5000, 5200, '2026-01-10');

-- Insert Price History (last 12 months for trend analysis)
-- Prices in Bangladeshi Taka (BDT)
INSERT INTO price_history (crop_id, market_id, price, quantity_sold, record_date) VALUES
-- Rice (Aman) historical prices (Market 1 - Kawran Bazar)
(1, 1, 1850, 500, '2025-02-15'),
(1, 1, 1880, 480, '2025-03-15'),
(1, 1, 1920, 520, '2025-04-15'),
(1, 1, 1950, 450, '2025-05-15'),
(1, 1, 1980, 420, '2025-06-15'),
(1, 1, 1900, 480, '2025-07-15'),
(1, 1, 1880, 510, '2025-08-15'),
(1, 1, 1920, 550, '2025-09-15'),
(1, 1, 2000, 580, '2025-10-15'),
(1, 1, 2080, 600, '2025-11-15'),
(1, 1, 2120, 550, '2025-12-15'),
(1, 1, 2150, 520, '2026-01-10'),

-- Rice (Boro) historical prices (Market 1)
(2, 1, 2100, 400, '2025-02-15'),
(2, 1, 2150, 420, '2025-03-15'),
(2, 1, 2200, 410, '2025-04-15'),
(2, 1, 2250, 380, '2025-05-15'),
(2, 1, 2300, 360, '2025-06-15'),
(2, 1, 2250, 400, '2025-07-15'),
(2, 1, 2280, 460, '2025-08-15'),
(2, 1, 2350, 500, '2025-09-15'),
(2, 1, 2400, 560, '2025-10-15'),
(2, 1, 2420, 520, '2025-11-15'),
(2, 1, 2440, 480, '2025-12-15'),
(2, 1, 2450, 440, '2026-01-10'),

-- Potato historical prices (Market 1)
(5, 1, 600, 600, '2025-02-15'),
(5, 1, 650, 640, '2025-03-15'),
(5, 1, 750, 560, '2025-04-15'),
(5, 1, 900, 500, '2025-05-15'),
(5, 1, 1050, 440, '2025-06-15'),
(5, 1, 1150, 400, '2025-07-15'),
(5, 1, 1080, 480, '2025-08-15'),
(5, 1, 1000, 560, '2025-09-15'),
(5, 1, 900, 620, '2025-10-15'),
(5, 1, 850, 660, '2025-11-15'),
(5, 1, 900, 700, '2025-12-15'),
(5, 1, 950, 640, '2026-01-10'),

-- Onion historical prices (Market 1) - volatile
(6, 1, 1000, 250, '2025-02-15'),
(6, 1, 1200, 230, '2025-03-15'),
(6, 1, 1400, 210, '2025-04-15'),
(6, 1, 1700, 180, '2025-05-15'),
(6, 1, 2000, 150, '2025-06-15'),
(6, 1, 2300, 120, '2025-07-15'),
(6, 1, 2100, 140, '2025-08-15'),
(6, 1, 1800, 180, '2025-09-15'),
(6, 1, 1600, 220, '2025-10-15'),
(6, 1, 1500, 240, '2025-11-15'),
(6, 1, 1550, 250, '2025-12-15'),
(6, 1, 1600, 230, '2026-01-10'),

-- Jute historical prices (Market 7 - Sylhet)
(8, 7, 2800, 150, '2025-02-15'),
(8, 7, 2900, 160, '2025-03-15'),
(8, 7, 3000, 170, '2025-04-15'),
(8, 7, 3050, 165, '2025-05-15'),
(8, 7, 3100, 155, '2025-06-15'),
(8, 7, 3080, 160, '2025-07-15'),
(8, 7, 3120, 170, '2025-08-15'),
(8, 7, 3180, 180, '2025-09-15'),
(8, 7, 3220, 185, '2025-10-15'),
(8, 7, 3260, 175, '2025-11-15'),
(8, 7, 3280, 168, '2025-12-15'),
(8, 7, 3300, 160, '2026-01-10'),

-- Mustard historical prices (Market 7)
(9, 7, 4500, 80, '2025-02-15'),
(9, 7, 4600, 85, '2025-03-15'),
(9, 7, 4700, 90, '2025-04-15'),
(9, 7, 4800, 92, '2025-05-15'),
(9, 7, 4900, 88, '2025-06-15'),
(9, 7, 5000, 85, '2025-07-15'),
(9, 7, 5050, 87, '2025-08-15'),
(9, 7, 5100, 89, '2025-09-15'),
(9, 7, 5120, 91, '2025-10-15'),
(9, 7, 5150, 88, '2025-11-15'),
(9, 7, 5180, 86, '2025-12-15'),
(9, 7, 5200, 82, '2026-01-10');

-- Insert Market Supply Data (for saturation analysis)
-- Prices in Bangladeshi Taka (BDT)
INSERT INTO market_supply (farmer_id, market_id, crop_id, quantity, price_per_unit, supply_date) VALUES
-- High supply to Kawran Bazar (Market 1) - Rice (Aman)
(1, 1, 1, 45, 2150, '2026-01-10'),
(2, 1, 1, 30, 2150, '2026-01-10'),
(14, 1, 1, 65, 2150, '2026-01-10'),
(3, 1, 1, 40, 2150, '2026-01-10'),
(4, 1, 1, 55, 2150, '2026-01-10'),
(15, 1, 1, 20, 2150, '2026-01-10'),

-- Medium supply to Mohammadpur Market (Market 2) - Rice (Aman)
(1, 2, 1, 35, 2180, '2026-01-10'),
(2, 2, 1, 28, 2180, '2026-01-10'),
(14, 2, 1, 38, 2180, '2026-01-10'),

-- Supply to Khatunganj (Market 4)
(5, 4, 1, 42, 2200, '2026-01-10'),
(6, 4, 1, 25, 2200, '2026-01-10'),
(16, 4, 1, 48, 2200, '2026-01-10'),
(7, 4, 1, 58, 2200, '2026-01-10'),

-- Rice (Boro) supply
(1, 1, 2, 18, 2450, '2026-01-10'),
(2, 1, 2, 15, 2450, '2026-01-10'),
(14, 1, 2, 22, 2450, '2026-01-10'),

-- Potato supply (high saturation in Market 5 - Rajshahi)
(8, 5, 5, 70, 900, '2026-01-10'),
(18, 5, 5, 60, 900, '2026-01-10'),
(8, 5, 5, 55, 900, '2026-01-10'),
(18, 5, 5, 75, 900, '2026-01-10'),
(8, 6, 5, 35, 1080, '2026-01-10'),
(9, 6, 5, 42, 1080, '2026-01-10'),

-- Jute supply (Bangladesh specialty)
(10, 7, 8, 28, 3300, '2026-01-10'),
(11, 7, 8, 24, 3300, '2026-01-10'),
(20, 7, 8, 32, 3300, '2026-01-10'),
(10, 8, 8, 22, 3180, '2026-01-10'),
(11, 8, 8, 18, 3180, '2026-01-10'),

-- Mustard supply
(10, 7, 9, 15, 5200, '2026-01-10'),
(20, 7, 9, 18, 5200, '2026-01-10'),
(11, 8, 9, 12, 5150, '2026-01-10'),

-- Onion supply
(5, 4, 6, 38, 1620, '2026-01-10'),
(6, 4, 6, 30, 1620, '2026-01-10'),
(7, 4, 6, 45, 1620, '2026-01-10'),
(8, 6, 6, 32, 1650, '2026-01-10'),
(9, 6, 6, 40, 1650, '2026-01-10');

-- ============================================================
-- END OF SCHEMA AND SAMPLE DATA (BANGLADESH)
-- ============================================================
