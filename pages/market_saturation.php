<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;
$crops = [];
$filterCrop = null;

$crops = getAllCrops();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['analyze'])) {
    $filterCrop = isset($_POST['crop_id']) ? (int) $_POST['crop_id'] : null;

    $sql = "
        SELECT 
            m.market_name,
            r.region_name,
            c.crop_name,
            SUM(ms.quantity) AS total_supply,
            COUNT(DISTINCT ms.farmer_id) AS farmer_count,
            ROUND(
                SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id), 
                2
            ) AS saturation_index,
            ROUND(AVG(ms.price_per_unit), 2) AS avg_price,
            CASE 
                WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 150 THEN 'HIGH'
                WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 100 THEN 'MEDIUM'
                ELSE 'LOW'
            END AS saturation_level
        FROM 
            market_supply ms
            JOIN markets m ON ms.market_id = m.market_id
            JOIN regions r ON m.region_id = r.region_id
            JOIN crops c ON ms.crop_id = c.crop_id
    ";

    $params = [];
    if ($filterCrop) {
        $sql .= " WHERE ms.crop_id = :crop_id ";
        $params['crop_id'] = $filterCrop;
    }

    $sql .= "
        GROUP BY 
            m.market_id, 
            m.market_name, 
            r.region_name,
            c.crop_id,
            c.crop_name
        ORDER BY 
            SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) DESC
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
    
    .saturation-high {
        background: #FEE2E2;
        color: #B91C1C;
    }
    
    .saturation-medium {
        background: #FEF3C7;
        color: #92400E;
    }
    
    .saturation-low {
        background: #DCFCE7;
        color: #166534;
    }

    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">📊 Market Saturation Analysis</h1>
            <p class="text-body">Identify markets with high supply concentration and potential price drops</p>
            
            <!-- Saturation Explanation -->
            <div class="mt-4 p-4 bg-white rounded-xl border border-border shadow-sm">
                <h3 class="font-semibold text-heading mb-2">Understanding Saturation Index</h3>
               
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 saturation-high rounded-full">HIGH: &gt;150 (Risk of price crash)</span>
                    <span class="px-3 py-1 saturation-medium rounded-full">MEDIUM: 100-150 (Monitor closely)</span>
                    <span class="px-3 py-1 saturation-low rounded-full">LOW: &lt;100 (Healthy market)</span>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="crop_id" class="block text-sm font-medium text-heading mb-2">
                        Filter by Crop (Optional)
                    </label>
                    <select id="crop_id" name="crop_id"
                        class="w-full px-4 py-2 border border-border-strong rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">-- All Crops --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $filterCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-6 py-2 btn-primary rounded-lg font-medium">
                    Calculate Saturation
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
            $highCount = count(array_filter($results, fn($r) => $r['saturation_level'] === 'HIGH'));
            $mediumCount = count(array_filter($results, fn($r) => $r['saturation_level'] === 'MEDIUM'));
            $lowCount = count(array_filter($results, fn($r) => $r['saturation_level'] === 'LOW'));
            $avgSaturation = count($results) > 0 ? array_sum(array_column($results, 'saturation_index')) / count($results) : 0;
            ?>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">High Saturation</p>
                    <p class="text-2xl font-bold text-red-600"><?= $highCount ?></p>
                    <p class="text-xs text-gray-500">markets at risk</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Medium Saturation</p>
                    <p class="text-2xl font-bold text-amber-600"><?= $mediumCount ?></p>
                    <p class="text-xs text-gray-500">need monitoring</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Low Saturation</p>
                    <p class="text-2xl font-bold text-emerald-600"><?= $lowCount ?></p>
                    <p class="text-xs text-gray-500">healthy markets</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Avg Index</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($avgSaturation, 1) ?></p>
                    <p class="text-xs text-gray-500">kg/farmer</p>
                </div>
            </div>

            <!-- Saturation Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Market Saturation Analysis
                        <span class="text-sm font-normal text-gray-500">
                            (<?= count($results) ?> market-crop combinations)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Market</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Supply</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Farmers</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saturation Index</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <?php
                                $levelClass = match ($row['saturation_level']) {
                                    'HIGH' => 'saturation-high',
                                    'MEDIUM' => 'saturation-medium',
                                    default => 'saturation-low'
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['market_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($row['region_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?= htmlspecialchars($row['crop_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-900">
                                        <?= number_format($row['total_supply']) ?> kg
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-700">
                                        <?= $row['farmer_count'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <span class="font-mono font-bold text-gray-900">
                                            <?= number_format($row['saturation_index'], 2) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium <?= $levelClass ?>">
                                            <?= $row['saturation_level'] ?>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No supply data found</h3>
                    <p class="text-gray-600">No market supply records found for the selected criteria.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>