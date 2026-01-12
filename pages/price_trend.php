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
    $selectedCrop = isset($_POST['crop_id']) ? (int)$_POST['crop_id'] : 
                    (isset($_GET['crop_id']) ? (int)$_GET['crop_id'] : null);
    
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
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-green-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="../index.php" class="text-2xl font-bold">🌾 AgriSense</a>
                <span class="text-green-200">Market Intelligence System</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">📈 Historical Price Trend Analysis</h1>
            <p class="text-gray-600">
                Analyze month-wise price trends for crops using historical data.
                Understand seasonal patterns and price fluctuations over time.
            </p>
        </div>

        <!-- Crop Selection Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Select Crop for Analysis</h2>
            <form method="POST" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[250px]">
                    <label for="crop_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Crop
                    </label>
                    <select 
                        id="crop_id" 
                        name="crop_id" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                        <option 
                            value="<?= $crop['crop_id'] ?>"
                            <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($crop['crop_name']) ?> (<?= ucfirst($crop['category']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors"
                >
                    📊 Analyze Trends
                </button>
            </form>
        </div>

        <!-- Error Display -->
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
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
        $priceChange = count($prices) >= 2 ? $prices[count($prices)-1] - $prices[0] : 0;
        $priceChangePercent = count($prices) >= 2 && $prices[0] > 0 ? ($priceChange / $prices[0]) * 100 : 0;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-500">Crop Analyzed</p>
                <p class="text-xl font-bold text-green-700"><?= htmlspecialchars($cropName) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-500">Average Price</p>
                <p class="text-xl font-bold text-gray-800">৳<?= number_format($overallAvg, 2) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-500">Price Change</p>
                <p class="text-xl font-bold <?= $priceChange >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $priceChange >= 0 ? '↑' : '↓' ?> <?= abs(number_format($priceChangePercent, 1)) ?>%
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-500">Total Quantity Sold</p>
                <p class="text-xl font-bold text-gray-800"><?= number_format($totalQty) ?> Q</p>
            </div>
        </div>
        
        <!-- Price Trend Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-700">
                    📅 Monthly Price Trends for <?= htmlspecialchars($cropName) ?>
                    <span class="text-sm font-normal text-gray-500">
                        (<?= count($results) ?> months)
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Month
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Avg Price (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Min Price (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Max Price (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Quantity Sold
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Trend
                            </th>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($row['month_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900 font-semibold">
                                ৳<?= number_format($row['avg_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-blue-600">
                                ৳<?= number_format($row['min_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-red-600">
                                ৳<?= number_format($row['max_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700">
                                <?= number_format($row['total_quantity']) ?> Q
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if ($trend === 'up'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-green-700 bg-green-100">
                                        ↑ Up
                                    </span>
                                <?php elseif ($trend === 'down'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-red-700 bg-red-100">
                                        ↓ Down
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-gray-700 bg-gray-100">
                                        — Start
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
            
            <!-- Visual Trend Bar -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">Price Trend Visualization</h3>
                <div class="flex items-end gap-1 h-24">
                    <?php 
                    $maxPrice = max($prices);
                    foreach ($results as $index => $row): 
                        $height = $maxPrice > 0 ? ($row['avg_price'] / $maxPrice) * 100 : 0;
                    ?>
                    <div class="flex-1 flex flex-col items-center">
                        <div 
                            class="w-full bg-green-500 rounded-t transition-all hover:bg-green-600"
                            style="height: <?= $height ?>%"
                            title="<?= $row['month_name'] ?>: ৳<?= number_format($row['avg_price'], 2) ?>"
                        ></div>
                        <span class="text-xs text-gray-500 mt-1 transform -rotate-45 origin-left">
                            <?= substr($row['month_key'], 5) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
            <p class="font-bold">⚠️ No Historical Data Found</p>
            <p>No price history records found for the selected crop.</p>
        </div>
        <?php else: ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
            <p class="font-bold">ℹ️ Getting Started</p>
            <p>Select a crop from the dropdown to view its historical price trends.</p>
        </div>
        <?php endif; ?>

        <!-- SQL Query Reference -->
        <div class="mt-8 bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-900 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-gray-200">📝 SQL Query Used</h3>
            </div>
            <pre class="p-6 text-sm text-green-400 overflow-x-auto"><code>SELECT 
    DATE_FORMAT(ph.record_date, '%Y-%m') AS month_key,
    DATE_FORMAT(ph.record_date, '%M %Y') AS month_name,
    ROUND(AVG(ph.price), 2) AS avg_price,
    SUM(ph.quantity_sold) AS total_quantity,
    MIN(ph.price) AS min_price,
    MAX(ph.price) AS max_price
FROM 
    price_history ph
    JOIN crops c ON ph.crop_id = c.crop_id
WHERE 
    ph.crop_id = :crop_id
GROUP BY 
    DATE_FORMAT(ph.record_date, '%Y-%m'),
    DATE_FORMAT(ph.record_date, '%M %Y')
ORDER BY 
    DATE_FORMAT(ph.record_date, '%Y-%m') ASC;</code></pre>
        </div>

        <!-- Back Navigation -->
        <div class="mt-6">
            <a href="../index.php" class="inline-flex items-center text-green-600 hover:text-green-800">
                ← Back to Dashboard
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>AgriSense - Agricultural Market Intelligence System</p>
            <p class="text-sm mt-1">DBMS Laboratory Project - Category A: Market Intelligence</p>
        </div>
    </footer>
</body>
</html>
