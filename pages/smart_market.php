<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;
$crops = [];
$selectedCrop = null;
$cropName = '';

$crops = getAllCrops();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['crop_id'])) {
    $selectedCrop = isset($_POST['crop_id']) ? (int) $_POST['crop_id'] : (isset($_GET['crop_id']) ? (int) $_GET['crop_id'] : null);

    if ($selectedCrop) {
        // Get crop name for display
        foreach ($crops as $crop) {
            if ($crop['crop_id'] == $selectedCrop) {
                $cropName = $crop['crop_name'];
                break;
            }
        }

        $sql = "
            SELECT 
                m.market_id,
                m.market_name,
                r.region_name,
                ROUND(AVG(mp.current_price), 2) AS avg_price,
                COALESCE(
                    ROUND(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 2), 
                    0
                ) AS saturation_index,
                COUNT(DISTINCT ms.farmer_id) AS active_farmers,
                COALESCE(SUM(ms.quantity), 0) AS total_supply,
                ROUND(
                    (AVG(mp.current_price) / 100) - (COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) / 10),
                    2
                ) AS recommendation_score,
                CASE 
                    WHEN COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) < 50 
                        AND AVG(mp.current_price) > (
                            SELECT AVG(mp2.current_price) 
                            FROM market_prices mp2 
                            WHERE mp2.crop_id = :crop_id_sub
                        )
                    THEN 'Highly Recommended'
                    WHEN COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) < 100
                    THEN 'Recommended'
                    WHEN COALESCE(SUM(ms.quantity) / NULLIF(COUNT(DISTINCT ms.farmer_id), 0), 0) < 150
                    THEN 'Consider'
                    ELSE 'Saturated'
                END AS recommendation_note
            FROM 
                markets m
                JOIN regions r ON m.region_id = r.region_id
                LEFT JOIN market_prices mp ON m.market_id = mp.market_id AND mp.crop_id = :crop_id
                LEFT JOIN market_supply ms ON m.market_id = ms.market_id AND ms.crop_id = :crop_id2
            WHERE 
                mp.crop_id = :crop_id3
            GROUP BY 
                m.market_id, 
                m.market_name, 
                r.region_name,
                mp.crop_id
            ORDER BY 
                recommendation_score DESC,
                avg_price DESC,
                saturation_index ASC
        ";

        $pdo = getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'crop_id' => $selectedCrop,
                    'crop_id_sub' => $selectedCrop,
                    'crop_id2' => $selectedCrop,
                    'crop_id3' => $selectedCrop
                ]);
                $results = $stmt->fetchAll();
            } catch (PDOException $e) {
                $error = "Query Error: " . $e->getMessage();
            }
        } else {
            $error = "Database connection failed.";
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

    .rank-1 {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        border-left: 4px solid #D97706;
    }

    .rank-2 {
        background: linear-gradient(135deg, #F5F5F4 0%, #E7E5E4 100%);
        border-left: 4px solid #78716C;
    }

    .rank-3 {
        background: linear-gradient(135deg, #FED7AA 0%, #FDBA74 100%);
        border-left: 4px solid #EA580C;
    }

    .badge-highly {
        background: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
        font-weight: 600;
    }

    .badge-recommended {
        background: #DBEAFE;
        color: #1E40AF;
        border: 1px solid #BFDBFE;
        font-weight: 600;
    }

    .badge-consider {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
        font-weight: 600;
    }

    .badge-saturated {
        background: #FEE2E2;
        color: #B91C1C;
        border: 1px solid #FECACA;
        font-weight: 600;
    }

    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">🎯 Smart Market Recommendation</h1>
            <p class="text-body">Find the best markets for selling your crop based on price and market saturation
            </p>

            <!-- Explanation -->
            <div class="mt-4 p-4 bg-white rounded-xl border border-border shadow-sm">
                <h3 class="font-bold text-heading mb-2">How It Works</h3>
                <p class="text-sm text-body mb-2">
                    This feature uses <strong class="text-primary">SQL-based ranking</strong> to suggest the best markets:
                </p>
                <ul class="text-sm text-body list-disc list-inside space-y-1">
                    <li><strong>Higher Average Price</strong> = Better for selling</li>
                    <li><strong>Lower Saturation</strong> (supply per farmer) = Less competition</li>
                    <li>Markets are ranked by a combined score</li>
                </ul>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="crop_id" class="block text-sm font-semibold text-heading mb-2">
                        Select Crop to Sell
                    </label>
                    <select id="crop_id" name="crop_id" required
                        class="w-full px-4 py-2 border border-border-strong rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-body">
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?> (<?= htmlspecialchars($crop['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 btn-primary rounded-lg">
                    🔍 Find Best Markets
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
            <!-- Top 3 Recommendations Cards -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    🏆 Top Market Recommendations for <?= htmlspecialchars($cropName) ?>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach (array_slice($results, 0, 3) as $index => $row): ?>
                        <?php
                        $rankClass = match ($index) {
                            0 => 'rank-1',
                            1 => 'rank-2',
                            2 => 'rank-3',
                            default => ''
                        };
                        $medal = match ($index) {
                            0 => '🥇',
                            1 => '🥈',
                            2 => '🥉',
                            default => ''
                        };
                        ?>
                        <div class="glass-card rounded-xl p-5 <?= $rankClass ?>">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl"><?= $medal ?></span>
                                <span class="text-sm font-medium text-gray-500">Rank #<?= $index + 1 ?></span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">
                                <?= htmlspecialchars($row['market_name']) ?>
                            </h3>
                            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($row['region_name']) ?></p>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Avg. Price:</span>
                                    <span class="font-bold text-green-700">৳<?= number_format($row['avg_price']) ?>/kg</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Saturation:</span>
                                    <span class="font-mono"><?= number_format($row['saturation_index'], 1) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Active Farmers:</span>
                                    <span><?= $row['active_farmers'] ?></span>
                                </div>
                            </div>

                            <?php
                            $badgeClass = match ($row['recommendation_note']) {
                                'Highly Recommended' => 'badge-highly',
                                'Recommended' => 'badge-recommended',
                                'Consider' => 'badge-consider',
                                default => 'badge-saturated'
                            };
                            ?>
                            <div class="mt-4">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                    <?= $row['recommendation_note'] ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Full Results Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        All Markets for <?= htmlspecialchars($cropName) ?>
                        <span class="text-sm font-normal text-gray-500">
                            (<?= count($results) ?> markets)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Market</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Region</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Avg. Price (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Saturation</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Score</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $index => $row): ?>
                                <?php
                                $badgeClass = match ($row['recommendation_note']) {
                                    'Highly Recommended' => 'badge-highly',
                                    'Recommended' => 'badge-recommended',
                                    'Consider' => 'badge-consider',
                                    default => 'badge-saturated'
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                        #<?= $index + 1 ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['market_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($row['region_name']) ?>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-emerald-700 font-bold">
                                        ৳<?= number_format($row['avg_price']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-700">
                                        <?= number_format($row['saturation_index'], 1) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-blue-700">
                                        <?= number_format($row['recommendation_score'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium <?= $badgeClass ?>">
                                            <?= $row['recommendation_note'] ?>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No market data found</h3>
                    <p class="text-gray-600">No price data available for the selected crop.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>