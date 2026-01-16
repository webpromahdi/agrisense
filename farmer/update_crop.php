<?php
/**
 * AgriSense - Farmer Crop Data Update Page
 * 
 * Protected page for farmers to submit crop supply data
 * Requires valid farmer code verification
 */

require_once __DIR__ . '/../controllers/FarmerUpdateController.php';

// Handle Exit/logout FIRST before any output
if (isset($_GET['logout'])) {
    FarmerUpdateController::clearFarmerSession();
    header('Location: /agrisense/index.php');
    exit;
}

// Require farmer verification
FarmerUpdateController::requireVerification();

$controller = new FarmerUpdateController();
$errors = [];
$success = false;

// Get farmer info from session
$farmerId = FarmerUpdateController::getVerifiedFarmerId();
$farmerName = FarmerUpdateController::getVerifiedFarmerName();

// Get crops and markets for dropdowns
$crops = $controller->getCrops();
$markets = $controller->getMarkets();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropId = $_POST['crop_id'] ?? '';
    $marketId = $_POST['market_id'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $pricePerUnit = $_POST['price_per_unit'] ?? '';

    if ($controller->submitSupply($farmerId, $cropId, $marketId, $quantity, $pricePerUnit)) {
        $success = true;
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
    <title>Update Crop Data - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-green-700 to-green-600 text-white shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">🌾</span>
                    <div>
                        <h1 class="text-xl font-bold">AgriSense</h1>
                        <p class="text-xs text-green-100">Farmer Portal</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium"><?= htmlspecialchars($farmerName) ?></p>
                        <p class="text-xs text-green-100">Verified Farmer</p>
                    </div>
                    <a href="update_crop.php?logout=1"
                        class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border border-white/20">
                        Exit
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 py-8">

        <!-- Page Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Submit Crop Supply</h2>
            <p class="text-slate-500 mt-1">Enter your crop supply details for the market</p>
        </div>

        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="bg-emerald-50 border border-emerald-400 text-emerald-700 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <div>
                        <p class="font-semibold">Supply Submitted Successfully!</p>
                        <p class="text-sm">Your crop supply data has been recorded in the system.</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="update_crop.php"
                    class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                    Submit Another
                </a>
                <a href="/agrisense/index.php"
                    class="flex-1 text-center bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                    Go to Dashboard
                </a>
            </div>
        <?php else: ?>

            <!-- Update Form -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-slate-100">

                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>

                    <!-- Crop Selection -->
                    <div class="mb-5">
                        <label for="crop_id" class="block text-sm font-medium text-slate-700 mb-2">Crop Name</label>
                        <select id="crop_id" name="crop_id"
                            class="w-full px-4 py-3 border <?= isset($errors['crop']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white">
                            <option value="">-- Select a Crop --</option>
                            <?php foreach ($crops as $crop): ?>
                                <option value="<?= $crop['crop_id'] ?>" <?= (isset($_POST['crop_id']) && $_POST['crop_id'] == $crop['crop_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($crop['crop_name']) ?> (<?= htmlspecialchars($crop['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['crop'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['crop']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Market Selection -->
                    <div class="mb-5">
                        <label for="market_id" class="block text-sm font-medium text-slate-700 mb-2">Market / Region</label>
                        <select id="market_id" name="market_id"
                            class="w-full px-4 py-3 border <?= isset($errors['market']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white">
                            <option value="">-- Select a Market --</option>
                            <?php foreach ($markets as $market): ?>
                                <option value="<?= $market['market_id'] ?>" <?= (isset($_POST['market_id']) && $_POST['market_id'] == $market['market_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($market['market_name']) ?> -
                                    <?= htmlspecialchars($market['region_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['market'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['market']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity and Price Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                        <!-- Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-slate-700 mb-2">Quantity
                                (kg)</label>
                            <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                                value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>"
                                class="w-full px-4 py-3 border <?= isset($errors['quantity']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                                placeholder="e.g., 50">
                            <?php if (isset($errors['quantity'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['quantity']) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Price per Unit -->
                        <div>
                            <label for="price_per_unit" class="block text-sm font-medium text-slate-700 mb-2">Price per Unit
                                (BDT)</label>
                            <input type="number" id="price_per_unit" name="price_per_unit" step="0.01" min="0.01"
                                value="<?= htmlspecialchars($_POST['price_per_unit'] ?? '') ?>"
                                class="w-full px-4 py-3 border <?= isset($errors['price']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                                placeholder="e.g., 2150">
                            <?php if (isset($errors['price'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['price']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        Submit Supply Data
                    </button>
                </form>
            </div>

        <?php endif; ?>

    </main>
</body>

</html>