<?php
/**
 * AgriSense - Logout Handler
 * 
 * Destroys user session and redirects to login page
 */

require_once __DIR__ . '/../controllers/AuthController.php';

// Logout user
AuthController::logout();

// Redirect to login page
header('Location: /agrisense/auth/login.php');
exit;
?>