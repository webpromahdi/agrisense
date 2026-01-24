-- ============================================================
-- AgriSense - Agricultural Market Intelligence Database
-- Database Schema (Structure Only)
-- ============================================================
-- Usage:
--   1. Run this file first to create tables
--   2. Run seed_data.sql to populate with sample data
-- ============================================================
DROP DATABASE agrisense;
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
-- TABLE: climate_risk
-- Static climate risk advisory information per region
-- ============================================================
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

-- ============================================================
-- END OF SCHEMA
-- ============================================================
