<?php
/**
 * AgriSense - Feature A1: Price Anomaly Detection
 * 
 * Detects crops whose current market price deviates more than ±20%
 * from the average price across all markets.
 */

require_once __DIR__ . '/../db/connection.php';

$results = [];
$error = null;
$threshold = 20; // Default threshold

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['analyze'])) {
    $threshold = isset($_POST['threshold']) ? (float)$_POST['threshold'] : 20;
    $threshold = max(5, min(50, $threshold)); // Clamp between 5% and 50%
    
    // SQL Query: Price Anomaly Detection
    // All logic performed in SQL - PHP only executes and displays
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
        $error = "Database connection failed. Please check your configuration.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Anomaly Detection - AgriSense</title>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Price Anomaly Detection</h1>
            <p class="text-gray-600">
                Identify crops with prices deviating significantly from market averages.
                This helps detect unusual price movements that may indicate supply issues or market manipulation.
            </p>
        </div>

        <!-- Analysis Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Analysis Parameters</h2>
            <form method="POST" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="threshold" class="block text-sm font-medium text-gray-700 mb-2">
                        Deviation Threshold (%)
                    </label>
                    <input 
                        type="number" 
                        id="threshold" 
                        name="threshold" 
                        value="<?= htmlspecialchars($threshold) ?>"
                        min="5" 
                        max="50" 
                        step="5"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-1">Prices deviating more than this % will be flagged</p>
                </div>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors"
                >
                    🔍 Detect Anomalies
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

        <!-- Results Table -->
        <?php if (!empty($results)): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-700">
                    🚨 Detected Anomalies 
                    <span class="text-sm font-normal text-gray-500">
                        (<?= count($results) ?> found with ±<?= $threshold ?>% threshold)
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Crop Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Market Name
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Current Price (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Average Price (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Deviation (%)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($results as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($row['crop_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                <?= htmlspecialchars($row['market_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 font-mono">
                                ৳<?= number_format($row['current_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600 font-mono">
                                ৳<?= number_format($row['avg_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <?php 
                                $deviation = $row['deviation_percentage'];
                                $colorClass = $deviation > 0 ? 'text-red-600 bg-red-100' : 'text-blue-600 bg-blue-100';
                                $arrow = $deviation > 0 ? '↑' : '↓';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium <?= $colorClass ?>">
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
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
            <p class="font-bold">✅ No Anomalies Detected</p>
            <p>All crop prices are within ±<?= $threshold ?>% of their market averages.</p>
        </div>
        <?php else: ?>
        
        <?php endif; ?>

    </main>

    
</body>
</html>
