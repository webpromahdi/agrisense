<?php
/**
 * Farmer Update Controller
 */

require_once __DIR__ . '/../db/connection.php';

class FarmerUpdateController
{

    private $pdo;
    private $errors = [];

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validateCodeFormat($code)
    {
        if (empty($code)) {
            $this->errors['code'] = 'Farmer code is required';
            return false;
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            $this->errors['code'] = 'Code must be exactly 6 digits';
            return false;
        }

        return true;
    }

    public function verifyCode($code)
    {
        $this->errors = [];

        if (!$this->validateCodeFormat($code)) {
            return false;
        }

        if ($this->pdo === null) {
            $this->errors['general'] = 'Database connection failed';
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT f.farmer_id, f.farmer_name, f.farmer_code, r.region_name 
                 FROM farmers f 
                 JOIN regions r ON f.region_id = r.region_id 
                 WHERE f.farmer_code = ?"
            );
            $stmt->execute([$code]);
            $farmer = $stmt->fetch();

            if (!$farmer) {
                $this->errors['code'] = 'Invalid farmer code. Please check and try again.';
                return false;
            }

            return $farmer;

        } catch (PDOException $e) {
            error_log("Farmer verification error: " . $e->getMessage());
            $this->errors['general'] = 'Verification failed. Please try again.';
            return false;
        }
    }

    public function getFarmerById($farmerId)
    {
        if ($this->pdo === null) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT f.farmer_id, f.farmer_name, f.farmer_code, r.region_name 
                 FROM farmers f 
                 JOIN regions r ON f.region_id = r.region_id 
                 WHERE f.farmer_id = ?"
            );
            $stmt->execute([$farmerId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Farmer fetch error: " . $e->getMessage());
            return false;
        }
    }

    public function getCrops()
    {
        if ($this->pdo === null) {
            return [];
        }

        try {
            $stmt = $this->pdo->query(
                "SELECT crop_id, crop_name, category, unit FROM crops ORDER BY crop_name"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Crops fetch error: " . $e->getMessage());
            return [];
        }
    }

    public function getMarkets()
    {
        if ($this->pdo === null) {
            return [];
        }

        try {
            $stmt = $this->pdo->query(
                "SELECT m.market_id, m.market_name, r.region_name 
                 FROM markets m 
                 JOIN regions r ON m.region_id = r.region_id 
                 ORDER BY m.market_name"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Markets fetch error: " . $e->getMessage());
            return [];
        }
    }

    public function submitSupply($farmerId, $cropId, $marketId, $quantity, $pricePerUnit)
    {
        $this->errors = [];

        if (empty($cropId) || !is_numeric($cropId)) {
            $this->errors['crop'] = 'Please select a crop';
        }

        if (empty($marketId) || !is_numeric($marketId)) {
            $this->errors['market'] = 'Please select a market';
        }

        if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
            $this->errors['quantity'] = 'Please enter a valid quantity';
        }

        if (empty($pricePerUnit) || !is_numeric($pricePerUnit) || $pricePerUnit <= 0) {
            $this->errors['price'] = 'Please enter a valid price per unit';
        }

        if (!empty($this->errors)) {
            return false;
        }

        if ($this->pdo === null) {
            $this->errors['general'] = 'Database connection failed';
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO market_supply (farmer_id, market_id, crop_id, quantity, price_per_unit, supply_date) 
                 VALUES (?, ?, ?, ?, ?, CURDATE())"
            );
            $stmt->execute([
                $farmerId,
                $marketId,
                $cropId,
                $quantity,
                $pricePerUnit
            ]);

            return true;

        } catch (PDOException $e) {
            error_log("Supply submission error: " . $e->getMessage());
            $this->errors['general'] = 'Failed to submit supply data. Please try again.';
            return false;
        }
    }

    public static function startFarmerSession($farmer)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['farmer_id'] = $farmer['farmer_id'];
        $_SESSION['farmer_name'] = $farmer['farmer_name'];
        $_SESSION['farmer_verified'] = true;
    }

    public static function isFarmerVerified()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['farmer_verified']) && $_SESSION['farmer_verified'] === true;
    }

    public static function getVerifiedFarmerId()
    {
        if (!self::isFarmerVerified()) {
            return null;
        }

        return $_SESSION['farmer_id'] ?? null;
    }

    public static function getVerifiedFarmerName()
    {
        if (!self::isFarmerVerified()) {
            return null;
        }

        return $_SESSION['farmer_name'] ?? null;
    }

    public static function clearFarmerSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['farmer_id']);
        unset($_SESSION['farmer_name']);
        unset($_SESSION['farmer_verified']);
    }

    public static function requireVerification()
    {
        if (!self::isFarmerVerified()) {
            header('Location: /agrisense/farmer/verify_code.php');
            exit;
        }
    }
}
?>