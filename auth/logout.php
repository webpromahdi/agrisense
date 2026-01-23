<?php
/**
 * AgriSense - Logout Handler
 * 
 * Destroys user session and redirects to login page
 * Includes cache-control headers to prevent back-button access
 */

require_once __DIR__ . '/../controllers/AuthController.php';

// Prevent caching - stops back button from showing cached pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

// Logout user
AuthController::logout();

// Redirect to login page
header('Location: /agrisense/auth/login.php');
exit;
?>