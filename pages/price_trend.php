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
                DATE_FORMAT(ph.record_date, '%Y-%m') AS month_key,
                DATE_FORMAT(ph.record_date, '%M %Y') AS month_name,
                ROUND(AVG(ph.price), 2) AS avg_price,
                SUM(ph.quantity_sold) AS total_quantity,
                MIN(ph.price) AS min_price,
                MAX(ph.price) AS max_price,
                COUNT(*) AS record_count
            FROM 
                price_history ph
                JOIN crops c ON ph.crop_id = c.crop_id
            WHERE 
                ph.crop_id = :crop_id
            GROUP BY 
                DATE_FORMAT(ph.record_date, '%Y-%m'),
                DATE_FORMAT(ph.record_date, '%M %Y')
            ORDER BY 
                DATE_FORMAT(ph.record_date, '%Y-%m') ASC
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
    <title>Price Trend Analysis - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* AgriSense - Professional Agriculture Theme */
        body {
            background: #FAFAF9;
            min-height: 100vh;
        }

        .glass-nav {
            background: #166534;
            box-shadow: 0 2px 8px rgba(22, 101, 52, 0.15);
        }

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

        .trend-up {
            background: #DCFCE7;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .trend-down {
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        /* Text Colors */
        .text-heading { color: #1C1917; }
        .text-body { color: #44403C; }
        .text-muted { color: #78716C; }
    </style>
</head>

<body class="min-h-screen">
    <!-- Navigation - Deep Forest Green -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">AgriSense</h1>
                            <p class="text-xs text-white/80 font-medium">Price Trend Analysis</p>
                        </div>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-white"><?= htmlspecialchars($currentUser['name']) ?></p>
                        <p class="text-xs text-white/70"><?= htmlspecialchars($currentUser['email']) ?></p>
                    </div>
                    <a href="/agrisense/auth/logout.php"
                        class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white hover:bg-white/20 transition-colors font-medium">
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
            <h1 class="text-2xl font-bold text-heading mb-2">📈 Price Trend Analysis</h1>
            <p class="text-body">Analyze historical price trends for crops</p>
        </div>

        <!-- Crop Selection -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="crop_id" class="block text-sm font-semibold text-heading mb-2">
                        Select Crop
                    </label>
                    <select id="crop_id" name="crop_id" required
                        class="w-full px-4 py-2 border border-border-strong rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-body">
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 btn-primary rounded-lg">
                    View Trends
                </button>
            </form>
        </div>

        <!-- Error Display -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-destructive-light border border-red-200 rounded-xl text-destructive">
                <p class="font-bold">Error</p>
                <p class="text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <?php if (!empty($results)): ?>
            <?php
            $prices = array_column($results, 'avg_price');
            $totalMonths = count($results);
            $overallAvg = array_sum($prices) / $totalMonths;
            $firstPrice = $prices[0];
            $lastPrice = $prices[count($prices) - 1];
            $priceChange = $lastPrice - $firstPrice;
            $priceChangePercent = $firstPrice > 0 ? ($priceChange / $firstPrice) * 100 : 0;
            ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Crop Analyzed</p>
                    <p class="text-xl font-bold text-green-700"><?= htmlspecialchars($cropName) ?></p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Overall Average</p>
                    <p class="text-xl font-bold text-gray-800">৳<?= number_format($overallAvg, 2) ?></p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Price Change</p>
                    <p class="text-xl font-bold <?= $priceChange >= 0 ? 'text-green-700' : 'text-red-600' ?>">
                        <?= $priceChange >= 0 ? '↑' : '↓' ?> ৳<?= number_format(abs($priceChange), 2) ?>
                    </p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Total Months</p>
                    <p class="text-xl font-bold text-gray-800"><?= $totalMonths ?></p>
                </div>
            </div>

            <!-- Trend Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Monthly Price Trends for <?= htmlspecialchars($cropName) ?>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Month</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Avg Price (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Min Price (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Max Price (৳)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity Sold</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $prevPrice = null;
                            foreach ($results as $index => $row):
                                $currentPrice = $row['avg_price'];
                                $trend = $prevPrice === null ? 'neutral' :
                                    ($currentPrice > $prevPrice ? 'up' :
                                        ($currentPrice < $prevPrice ? 'down' : 'neutral'));
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['month_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                                        ৳<?= number_format($row['avg_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600">
                                        ৳<?= number_format($row['min_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                        ৳<?= number_format($row['max_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                        <?= number_format($row['total_quantity']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <?php if ($trend === 'up'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium trend-up">
                                                ↑ Up
                                            </span>
                                        <?php elseif ($trend === 'down'): ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium trend-down">
                                                ↓ Down
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                —
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                $prevPrice = $currentPrice;
                            endforeach;
                            ?>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No historical data found</h3>
                    <p class="text-gray-600">No price history records found for the selected crop.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>