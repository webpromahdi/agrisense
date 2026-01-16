<?php
/**
 * AgriSense - Feature A3: Historical Price Trend Analysis
 * 
 * Shows month-wise price trends for a selected crop using historical data.
 * Uses GROUP BY month and AVG() for aggregation.
 */

require_once __DIR__ . '/../db/connection.php';

$results = [];
$error = null;
$crops = [];
$selectedCrop = null;
$cropName = '';

// Fetch all crops for dropdown
$crops = getAllCrops();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['crop_id'])) {
    $selectedCrop = isset($_POST['crop_id']) ? (int) $_POST['crop_id'] :
        (isset($_GET['crop_id']) ? (int) $_GET['crop_id'] : null);

    if ($selectedCrop) {
        // SQL Query: Historical Price Trend Analysis
        // Uses DATE_FORMAT for month grouping and AVG for price aggregation
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

                // Get crop name for display
                $cropStmt = $pdo->prepare("SELECT crop_name FROM crops WHERE crop_id = :crop_id");
                $cropStmt->execute(['crop_id' => $selectedCrop]);
                $cropData = $cropStmt->fetch();
                $cropName = $cropData ? $cropData['crop_name'] : '';

            } catch (PDOException $e) {
                $error = "Query Error: " . $e->getMessage();
            }
        } else {
            $error = "Database connection failed. Please check your configuration.";
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
    <title>Historical Price Trend - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-green-700 to-green-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="../index.php" class="flex items-center space-x-2 hover:opacity-90 transition-opacity">
                    <span class="text-2xl">🌾</span>
                    <span class="text-xl font-bold">AgriSense</span>
                </a>
                <span class="text-green-100 text-sm font-medium">Market Intelligence System</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-slate-100">
            <div class="flex items-center space-x-3 mb-3">
                <span class="text-3xl">📈</span>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Historical Price Trend Analysis</h1>
            </div>
            <p class="text-slate-600 leading-relaxed">
                Analyze month-wise price trends for crops using historical data.
                Understand seasonal patterns and price fluctuations over time.
            </p>
        </div>

        <!-- Crop Selection Form -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-slate-100">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">Select Crop for Analysis</h2>
            <form method="POST" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[250px]">
                    <label for="crop_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Crop
                    </label>
                    <select id="crop_id" name="crop_id" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white">
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?> (<?= ucfirst($crop['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 bg-teal-500 hover:bg-teal-600 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
                    📊 Analyze Trends
                </button>
            </form>
        </div>

        <!-- Error Display -->
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-lg">
                <p class="font-bold">Error</p>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <?php if (!empty($results)): ?>

            <!-- Summary Cards -->
            <?php
            $prices = array_column($results, 'avg_price');
            $quantities = array_column($results, 'total_quantity');
            $overallAvg = count($prices) > 0 ? array_sum($prices) / count($prices) : 0;
            $totalQty = array_sum($quantities);
            $priceChange = count($prices) >= 2 ? $prices[count($prices) - 1] - $prices[0] : 0;
            $priceChangePercent = count($prices) >= 2 && $prices[0] > 0 ? ($priceChange / $prices[0]) * 100 : 0;
            ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-emerald-500">
                    <p class="text-sm text-slate-500 font-medium">Crop Analyzed</p>
                    <p class="text-xl font-bold text-emerald-600"><?= htmlspecialchars($cropName) ?></p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-slate-400">
                    <p class="text-sm text-slate-500 font-medium">Average Price</p>
                    <p class="text-xl font-bold text-slate-700">৳<?= number_format($overallAvg, 2) ?></p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-<?= $priceChange >= 0 ? 'emerald' : 'rose' ?>-400">
                    <p class="text-sm text-slate-500 font-medium">Price Change</p>
                    <p class="text-xl font-bold <?= $priceChange >= 0 ? 'text-emerald-600' : 'text-rose-500' ?>">
                        <?= $priceChange >= 0 ? '↑' : '↓' ?>     <?= abs(number_format($priceChangePercent, 1)) ?>%
                    </p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-amber-500">
                    <p class="text-sm text-slate-500 font-medium">Total Quantity Sold</p>
                    <p class="text-xl font-bold text-amber-600"><?= number_format($totalQty) ?> kg</p>
                </div>
            </div>

            <!-- Price Trend Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-100">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-700">
                        📅 Monthly Price Trends for <?= htmlspecialchars($cropName) ?>
                        <span class="text-sm font-normal text-slate-500">(<?= count($results) ?> months)</span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Month</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Avg Price (৳)</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Min Price (৳)</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Max Price (৳)</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Quantity Sold</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $prevPrice = null;
                            foreach ($results as $index => $row):
                                $currentPrice = $row['avg_price'];
                                $trend = $prevPrice === null ? 'neutral' :
                                    ($currentPrice > $prevPrice ? 'up' :
                                        ($currentPrice < $prevPrice ? 'down' : 'neutral'));
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="font-medium text-slate-900"><?= htmlspecialchars($row['month_name']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-slate-900 font-semibold">
                                        ৳<?= number_format($row['avg_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-sky-600">
                                        ৳<?= number_format($row['min_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-rose-500">
                                        ৳<?= number_format($row['max_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-slate-600">
                                        <?= number_format($row['total_quantity']) ?> kg
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if ($trend === 'up'): ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-emerald-700 bg-emerald-50">↑
                                                Up</span>
                                        <?php elseif ($trend === 'down'): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-rose-700 bg-rose-50">↓
                                                Down</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-slate-600 bg-slate-100">—
                                                Start</span>
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

                <!-- Visual Trend Bar -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-600 mb-3">Price Trend Visualization</h3>
                    <div class="flex items-end gap-1 h-24">
                        <?php
                        $maxPrice = max($prices);
                        foreach ($results as $index => $row):
                            $height = $maxPrice > 0 ? ($row['avg_price'] / $maxPrice) * 100 : 0;
                            ?>
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-teal-500 rounded-t transition-all hover:bg-teal-600"
                                    style="height: <?= $height ?>%"
                                    title="<?= $row['month_name'] ?>: ৳<?= number_format($row['avg_price'], 2) ?>"></div>
                                <span class="text-xs text-slate-500 mt-1 transform -rotate-45 origin-left">
                                    <?= substr($row['month_key'], 5) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
            <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-700 p-4 rounded-lg">
                <p class="font-bold">⚠️ No Historical Data Found</p>
                <p>No price history records found for the selected crop.</p>
            </div>
        <?php else: ?>
            <div class="bg-sky-50 border-l-4 border-sky-400 text-sky-700 p-4 rounded-lg">
                <p class="font-bold">ℹ️ Getting Started</p>
                <p>Select a crop from the dropdown to view its historical price trends.</p>
            </div>
        <?php endif; ?>


        <!-- Back Navigation -->
        <div class="mt-8">
            <a href="../index.php"
                class="inline-flex items-center text-slate-600 hover:text-green-600 transition-colors duration-200">
                <span class="mr-2">←</span> Back to Dashboard
            </a>
        </div>
    </main>
</body>

</html>