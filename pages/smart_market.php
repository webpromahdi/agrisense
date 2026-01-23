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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Market Recommendation - AgriSense</title>
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
        
        .rank-1 { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; }
        .rank-2 { background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); border-left: 4px solid #6b7280; }
        .rank-3 { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); border-left: 4px solid #ea580c; }
        
        .badge-highly { background: rgba(34, 197, 94, 0.15); color: #059669; }
        .badge-recommended { background: rgba(59, 130, 246, 0.15); color: #2563eb; }
        .badge-consider { background: rgba(245, 158, 11, 0.15); color: #d97706; }
        .badge-saturated { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-lg">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">AgriSense</h1>
                            <p class="text-xs text-emerald-600">Smart Market Recommendation</p>
                        </div>
                    </a>
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
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">🎯 Smart Market Recommendation</h1>
            <p class="text-gray-600">Find the best markets for selling your crop based on price and market saturation</p>
            
            <!-- Explanation -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-gray-700 mb-2">How It Works</h3>
                <p class="text-sm text-gray-600 mb-2">
                    This feature uses <strong>SQL-based ranking</strong> to suggest the best markets:
                </p>
                <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
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
                    <label for="crop_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Crop to Sell
                    </label>
                    <select id="crop_id" name="crop_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?> (<?= htmlspecialchars($crop['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-6 py-2 btn-primary rounded-lg font-medium">
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
                                    <span class="font-bold text-emerald-700">৳<?= number_format($row['avg_price']) ?>/kg</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Market</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Price (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saturation</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-emerald-700 font-bold">
                                        ৳<?= number_format($row['avg_price']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-700">
                                        <?= number_format($row['saturation_index'], 1) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-blue-700">
                                        <?= number_format($row['recommendation_score'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium <?= $badgeClass ?>">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No market data found</h3>
                    <p class="text-gray-600">No price data available for the selected crop.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
