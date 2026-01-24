<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/../controllers/AuthController.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

AuthController::logout();

header('Location: /agrisense/auth/login.php');
exit;
?>