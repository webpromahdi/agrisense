<?php
require_once __DIR__ . '/../controllers/FarmerUpdateController.php';

if (isset($_GET['logout'])) {
    FarmerUpdateController::clearFarmerSession();
    header('Location: /agrisense/index.php');
    exit;
}

FarmerUpdateController::requireVerification();
$controller = new FarmerUpdateController();
$errors = [];
$success = false;

$farmerId = FarmerUpdateController::getVerifiedFarmerId();
$farmerName = FarmerUpdateController::getVerifiedFarmerName();
$crops = $controller->getCrops();
$markets = $controller->getMarkets();

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
    <title>Submit Crop Supply - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #FAFAF9;
            min-height: 100vh;
        }
        
        .glass-nav {
            background: #166534;
            box-shadow: 0 2px 8px rgba(22, 101, 52, 0.15);
        }
        
        .glass-card {
            background: #FFFFFF;
            border: 1px solid #E7E5E4;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #166534 0%, #14532d 100%);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(22, 101, 52, 0.25);
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        }
        
        .farmer-portal {
            background: #DCFCE7;
            border: 2px solid #BBF7D0;
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.1);
        }
        
        .feature-icon {
            background: #DCFCE7;
            border: 1px solid #BBF7D0;
        }

        .text-heading { color: #1C1917; }
        .text-body { color: #44403C; }
        .text-muted { color: #78716C; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation - Deep Forest Green -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/agrisense/index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">AgriSense</h1>
                            <p class="text-xs text-white/80 font-medium">Farmer Portal</p>
                        </div>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-white"><?= htmlspecialchars($farmerName) ?></p>
                        <p class="text-xs text-white/70">Verified Farmer</p>
                    </div>
                    <a href="update_crop.php?logout=1"
                       class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white hover:bg-white/20 transition-colors font-medium">
                        Exit Portal
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center shadow-lg">
                    <span class="text-2xl text-white">👨‍🌾</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-heading">Submit Crop Supply</h1>
                    <p class="text-body mt-1">Enter your crop supply details for the market</p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="farmer-portal rounded-2xl p-8 mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-heading">Supply Submitted Successfully!</h2>
                        <p class="text-green-700 font-medium">Your crop supply data has been recorded in the system.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <a href="update_crop.php"
                        class="btn-primary px-6 py-3 rounded-xl text-lg text-center inline-flex items-center justify-center shadow-lg">
                        <span class="mr-3">➕</span> Submit Another
                    </a>
                    <a href="/agrisense/index.php"
                        class="glass-card px-6 py-3 rounded-xl font-semibold text-lg text-center inline-flex items-center justify-center text-body hover:bg-gray-50 transition-colors">
                        <span class="mr-3">🏠</span> Go to Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Form -->
            <div class="glass-card rounded-2xl p-8">
                <?php if (isset($errors['general'])): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <p class="font-semibold"><?= htmlspecialchars($errors['general']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <!-- Crop Selection -->
                    <div>
                        <label for="crop_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Crop Name
                        </label>
                        <div class="relative">
                            <select id="crop_id" name="crop_id"
                                class="w-full px-4 py-3 glass-card rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent appearance-none">
                                <option value="">-- Select a Crop --</option>
                                <?php foreach ($crops as $crop): ?>
                                    <option value="<?= $crop['crop_id'] ?>" <?= (isset($_POST['crop_id']) && $_POST['crop_id'] == $crop['crop_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($crop['crop_name']) ?> (<?= htmlspecialchars($crop['category']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <?php if (isset($errors['crop'])): ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?= htmlspecialchars($errors['crop']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Market Selection -->
                    <div>
                        <label for="market_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Market / Region
                        </label>
                        <div class="relative">
                            <select id="market_id" name="market_id"
                                class="w-full px-4 py-3 glass-card rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent appearance-none">
                                <option value="">-- Select a Market --</option>
                                <?php foreach ($markets as $market): ?>
                                    <option value="<?= $market['market_id'] ?>" <?= (isset($_POST['market_id']) && $_POST['market_id'] == $market['market_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($market['market_name']) ?> - <?= htmlspecialchars($market['region_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <?php if (isset($errors['market'])): ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?= htmlspecialchars($errors['market']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity and Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantity (kg)
                            </label>
                            <div class="relative">
                                <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                                    value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>"
                                    class="w-full px-4 py-3 glass-card rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    placeholder="e.g., 50">
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <span class="text-gray-400">kg</span>
                                </div>
                            </div>
                            <?php if (isset($errors['quantity'])): ?>
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?= htmlspecialchars($errors['quantity']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="price_per_unit" class="block text-sm font-medium text-gray-700 mb-2">
                                Price per Unit (৳)
                            </label>
                            <div class="relative">
                                <input type="number" id="price_per_unit" name="price_per_unit" step="0.01" min="0.01"
                                    value="<?= htmlspecialchars($_POST['price_per_unit'] ?? '') ?>"
                                    class="w-full px-4 py-3 glass-card rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    placeholder="e.g., 2150">
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <span class="text-gray-400">৳</span>
                                </div>
                            </div>
                            <?php if (isset($errors['price'])): ?>
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?= htmlspecialchars($errors['price']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full btn-primary px-6 py-4 rounded-xl font-medium text-lg mt-6">
                        <span class="mr-3">📤</span> Submit Supply Data
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>