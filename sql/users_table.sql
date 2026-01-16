-- ============================================================
-- AgriSense - Users Table for Authentication
-- ============================================================

USE agrisense;

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
-- END OF USERS TABLE SCHEMA
-- ============================================================
