<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$stats = [];
$pdo = getConnection();

if ($pdo) {
    try {
        $stats['crops'] = $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn();
        $stats['markets'] = $pdo->query("SELECT COUNT(*) FROM markets")->fetchColumn();
        $stats['regions'] = $pdo->query("SELECT COUNT(*) FROM regions")->fetchColumn();
        $stats['farmers'] = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn();
        $stats['price_records'] = $pdo->query("SELECT COUNT(*) FROM market_prices")->fetchColumn();
        $stats['supply_records'] = $pdo->query("SELECT COUNT(*) FROM market_supply")->fetchColumn();
    } catch (PDOException $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.3);
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .farmer-portal {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(34, 197, 94, 0.3);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.1);
        }

        .feature-icon {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Simple Top Navigation -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-lg">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">AgriSense</h1>
                            <p class="text-xs text-emerald-600">Market Intelligence</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($currentUser['name']) ?></p>
                        <p class="text-xs text-emerald-600"><?= htmlspecialchars($currentUser['email']) ?></p>
                    </div>
                    <a href="/agrisense/auth/logout.php"
                        class="px-4 py-2 glass-card rounded-lg text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50 transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Welcome back,
                <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>! 👋</h1>
            <p class="text-gray-600">Here's your agricultural market intelligence overview</p>
        </div>

        <!-- Farmer Portal (Prominently Featured) -->
        <div class="farmer-portal rounded-2xl p-8 mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div class="mb-6 md:mb-0">
                    <div class="flex items-center mb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mr-4">
                            <span class="text-2xl text-white">👨‍🌾</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Farmer Portal</h2>
                            <p class="text-emerald-600">Secure data submission for registered farmers</p>
                        </div>
                    </div>
                    <ul class="space-y-2 mt-4">
                        <li class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update crop supply records
                        </li>
                        <li class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            6-digit secure verification
                        </li>
                        <li class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Contribute to market intelligence
                        </li>
                    </ul>
                </div>
                <a href="farmer/verify_code.php"
                    class="btn-primary px-8 py-3 rounded-xl font-medium text-lg inline-flex items-center shadow-lg">
                    <span class="mr-3">🚜</span> Enter Farmer Portal →
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <?php if (!empty($stats)): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="stats-card rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Crops</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['crops'] ?? '-' ?></p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-green-100 border border-emerald-200 flex items-center justify-center">
                            <span class="text-emerald-600">🌾</span>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Markets</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['markets'] ?? '-' ?></p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-green-100 border border-emerald-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Regions</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['regions'] ?? '-' ?></p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-green-100 border border-emerald-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Farmers</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['farmers'] ?? '-' ?></p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-green-100 border border-emerald-200 flex items-center justify-center">
                            <span class="text-emerald-600">👨‍🌾</span>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Price Records</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['price_records'] ?? '-' ?></p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-green-100 border border-emerald-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Supply Records</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['supply_records'] ?? '-' ?></p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-green-100 border border-emerald-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Market Analytics Features -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Market Analytics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Inter-Market Price Gap Analysis -->
                <a href="pages/market_price_gap.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">🔄</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Price Gap</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Compare crop prices across different markets</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>Compare Markets</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Historical Price Trend Analysis -->
                <a href="pages/price_trend.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">📈</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Price Trend</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Historical price analysis and trends</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>View Trends</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Top Crop by Region -->
                <a href="pages/top_crop_region.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">🏆</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Top Crop</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Highest revenue generating crops by region</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>View Rankings</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Top Farmer by Region -->
                <a href="pages/top_farmer_region.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">👨‍🌾</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Top Farmer</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Leading farmers by revenue contribution</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>View Leaders</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Smart Market Recommendation -->
                <a href="pages/smart_market.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">🎯</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Smart Market</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Best markets for selling based on price & saturation</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>Get Recommendations</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Seasonal Price Memory -->
                <a href="pages/seasonal_price_memory.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">📅</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Price Memory</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Compare prices with same period last year</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>Compare Seasons</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Crop Over-Supply Detection -->
                <a href="pages/oversupply_alert.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">⚠️</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Over-Supply Alert</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Detect crops with abnormally high supply</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>Detect Anomalies</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>

                <!-- Climate Risk Advisory -->
                <a href="pages/climate_risk_dashboard.php" class="glass-card rounded-xl p-6 block hover:no-underline">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg feature-icon flex items-center justify-center mr-4">
                            <span class="text-2xl text-emerald-600">🌦️</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Climate Risk</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Region-wise climate risk advisories</p>
                    <div class="flex items-center text-emerald-600 font-medium">
                        <span>View Advisories</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </main>
</body>

</html>