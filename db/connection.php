<?php
/**
 * Database Connection
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'agrisense');
define('DB_USER', 'root');
define('DB_PASS', '');

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
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}

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

function getAllCrops() {
    $sql = "SELECT crop_id, crop_name, category FROM crops ORDER BY crop_name";
    return executeQuery($sql);
}

function getAllMarkets() {
    $sql = "SELECT m.market_id, m.market_name, r.region_name 
            FROM markets m 
            JOIN regions r ON m.region_id = r.region_id 
            ORDER BY m.market_name";
    return executeQuery($sql);
}

function getAllRegions() {
    $sql = "SELECT region_id, region_name, state FROM regions ORDER BY region_name";
    return executeQuery($sql);
}
?>
