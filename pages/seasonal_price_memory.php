<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;
$markets = [];
$selectedMarket = null;
$currentMonth = date('F Y');
$lastYearMonth = date('F Y', strtotime('-1 year'));

$markets = getAllMarkets();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['analyze'])) {
    $selectedMarket = isset($_POST['market_id']) ? (int) $_POST['market_id'] : null;

    // Since our sample data has January 2026 as current and January 2025 as last year,
    // we'll use a flexible query that works with the available data
    $sql = "
        SELECT 
            c.crop_id,
            c.crop_name,
            c.category,
            m.market_name,
            r.region_name,
            ROUND(AVG(current_period.price), 2) AS current_price,
            ROUND(AVG(last_year.price), 2) AS last_year_price,
            ROUND(
                ((AVG(current_period.price) - AVG(last_year.price)) / NULLIF(AVG(last_year.price), 0)) * 100,
                2
            ) AS percent_change,
            CASE 
                WHEN AVG(current_period.price) > AVG(last_year.price) * 1.05 THEN 'UP'
                WHEN AVG(current_period.price) < AVG(last_year.price) * 0.95 THEN 'DOWN'
                ELSE 'STABLE'
            END AS direction
        FROM 
            crops c
            JOIN price_history current_period ON c.crop_id = current_period.crop_id
            JOIN markets m ON current_period.market_id = m.market_id
            JOIN regions r ON m.region_id = r.region_id
            JOIN price_history last_year 
                ON current_period.crop_id = last_year.crop_id
                AND current_period.market_id = last_year.market_id
                AND MONTH(current_period.record_date) = MONTH(last_year.record_date)
                AND YEAR(current_period.record_date) = YEAR(last_year.record_date) + 1
    ";

    $params = [];
    if ($selectedMarket) {
        $sql .= " WHERE m.market_id = :market_id ";
        $params['market_id'] = $selectedMarket;
    }

    $sql .= "
        GROUP BY 
            c.crop_id,
            c.crop_name,
            c.category,
            m.market_id,
            m.market_name,
            r.region_name
        HAVING 
            AVG(last_year.price) IS NOT NULL
        ORDER BY 
            ABS((AVG(current_period.price) - AVG(last_year.price)) / NULLIF(AVG(last_year.price), 0)) DESC
    ";

    $pdo = getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = "Query Error: " . $e->getMessage();
        }
    } else {
        $error = "Database connection failed.";
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

    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }

    .direction-up {
        color: #166534;
    }

    .direction-down {
        color: #B91C1C;
    }

    .direction-stable {
        color: #78716C;
    }

    .badge-up {
        background: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
        font-weight: 600;
    }

    .badge-down {
        background: #FEE2E2;
        color: #B91C1C;
        border: 1px solid #FECACA;
        font-weight: 600;
    }

    .badge-stable {
        background: #F5F5F4;
        color: #44403C;
        border: 1px solid #E7E5E4;
        font-weight: 600;
    }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">📅 Seasonal Price Memory</h1>
            <p class="text-body">Compare current prices with the same period last year</p>

            <!-- Explanation -->
            <div class="mt-4 p-4 bg-white rounded-xl border border-border shadow-sm">
                <h3 class="font-bold text-heading mb-2">One-Season Reminder</h3>
                <p class="text-sm text-body mb-2">
                    This feature shows how prices compare to the <strong>same month last year</strong>:
                </p>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 badge-up rounded-full flex items-center gap-1">
                        <span class="text-lg">⬆</span> Price Increased (&gt;5%)
                    </span>
                    <span class="px-3 py-1 badge-down rounded-full flex items-center gap-1">
                        <span class="text-lg">⬇</span> Price Decreased (&gt;5%)
                    </span>
                    <span class="px-3 py-1 badge-stable rounded-full flex items-center gap-1">
                        <span class="text-lg">→</span> Price Stable (±5%)
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="market_id" class="block text-sm font-semibold text-heading mb-2">
                        Filter by Market (Optional)
                    </label>
                    <select id="market_id" name="market_id"
                        class="w-full px-4 py-2 border border-border-strong rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-body">
                        <option value="">-- All Markets --</option>
                        <?php foreach ($markets as $market): ?>
                            <option value="<?= $market['market_id'] ?>" <?= $selectedMarket == $market['market_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($market['market_name']) ?>
                                (<?= htmlspecialchars($market['region_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 btn-primary rounded-lg font-medium">
                    📊 Compare Prices
                </button>
            </form>
        </div>

        <!-- Error Display -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                <p class="font-medium">Error</p>
                <p class="text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <?php if (!empty($results)): ?>
            <?php
            $upCount = count(array_filter($results, fn($r) => $r['direction'] === 'UP'));
            $downCount = count(array_filter($results, fn($r) => $r['direction'] === 'DOWN'));
            $stableCount = count(array_filter($results, fn($r) => $r['direction'] === 'STABLE'));
            $avgChange = count($results) > 0 ? array_sum(array_column($results, 'percent_change')) / count($results) : 0;
            ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Price Increased</p>
                    <p class="text-2xl font-bold text-emerald-600"><?= $upCount ?></p>
                    <p class="text-xs text-gray-500">crops ⬆</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Price Decreased</p>
                    <p class="text-2xl font-bold text-red-600"><?= $downCount ?></p>
                    <p class="text-xs text-gray-500">crops ⬇</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Price Stable</p>
                    <p class="text-2xl font-bold text-gray-600"><?= $stableCount ?></p>
                    <p class="text-xs text-gray-500">crops →</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Avg Change</p>
                    <p class="text-2xl font-bold <?= $avgChange >= 0 ? 'text-emerald-600' : 'text-red-600' ?>">
                        <?= $avgChange >= 0 ? '+' : '' ?>     <?= number_format($avgChange, 1) ?>%
                    </p>
                    <p class="text-xs text-gray-500">overall</p>
                </div>
            </div>

            <!-- Results Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Year-over-Year Price Comparison
                        <span class="text-sm font-normal text-gray-500">
                            (<?= count($results) ?> records)
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
                                    Market</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Year (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Current (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Change %</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Direction</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <?php
                                $directionIcon = match ($row['direction']) {
                                    'UP' => '⬆',
                                    'DOWN' => '⬇',
                                    default => '→'
                                };
                                $directionClass = match ($row['direction']) {
                                    'UP' => 'direction-up',
                                    'DOWN' => 'direction-down',
                                    default => 'direction-stable'
                                };
                                $badgeClass = match ($row['direction']) {
                                    'UP' => 'badge-up',
                                    'DOWN' => 'badge-down',
                                    default => 'badge-stable'
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($row['crop_name']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['category']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($row['market_name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['region_name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-600">
                                        ৳<?= number_format($row['last_year_price']) ?>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-bold text-gray-900">
                                        ৳<?= number_format($row['current_price']) ?>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-bold <?= $directionClass ?>">
                                        <?= $row['percent_change'] >= 0 ? '+' : '' ?>
                                        <?= number_format($row['percent_change'], 1) ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $badgeClass ?>">
                                            <span class="mr-1"><?= $directionIcon ?></span>
                                            <?= $row['direction'] ?>
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
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <?php if ($selectedMarket): ?>
                        <?php
                        $selectedMarketName = '';
                        foreach ($markets as $m) {
                            if ($m['market_id'] == $selectedMarket) {
                                $selectedMarketName = $m['market_name'];
                                break;
                            }
                        }
                        ?>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Insufficient historical data for this market</h3>
                        <p class="text-gray-600">No year-over-year comparison data available for <strong><?= htmlspecialchars($selectedMarketName) ?></strong>.</p>
                        <p class="text-sm text-gray-500 mt-2">
                            This market may not have complete price history records for both January 2025 and January 2026.
                        </p>
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-left">
                            <p class="text-sm text-blue-800">
                                <strong>💡 Tip:</strong> Try selecting "All Markets" to see available comparisons across all regions.
                            </p>
                        </div>
                    <?php else: ?>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No comparison data available</h3>
                        <p class="text-gray-600">No matching price records found for year-over-year comparison.</p>
                        <p class="text-sm text-gray-500 mt-2">
                            This requires price data from both the current period (January 2026) and the same period last year (January 2025).
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>