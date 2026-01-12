<?php
/**
 * AgriSense - Database Connection
 * 
 * PDO Database Connection with prepared statement support
 * All database connections must use this file
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'agrisense');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty

/**
 * Get PDO Database Connection
 * 
 * @return PDO|null Database connection object or null on failure
 */
function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error (in production, log to file instead)
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Execute a prepared statement with parameters
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return array|false Result set or false on failure
 */
function executeQuery($sql, $params = []) {
    $pdo = getConnection();
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all crops for dropdown selection
 * 
 * @return array List of crops
 */
function getAllCrops() {
    $sql = "SELECT crop_id, crop_name, category FROM crops ORDER BY crop_name";
    return executeQuery($sql);
}

/**
 * Get all markets for dropdown selection
 * 
 * @return array List of markets
 */
function getAllMarkets() {
    $sql = "SELECT m.market_id, m.market_name, r.region_name 
            FROM markets m 
            JOIN regions r ON m.region_id = r.region_id 
            ORDER BY m.market_name";
    return executeQuery($sql);
}

/**
 * Get all regions for dropdown selection
 * 
 * @return array List of regions
 */
function getAllRegions() {
    $sql = "SELECT region_id, region_name, state FROM regions ORDER BY region_name";
    return executeQuery($sql);
}
?>
