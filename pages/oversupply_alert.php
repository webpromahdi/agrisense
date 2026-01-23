<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;
$threshold = 40; // Default threshold of 40%

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['analyze'])) {
    $threshold = isset($_POST['threshold']) ? (int) $_POST['threshold'] : 40;

    if ($threshold < 10)
        $threshold = 10;
    if ($threshold > 100)
        $threshold = 100;

    // Query to detect oversupply based on comparing recent supply with historical average
    // Using sample data context - adjust dates as needed
    $sql = "
        SELECT 
            c.crop_id,
            c.crop_name,
            c.category,
            COALESCE(recent.recent_supply, 0) AS recent_supply,
            COALESCE(ROUND(historical.avg_supply, 2), 0) AS avg_supply,
            ROUND(
                ((COALESCE(recent.recent_supply, 0) - COALESCE(historical.avg_supply, 0)) 
                 / NULLIF(historical.avg_supply, 0)) * 100,
                2
            ) AS growth_percent,
            COALESCE(recent.farmer_count, 0) AS farmer_count,
            COALESCE(recent.market_count, 0) AS market_count,
            CASE 
                WHEN ((COALESCE(recent.recent_supply, 0) - COALESCE(historical.avg_supply, 0)) 
                      / NULLIF(historical.avg_supply, 0)) * 100 > ? 
                THEN 'HIGH'
                WHEN ((COALESCE(recent.recent_supply, 0) - COALESCE(historical.avg_supply, 0)) 
                      / NULLIF(historical.avg_supply, 0)) * 100 > (? / 2) 
                THEN 'ELEVATED'
                ELSE 'NORMAL'
            END AS risk_label
        FROM 
            crops c
            LEFT JOIN (
                SELECT 
                    crop_id,
                    SUM(quantity) AS recent_supply,
                    COUNT(DISTINCT farmer_id) AS farmer_count,
                    COUNT(DISTINCT market_id) AS market_count
                FROM 
                    market_supply
                WHERE 
                    supply_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY 
                    crop_id
            ) recent ON c.crop_id = recent.crop_id
            LEFT JOIN (
                SELECT 
                    crop_id,
                    AVG(quantity) * 30 AS avg_supply
                FROM 
                    market_supply
                WHERE 
                    supply_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY 
                    crop_id
            ) historical ON c.crop_id = historical.crop_id
        WHERE 
            recent.recent_supply IS NOT NULL
        ORDER BY 
            growth_percent DESC
    ";

    $pdo = getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$threshold, $threshold]);
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = "Query Error: " . $e->getMessage();
        }
    } else {
        $error = "Database connection failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Over-Supply Alert - AgriSense</title>
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

        .risk-high {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .risk-elevated {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            border-left: 4px solid #d97706;
        }

        .risk-normal {
            background: rgba(34, 197, 94, 0.15);
            color: #059669;
            border-left: 4px solid #059669;
        }

        .badge-high {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
        }

        .badge-elevated {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
        }

        .badge-normal {
            background: rgba(34, 197, 94, 0.15);
            color: #059669;
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-lg">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">AgriSense</h1>
                            <p class="text-xs text-emerald-600">Over-Supply Detection</p>
                        </div>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-medium text-gray-800">
                            <?= htmlspecialchars($currentUser['name']) ?>
                        </p>
                        <p class="text-xs text-emerald-600">
                            <?= htmlspecialchars($currentUser['email']) ?>
                        </p>
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
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">⚠️ Crop Over-Supply Detection</h1>
            <p class="text-gray-600">Identify crops with unusually high recent supply compared to historical average</p>

            <!-- Important Notice -->
            <div class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">📊</span>
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-1">Detection, Not Prediction</h3>
                        <p class="text-sm text-gray-600">
                            This feature <strong>detects current anomalies</strong> in supply data by comparing the
                            <strong>last 30 days</strong> with historical averages. It does NOT predict future supply.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="threshold" class="block text-sm font-medium text-gray-700 mb-2">
                        Alert Threshold (% above average)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="threshold_range" min="10" max="100" value="<?= $threshold ?>"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            oninput="document.getElementById('threshold').value = this.value; document.getElementById('threshold_display').textContent = this.value + '%';">
                        <input type="number" id="threshold" name="threshold" min="10" max="100"
                            value="<?= $threshold ?>"
                            class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            oninput="document.getElementById('threshold_range').value = this.value; document.getElementById('threshold_display').textContent = this.value + '%';">
                        <span id="threshold_display" class="text-lg font-bold text-emerald-700 w-16">
                            <?= $threshold ?>%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Crops with supply growth above this threshold will be flagged
                        as HIGH risk</p>
                </div>
                <button type="submit" class="px-6 py-2 btn-primary rounded-lg font-medium">
                    🔍 Detect Over-Supply
                </button>
            </form>
        </div>

        <!-- Error Display -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                <p class="font-medium">Error</p>
                <p class="text-sm">
                    <?= htmlspecialchars($error) ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <?php if (!empty($results)): ?>
            <?php
            $highCount = count(array_filter($results, fn($r) => $r['risk_label'] === 'HIGH'));
            $elevatedCount = count(array_filter($results, fn($r) => $r['risk_label'] === 'ELEVATED'));
            $normalCount = count(array_filter($results, fn($r) => $r['risk_label'] === 'NORMAL'));
            $totalRecentSupply = array_sum(array_column($results, 'recent_supply'));
            ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-card rounded-xl p-4 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500">HIGH Risk</p>
                    <p class="text-2xl font-bold text-red-600">
                        <?= $highCount ?>
                    </p>
                    <p class="text-xs text-gray-500">crops (&gt;
                        <?= $threshold ?>%)
                    </p>
                </div>
                <div class="glass-card rounded-xl p-4 border-l-4 border-amber-500">
                    <p class="text-sm text-gray-500">ELEVATED Risk</p>
                    <p class="text-2xl font-bold text-amber-600">
                        <?= $elevatedCount ?>
                    </p>
                    <p class="text-xs text-gray-500">crops (&gt;
                        <?= $threshold / 2 ?>%)
                    </p>
                </div>
                <div class="glass-card rounded-xl p-4 border-l-4 border-emerald-500">
                    <p class="text-sm text-gray-500">NORMAL</p>
                    <p class="text-2xl font-bold text-emerald-600">
                        <?= $normalCount ?>
                    </p>
                    <p class="text-xs text-gray-500">crops</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Total Recent Supply</p>
                    <p class="text-2xl font-bold text-blue-600">
                        <?= number_format($totalRecentSupply) ?>
                    </p>
                    <p class="text-xs text-gray-500">kg (last 30 days)</p>
                </div>
            </div>

            <!-- Alert Cards for HIGH risk -->
            <?php
            $highRiskCrops = array_filter($results, fn($r) => $r['risk_label'] === 'HIGH');
            if (!empty($highRiskCrops)):
                ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-red-700 mb-4">🚨 High Risk Alerts</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($highRiskCrops as $row): ?>
                            <div class="glass-card rounded-xl p-5 risk-high">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl">⚠️</span>
                                    <span class="px-3 py-1 bg-red-500 text-white rounded-full text-xs font-bold">
                                        +
                                        <?= number_format($row['growth_percent'], 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-1">
                                    <?= htmlspecialchars($row['crop_name']) ?>
                                </h3>
                                <p class="text-sm text-gray-600 mb-3">
                                    <?= htmlspecialchars($row['category']) ?>
                                </p>

                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Recent Supply:</span>
                                        <span class="font-bold">
                                            <?= number_format($row['recent_supply']) ?> kg
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Avg Supply:</span>
                                        <span class="font-mono">
                                            <?= number_format($row['avg_supply']) ?> kg
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Farmers:</span>
                                        <span>
                                            <?= $row['farmer_count'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Full Results Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        All Crops Supply Analysis
                        <span class="text-sm font-normal text-gray-500">
                            (
                            <?= count($results) ?> crops, threshold:
                            <?= $threshold ?>%)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Crop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Recent Supply</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Avg Supply</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Growth %</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <?php
                                $badgeClass = match ($row['risk_label']) {
                                    'HIGH' => 'badge-high',
                                    'ELEVATED' => 'badge-elevated',
                                    default => 'badge-normal'
                                };
                                $riskIcon = match ($row['risk_label']) {
                                    'HIGH' => '🔴',
                                    'ELEVATED' => '🟡',
                                    default => '🟢'
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['crop_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($row['category']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-900">
                                        <?= number_format($row['recent_supply']) ?> kg
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-600">
                                        <?= number_format($row['avg_supply']) ?> kg
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-bold <?= $row['growth_percent'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
                                        <?= $row['growth_percent'] !== null ? ($row['growth_percent'] >= 0 ? '+' : '') . number_format($row['growth_percent'], 1) . '%' : 'N/A' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium <?= $badgeClass ?>">
                                            <?= $riskIcon ?>
                                            <?= $row['risk_label'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
            <div class="glass-card rounded-xl p-6">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">✅</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No over-supply detected</h3>
                    <p class="text-gray-600">All crops are within normal supply ranges based on historical data.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>