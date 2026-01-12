<?php
/**
 * AgriSense - Feature A5: Most Profitable Crop by Region
 * Shows top crop by region only - no selection method needed
 */

require_once __DIR__ . '/../db/connection.php';

$results = [];
$error = null;

// Always run the analysis - no form submission needed
$pdo = getConnection();
if ($pdo) {
    try {
        // SQL Query: Most Profitable Crop by Region
        $sql = "
            SELECT 
                region_revenue.region_name,
                region_revenue.state,
                region_revenue.crop_name,
                region_revenue.total_revenue,
                region_revenue.total_quantity,
                region_revenue.avg_price,
                region_revenue.farmer_count
            FROM (
                SELECT 
                    r.region_id,
                    r.region_name,
                    r.state,
                    c.crop_id,
                    c.crop_name,
                    SUM(ms.quantity * ms.price_per_unit) AS total_revenue,
                    SUM(ms.quantity) AS total_quantity,
                    ROUND(AVG(ms.price_per_unit), 2) AS avg_price,
                    COUNT(DISTINCT ms.farmer_id) AS farmer_count
                FROM 
                    market_supply ms
                    JOIN markets m ON ms.market_id = m.market_id
                    JOIN regions r ON m.region_id = r.region_id
                    JOIN crops c ON ms.crop_id = c.crop_id
                GROUP BY 
                    r.region_id, 
                    r.region_name,
                    r.state,
                    c.crop_id, 
                    c.crop_name
            ) region_revenue
            WHERE 
                region_revenue.total_revenue = (
                    SELECT MAX(inner_rev.total_revenue)
                    FROM (
                        SELECT 
                            r2.region_id,
                            SUM(ms2.quantity * ms2.price_per_unit) AS total_revenue
                        FROM 
                            market_supply ms2
                            JOIN markets m2 ON ms2.market_id = m2.market_id
                            JOIN regions r2 ON m2.region_id = r2.region_id
                        GROUP BY 
                            r2.region_id, 
                            ms2.crop_id
                    ) inner_rev
                    WHERE inner_rev.region_id = region_revenue.region_id
                )
            ORDER BY 
                region_revenue.total_revenue DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        $error = "Query Error: " . $e->getMessage();
    }
} else {
    $error = "Database connection failed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Most Profitable Crop by Region - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .medal-1 { color: #FFD700; } /* Gold */
        .medal-2 { color: #C0C0C0; } /* Silver */
        .medal-3 { color: #CD7F32; } /* Bronze */
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-green-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="../index.php" class="text-2xl font-bold">🌾 AgriSense</a>
                <span class="text-green-200">Top Crops by Region</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🏆 Top Performing Crop by Region</h1>
            <p class="text-gray-600">
                Automatically shows the most profitable crop in each region based on total revenue.
            </p>
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
        $totalRevenue = array_sum(array_column($results, 'total_revenue'));
        $totalQuantity = array_sum(array_column($results, 'total_quantity'));
        $regionCount = count($results);
        $topRegion = $results[0] ?? null;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Total Regions</p>
                <p class="text-3xl font-bold text-green-700"><?= $regionCount ?></p>
                <p class="text-xs text-gray-400">with crop data</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-bold text-blue-700">৳<?= number_format($totalRevenue) ?></p>
                <p class="text-xs text-gray-400">from top crops</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-yellow-500">
                <p class="text-sm text-gray-500">Total Quantity</p>
                <p class="text-2xl font-bold text-yellow-700"><?= number_format($totalQuantity) ?></p>
                <p class="text-xs text-gray-400">units traded</p>
            </div>
            <?php if ($topRegion): ?>
            <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Top Region</p>
                <p class="text-xl font-bold text-purple-700"><?= htmlspecialchars($topRegion['region_name']) ?></p>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($topRegion['crop_name']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Results Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-700">
                    🥇 Most Profitable Crops by Region
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Showing the highest revenue-generating crop in each region
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Rank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Region
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                State
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Top Crop
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Total Revenue
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Avg Price
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($results as $index => $row): ?>
                        <?php
                        $rank = $index + 1;
                        $bgColor = '';
                        if ($rank == 1) $bgColor = 'bg-yellow-50';
                        elseif ($rank == 2) $bgColor = 'bg-gray-50';
                        elseif ($rank == 3) $bgColor = 'bg-orange-50';
                        ?>
                        <tr class="<?= $bgColor ?> hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if ($rank <= 3): ?>
                                    <span class="text-2xl medal-<?= $rank ?>">
                                        <?= $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : '🥉') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-medium text-gray-600"><?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($row['region_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($row['state']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    🌾 <?= htmlspecialchars($row['crop_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-green-700">
                                ৳<?= number_format($row['total_revenue']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-700">
                                <?= number_format($row['total_quantity']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-700">
                                ৳<?= number_format($row['avg_price'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                   
                </table>
            </div>
        </div>

        
        
        <?php else: ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
            <p class="font-bold">⚠️ No Data Found</p>
            <p>No market supply records found for revenue analysis. Please ensure:</p>
            <ul class="list-disc list-inside mt-2 ml-4">
                <li>Market supply data exists in the database</li>
                <li>Regions and crops are properly linked</li>
                <li>Price and quantity data is available</li>
            </ul>
        </div>
        <?php endif; ?>

   
</body>
</html>