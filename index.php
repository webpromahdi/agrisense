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
    <style>
        /* Smooth hover transitions for cards */
        .feature-card {
            transition: all 0.3s ease-in-out;
        }

        .feature-card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-green-700 to-green-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <span class="text-3xl">🌾</span>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">AgriSense</h1>
                        <p class="text-xs text-green-100 font-medium">Agricultural Market Intelligence</p>
                    </div>
                </div>

                <!-- User Info & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium"><?= htmlspecialchars($currentUser['name']) ?></p>
                        <p class="text-xs text-green-100"><?= htmlspecialchars($currentUser['email']) ?></p>
                    </div>
                    <a href="/agrisense/auth/logout.php"
                        class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border border-white/20">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">

        <!-- Quick Stats -->
        <?php if (!empty($stats)): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border border-slate-100">
                    <p class="text-2xl font-bold text-emerald-600"><?= $stats['crops'] ?? '-' ?></p>
                    <p class="text-sm text-slate-500 font-medium">Crops</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border border-slate-100">
                    <p class="text-2xl font-bold text-emerald-600"><?= $stats['markets'] ?? '-' ?></p>
                    <p class="text-sm text-slate-500 font-medium">Markets</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border border-slate-100">
                    <p class="text-2xl font-bold text-emerald-600"><?= $stats['regions'] ?? '-' ?></p>
                    <p class="text-sm text-slate-500 font-medium">Regions</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border border-slate-100">
                    <p class="text-2xl font-bold text-emerald-600"><?= $stats['farmers'] ?? '-' ?></p>
                    <p class="text-sm text-slate-500 font-medium">Farmers</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border border-slate-100">
                    <p class="text-2xl font-bold text-emerald-600"><?= $stats['price_records'] ?? '-' ?></p>
                    <p class="text-sm text-slate-500 font-medium">Price Records</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border border-slate-100">
                    <p class="text-2xl font-bold text-emerald-600"><?= $stats['supply_records'] ?? '-' ?></p>
                    <p class="text-sm text-slate-500 font-medium">Supply Records</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Category A: Market Intelligence Features -->
        <section class="mb-12">
            <div class="flex items-start mb-8">
                <div class="w-1 h-12 bg-gradient-to-b from-green-500 to-emerald-600 rounded-full mr-4"></div>
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800">Market Intelligence Features</h2>
                    <p class="text-slate-500 mt-1">Data-driven insights from agricultural markets</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Feature A1: Price Anomaly Detection -->
                <div
                    class="feature-card bg-white rounded-xl shadow-md hover:shadow-xl overflow-hidden border border-slate-100 flex flex-col">
                    <div class="bg-gradient-to-r from-rose-400 to-rose-500 text-white px-6 py-5">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">📊</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Price Anomaly Detection</h3>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <p class="text-slate-600 mb-5 leading-relaxed flex-grow">
                            Detect crops whose prices deviate more than ±20% from average prices across all markets.
                        </p>
                        <a href="pages/price_anomaly.php"
                            class="block w-full text-center px-4 py-2.5 bg-rose-400 hover:bg-rose-500 text-white rounded-lg font-medium transition-colors duration-200">
                            Analyze Anomalies →
                        </a>
                    </div>
                </div>

                <!-- Feature A5: Most Profitable Crop by Region -->
                <div
                    class="feature-card bg-white rounded-xl shadow-md hover:shadow-xl overflow-hidden border border-slate-100 flex flex-col">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-5">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">🏆</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Top Crop by Region</h3>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <p class="text-slate-600 mb-5 leading-relaxed flex-grow">
                            Find which crop generates the highest revenue in each region.
                        </p>
                        <a href="pages/top_crop_region.php"
                            class="block w-full text-center px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium transition-colors duration-200">
                            Find Top Crops →
                        </a>
                    </div>
                </div>

                <!-- Top Farmer by Region Feature -->
                <div
                    class="feature-card bg-white rounded-xl shadow-md hover:shadow-xl overflow-hidden border border-slate-100 flex flex-col">
                    <div class="bg-gradient-to-r from-sky-400 to-sky-500 text-white px-6 py-5">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">👨‍🌾</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Top Farmer by Region</h3>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <p class="text-slate-600 mb-5 leading-relaxed flex-grow">
                            Identify the farmer with highest total revenue in each region.
                        </p>
                        <a href="pages/top_farmer_region.php"
                            class="block w-full text-center px-4 py-2.5 bg-sky-400 hover:bg-sky-500 text-white rounded-lg font-medium transition-colors duration-200">
                            View Top Farmers →
                        </a>
                    </div>
                </div>

                <!-- Farmer Portal: Crop Data Update -->
                <div
                    class="feature-card bg-white rounded-xl shadow-md hover:shadow-xl overflow-hidden border border-slate-100 flex flex-col">
                    <div class="bg-gradient-to-r from-amber-400 to-amber-500 text-white px-6 py-5">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">🌾</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Farmer Portal</h3>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <p class="text-slate-600 mb-5 leading-relaxed flex-grow">
                            Farmers can update their crop supply data using their unique 6-digit code.
                        </p>
                        <a href="farmer/verify_code.php"
                            class="block w-full text-center px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-white rounded-lg font-medium transition-colors duration-200">
                            Enter Farmer Portal →
                        </a>
                    </div>
                </div>

                <!-- Feature: Inter-Market Price Gap Analysis -->
                <div
                    class="feature-card bg-white rounded-xl shadow-md hover:shadow-xl overflow-hidden border border-slate-100 flex flex-col">
                    <div class="bg-gradient-to-r from-violet-500 to-violet-600 text-white px-6 py-5">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">🔄</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Price Gap Analysis</h3>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <p class="text-slate-600 mb-5 leading-relaxed flex-grow">
                            Compare crop prices across markets to identify significant price differences.
                        </p>
                        <a href="pages/market_price_gap.php"
                            class="block w-full text-center px-4 py-2.5 bg-violet-500 hover:bg-violet-600 text-white rounded-lg font-medium transition-colors duration-200">
                            Analyze Price Gaps →
                        </a>
                    </div>
                </div>

                <!-- Feature: Historical Price Trend Analysis -->
                <div
                    class="feature-card bg-white rounded-xl shadow-md hover:shadow-xl overflow-hidden border border-slate-100 flex flex-col">
                    <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white px-6 py-5">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl">📈</span>
                        </div>
                        <h3 class="text-xl font-bold mt-2">Price Trend Analysis</h3>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <p class="text-slate-600 mb-5 leading-relaxed flex-grow">
                            Analyze month-wise price trends using historical price data.
                        </p>
                        <a href="pages/price_trend.php"
                            class="block w-full text-center px-4 py-2.5 bg-teal-500 hover:bg-teal-600 text-white rounded-lg font-medium transition-colors duration-200">
                            View Price Trends →
                        </a>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <p class="text-center text-sm text-slate-500">
                AgriSense — Agricultural Market Intelligence & Analytical Database System
            </p>
        </div>
    </footer>

</body>

</html>