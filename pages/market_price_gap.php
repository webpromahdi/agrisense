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
    $selectedCrop = isset($_POST['crop_id']) ? (int) $_POST['crop_id'] :
        (isset($_GET['crop_id']) ? (int) $_GET['crop_id'] : null);

    if ($selectedCrop) {
        $sql = "
            SELECT 
                c.crop_name,
                ma.market_name AS market_a_name,
                mp_a.current_price AS market_a_price,
                mb.market_name AS market_b_name,
                mp_b.current_price AS market_b_price,
                ABS(mp_a.current_price - mp_b.current_price) AS price_gap,
                ROUND(
                    (ABS(mp_a.current_price - mp_b.current_price) / 
                     LEAST(mp_a.current_price, mp_b.current_price)) * 100,
                    2
                ) AS gap_percentage,
                CASE 
                    WHEN mp_a.current_price > mp_b.current_price THEN 'Market A Higher'
                    WHEN mp_a.current_price < mp_b.current_price THEN 'Market B Higher'
                    ELSE 'Equal'
                END AS price_comparison
            FROM 
                market_prices mp_a
                JOIN market_prices mp_b 
                    ON mp_a.crop_id = mp_b.crop_id 
                    AND mp_a.market_id < mp_b.market_id
                JOIN crops c ON mp_a.crop_id = c.crop_id
                JOIN markets ma ON mp_a.market_id = ma.market_id
                JOIN markets mb ON mp_b.market_id = mb.market_id
            WHERE 
                mp_a.crop_id = :crop_id
            ORDER BY 
                ABS(mp_a.current_price - mp_b.current_price) DESC
        ";

        $pdo = getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['crop_id' => $selectedCrop]);
                $results = $stmt->fetchAll();

                $cropStmt = $pdo->prepare("SELECT crop_name FROM crops WHERE crop_id = :crop_id");
                $cropStmt->execute(['crop_id' => $selectedCrop]);
                $cropData = $cropStmt->fetch();
                $cropName = $cropData ? $cropData['crop_name'] : '';

            } catch (PDOException $e) {
                $error = "Query Error: " . $e->getMessage();
            }
        } else {
            $error = "Database connection failed.";
        }
    } else {
        $error = "Please select a crop to analyze.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Gap Analysis - AgriSense</title>
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
                            <p class="text-xs text-emerald-600">Price Gap Analysis</p>
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
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Price Gap Analysis</h1>
            <p class="text-gray-600">Compare crop prices across different markets</p>
        </div>

        <!-- Crop Selection -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="crop_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Crop
                    </label>
                    <select id="crop_id" name="crop_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-6 py-2 btn-primary rounded-lg font-medium">
                    Analyze Price Gaps
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
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Price Gaps for <?= htmlspecialchars($cropName) ?>
                        <span class="text-sm font-normal text-gray-500">
                            (<?= count($results) ?> market comparisons)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Market A</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price A (৳)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Market B</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price B (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price Gap (৳)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Gap %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($row['market_a_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium <?= $row['price_comparison'] === 'Market A Higher' ? 'text-emerald-600' : 'text-gray-900' ?>">
                                        ৳<?= number_format($row['market_a_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($row['market_b_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium <?= $row['price_comparison'] === 'Market B Higher' ? 'text-emerald-600' : 'text-gray-900' ?>">
                                        ৳<?= number_format($row['market_b_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                                        ৳<?= number_format($row['price_gap'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <?php
                                        $gap = $row['gap_percentage'];
                                        $badgeClass = $gap > 20 ? 'bg-red-100 text-red-800' :
                                                     ($gap > 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-emerald-100 text-emerald-800');
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium <?= $badgeClass ?>">
                                            <?= $gap ?>%
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No price data found</h3>
                    <p class="text-gray-600">No market price records found for the selected crop across multiple markets.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>