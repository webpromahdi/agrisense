<?php
/**
 * AgriSense - Farmer Code Verification Page
 * 
 * Entry point for farmers to verify their identity using 6-digit code
 */

require_once __DIR__ . '/../controllers/FarmerUpdateController.php';

// Handle Exit/logout FIRST before any output
if (isset($_GET['logout'])) {
    FarmerUpdateController::clearFarmerSession();
    header('Location: /agrisense/index.php');
    exit;
}

$controller = new FarmerUpdateController();
$errors = [];

// If already verified, redirect to update form
if (FarmerUpdateController::isFarmerVerified()) {
    header('Location: update_crop.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['farmer_code'] ?? '');

    $farmer = $controller->verifyCode($code);

    if ($farmer) {
        // Start farmer session and redirect to update form
        FarmerUpdateController::startFarmerSession($farmer);
        header('Location: update_crop.php');
        exit;
    } else {
        $errors = $controller->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Verification - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center space-x-3 mb-2">
                <span class="text-4xl">🌾</span>
                <h1 class="text-3xl font-bold text-green-700">AgriSense</h1>
            </div>
            <p class="text-slate-500">Agricultural Market Intelligence</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-100">
            <div class="text-center mb-6">
                <span class="text-5xl">👨‍🌾</span>
                <h2 class="text-2xl font-bold text-slate-800 mt-3">Farmer Verification</h2>
                <p class="text-slate-500 mt-2">Enter your 6-digit farmer code to continue</p>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <!-- Farmer Code Input -->
                <div class="mb-6">
                    <label for="farmer_code" class="block text-sm font-medium text-slate-700 mb-2">Farmer Code</label>
                    <input type="text" id="farmer_code" name="farmer_code" maxlength="6" pattern="\d{6}"
                        inputmode="numeric" value="<?= htmlspecialchars($_POST['farmer_code'] ?? '') ?>"
                        class="w-full px-4 py-4 text-center text-2xl font-mono tracking-widest border <?= isset($errors['code']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                        placeholder="000000" autofocus>
                    <?php if (isset($errors['code'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['code']) ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-xs text-slate-500 text-center">Enter the 6-digit code provided to you</p>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                    Verify & Continue
                </button>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="/agrisense/index.php"
                class="text-slate-500 hover:text-green-600 text-sm transition-colors duration-200">
                ← Back to Home
            </a>
        </div>
    </div>
</body>

</html>