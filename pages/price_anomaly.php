<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;
$threshold = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['analyze'])) {
    $threshold = isset($_POST['threshold']) ? (float) $_POST['threshold'] : 20;
    $threshold = max(5, min(50, $threshold));

    $sql = "
        SELECT 
            c.crop_name,
            m.market_name,
            mp.current_price,
            ROUND(avg_prices.avg_price, 2) AS avg_price,
            ROUND(
                ((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) * 100, 
                2
            ) AS deviation_percentage
        FROM 
            market_prices mp
            JOIN crops c ON mp.crop_id = c.crop_id
            JOIN markets m ON mp.market_id = m.market_id
            JOIN (
                SELECT 
                    crop_id,
                    AVG(current_price) AS avg_price
                FROM 
                    market_prices
                GROUP BY 
                    crop_id
            ) avg_prices ON mp.crop_id = avg_prices.crop_id
        WHERE 
            ABS((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) > :threshold
        ORDER BY 
            ABS((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) DESC
    ";

    $pdo = getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['threshold' => $threshold / 100]);
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
    
    .table-header {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    }

    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">📊 Price Anomaly Detection</h1>
            <p class="text-body">Detect crops with prices deviating significantly from market averages</p>
        </div>

        <!-- Controls -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="threshold" class="block text-sm font-medium text-heading mb-2">
                        Deviation Threshold (%)
                    </label>
                    <input type="number" id="threshold" name="threshold" value="<?= htmlspecialchars($threshold) ?>"
                        min="5" max="50" step="5"
                        class="w-full sm:w-64 px-4 py-2 border border-border-strong rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-muted mt-2">Prices deviating more than this percentage will be flagged</p>
                </div>
                <button type="submit"
                    class="px-6 py-2 btn-primary rounded-lg font-medium">
                    Analyze Anomalies
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
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-emerald-100 table-header">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Detected Anomalies
                        <span class="text-sm font-normal text-gray-500">
                            (<?= count($results) ?> found with ±<?= $threshold ?>% threshold)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Market</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deviation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['crop_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($row['market_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        ৳<?= number_format($row['current_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                        ৳<?= number_format($row['avg_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <?php
                                        $deviation = $row['deviation_percentage'];
                                        $colorClass = $deviation > 0 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                                        $arrow = $deviation > 0 ? '↑' : '↓';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium <?= $colorClass ?>">
                                            <?= $arrow ?> <?= abs($deviation) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="glass-card rounded-xl p-6">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No anomalies detected</h3>
                    <p class="text-gray-600">All crop prices are within ±<?= $threshold ?>% of market averages.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>