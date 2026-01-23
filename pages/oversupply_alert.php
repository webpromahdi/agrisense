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
    }
}
?>

<?php include __DIR__ . '/../dashboard/partials/header.php'; ?>

<style>
    .glass-card {
        background: #FFFFFF;
        border: 1px solid #E7E5E4;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
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

    .risk-high {
        background: #FEE2E2;
        color: #B91C1C;
        border-left: 4px solid #DC2626;
    }

    .risk-elevated {
        background: #FEF3C7;
        color: #92400E;
        border-left: 4px solid #D97706;
    }

    .risk-normal {
        background: #DCFCE7;
        color: #166534;
        border-left: 4px solid #16A34A;
    }

    .badge-high {
        background: #FEE2E2;
        color: #B91C1C;
        border: 1px solid #FECACA;
        font-weight: 600;
    }

    .badge-elevated {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
        font-weight: 600;
    }

    .badge-normal {
        background: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
        font-weight: 600;
    }

    /* Text Colors */
    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">⚠️ Crop Over-Supply Detection</h1>
            <p class="text-body">Identify crops with unusually high recent supply compared to historical average</p>

            <!-- Important Notice -->
            <div class="mt-4 p-4 bg-white rounded-xl border border-border shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">📊</span>
                    <div>
                        <h3 class="font-bold text-heading mb-1">Detection, Not Prediction</h3>
                        <p class="text-sm text-body">
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

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>