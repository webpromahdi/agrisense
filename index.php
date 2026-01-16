<?php
/**
 * AgriSense - Agricultural Market Intelligence System
 * Main Dashboard / Landing Page
 * 
 * Category A: Market Intelligence Features
 */

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/connection.php';

// Require authentication - redirect to login if not logged in
AuthController::requireAuth();

// Get current user data
$currentUser = AuthController::getCurrentUser();

// Quick stats using SQL
$stats = [];
$pdo = getConnection();

if ($pdo) {
    try {
        // Get quick statistics
        $stats['crops'] = $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn();
        $stats['markets'] = $pdo->query("SELECT COUNT(*) FROM markets")->fetchColumn();
        $stats['regions'] = $pdo->query("SELECT COUNT(*) FROM regions")->fetchColumn();
        $stats['farmers'] = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn();
        $stats['price_records'] = $pdo->query("SELECT COUNT(*) FROM market_prices")->fetchColumn();
        $stats['supply_records'] = $pdo->query("SELECT COUNT(*) FROM market_supply")->fetchColumn();
    } catch (PDOException $e) {
        // Stats unavailable, continue without them
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriSense - Agricultural Market Intelligence System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-green-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-3xl">🌾</span>
                    <div>
                        <h1 class="text-2xl font-bold">AgriSense</h1>
                        <p class="text-xs text-green-200">Agricultural Market Intelligence</p>
                    </div>
                </div>

                <!-- User Info & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm font-medium"><?= htmlspecialchars($currentUser['name']) ?></p>
                        <p class="text-xs text-green-200"><?= htmlspecialchars($currentUser['email']) ?></p>
                    </div>
                    <a href="/agrisense/auth/logout.php"
                        class="bg-green-800 hover:bg-green-900 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->


    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">

        <!-- Quick Stats -->
        <?php if (!empty($stats)): ?>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= $stats['crops'] ?? '-' ?></p>
                    <p class="text-sm text-gray-500">Crops</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= $stats['markets'] ?? '-' ?></p>
                    <p class="text-sm text-gray-500">Markets</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= $stats['regions'] ?? '-' ?></p>
                    <p class="text-sm text-gray-500">Regions</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= $stats['farmers'] ?? '-' ?></p>
                    <p class="text-sm text-gray-500">Farmers</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= $stats['price_records'] ?? '-' ?></p>
                    <p class="text-sm text-gray-500">Price Records</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= $stats['supply_records'] ?? '-' ?></p>
                    <p class="text-sm text-gray-500">Supply Records</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Category A: Market Intelligence Features -->
        <section class="mb-12">
            <div class="flex items-center mb-6">
                <span class="bg-green-600 text-white px-4 py-1 rounded-full text-sm font-semibold mr-3">

                </span>
                <h2 class="text-2xl font-bold text-gray-800">Market Intelligence Features</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Feature A1: Price Anomaly Detection -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="bg-red-500 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">📊</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Price Anomaly Detection</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Detect crops whose prices deviate more than ±20% from average prices across all markets.
                        </p>
                        <a href="pages/price_anomaly.php"
                            class="block w-full text-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                            Analyze Anomalies →
                        </a>
                    </div>
                </div>

                <!-- Feature A5: Most Profitable Crop by Region -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="bg-green-500 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">🏆</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Top Crop by Region</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Find which crop generates the highest revenue in each region.
                        </p>
                        <a href="pages/top_crop_region.php"
                            class="block w-full text-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            Find Top Crops →
                        </a>
                    </div>
                </div>

                <!-- Top Farmer by Region Feature -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="bg-blue-500 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">👨‍🌾</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Top Farmer by Region</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Identify the farmer with highest total revenue in each region.
                        </p>
                        <a href="pages/top_farmer_region.php"
                            class="block w-full text-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            View Top Farmers →
                        </a>
                    </div>
                </div>

                <!-- Farmer Portal: Crop Data Update -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="bg-amber-500 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">🌾</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Farmer Portal</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Farmers can update their crop supply data using their unique 6-digit code.
                        </p>
                        <a href="farmer/verify_code.php"
                            class="block w-full text-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                            Enter Farmer Portal →
                        </a>
                    </div>
                </div>

                <!-- Feature: Inter-Market Price Gap Analysis -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="bg-purple-500 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">🔄</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Price Gap Analysis</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Compare crop prices across markets to identify significant price differences.
                        </p>
                        <a href="pages/market_price_gap.php"
                            class="block w-full text-center px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            Analyze Price Gaps →
                        </a>
                    </div>
                </div>

                <!-- Feature: Historical Price Trend Analysis -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="bg-teal-500 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">📈</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Price Trend Analysis</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Analyze month-wise price trends using historical price data.
                        </p>
                        <a href="pages/price_trend.php"
                            class="block w-full text-center px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                            View Price Trends →
                        </a>
                    </div>
                </div>


            </div>
        </section>


    </main>


</body>

</html>