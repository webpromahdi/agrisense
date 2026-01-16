<?php
/**
 * AgriSense - Top Performing Farmer by Region
 * 
 * Analytical feature showing highest revenue farmer per region
 * All calculations done in SQL (no PHP calculations)
 */

require_once __DIR__ . '/../db/connection.php';

$results = [];
$error = null;

// Execute the analysis query
$pdo = getConnection();
if ($pdo) {
    try {
        // SQL Query: Top Performing Farmer by Region
        // Revenue = SUM(quantity × price_per_unit)
        $sql = "
            SELECT 
                farmer_revenue.region_name,
                farmer_revenue.state,
                farmer_revenue.farmer_name,
                farmer_revenue.total_revenue,
                farmer_revenue.total_quantity,
                farmer_revenue.supply_count
            FROM (
                SELECT 
                    r.region_id,
                    r.region_name,
                    r.state,
                    f.farmer_id,
                    f.farmer_name,
                    SUM(ms.quantity * ms.price_per_unit) AS total_revenue,
                    SUM(ms.quantity) AS total_quantity,
                    COUNT(ms.supply_id) AS supply_count
                FROM 
                    market_supply ms
                    JOIN farmers f ON ms.farmer_id = f.farmer_id
                    JOIN regions r ON f.region_id = r.region_id
                GROUP BY 
                    r.region_id,
                    r.region_name,
                    r.state,
                    f.farmer_id,
                    f.farmer_name
            ) farmer_revenue
            WHERE 
                farmer_revenue.total_revenue = (
                    SELECT MAX(inner_rev.total_revenue)
                    FROM (
                        SELECT 
                            f2.region_id,
                            f2.farmer_id,
                            SUM(ms2.quantity * ms2.price_per_unit) AS total_revenue
                        FROM 
                            market_supply ms2
                            JOIN farmers f2 ON ms2.farmer_id = f2.farmer_id
                        GROUP BY 
                            f2.region_id,
                            f2.farmer_id
                    ) inner_rev
                    WHERE inner_rev.region_id = farmer_revenue.region_id
                )
            ORDER BY 
                farmer_revenue.total_revenue DESC
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
    <title>Top Performing Farmer by Region - AgriSense</title>
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
                <span class="text-green-100 text-sm font-medium">Top Farmers by Region</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-slate-100">
            <div class="flex items-center space-x-3 mb-3">
                <span class="text-3xl">👨‍🌾</span>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Top Performing Farmer by Region</h1>
            </div>
            <p class="text-slate-600 leading-relaxed">
                Identifies the farmer who has generated the highest total revenue in each region based on market supply
                transactions.
            </p>
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
            $totalRevenue = array_sum(array_column($results, 'total_revenue'));
            $totalQuantity = array_sum(array_column($results, 'total_quantity'));
            $regionCount = count($results);
            $topFarmer = $results[0] ?? null;
            ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-emerald-500">
                    <p class="text-sm text-slate-500 font-medium">Regions Covered</p>
                    <p class="text-3xl font-bold text-emerald-600"><?= $regionCount ?></p>
                    <p class="text-xs text-slate-400">with farmer data</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-sky-500">
                    <p class="text-sm text-slate-500 font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold text-sky-600">৳<?= number_format($totalRevenue) ?></p>
                    <p class="text-xs text-slate-400">by top farmers</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-amber-500">
                    <p class="text-sm text-slate-500 font-medium">Total Quantity</p>
                    <p class="text-2xl font-bold text-amber-600"><?= number_format($totalQuantity) ?></p>
                    <p class="text-xs text-slate-400">kg supplied</p>
                </div>
                <?php if ($topFarmer): ?>
                    <div
                        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-violet-500">
                        <p class="text-sm text-slate-500 font-medium">Highest Earner</p>
                        <p class="text-xl font-bold text-violet-600"><?= htmlspecialchars($topFarmer['farmer_name']) ?></p>
                        <p class="text-sm text-slate-500"><?= htmlspecialchars($topFarmer['region_name']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Results Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-100">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-700">
                        🏆 Top Farmers by Region
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Each region shows the farmer with the highest total revenue from market supplies
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Region
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    State
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Top Farmer
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Total Revenue
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Supplies
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($results as $index => $row): ?>
                                <?php
                                $rank = $index + 1;
                                $bgColor = '';
                                if ($rank == 1)
                                    $bgColor = 'bg-amber-50';
                                elseif ($rank == 2)
                                    $bgColor = 'bg-slate-50';
                                elseif ($rank == 3)
                                    $bgColor = 'bg-orange-50';
                                ?>
                                <tr class="<?= $bgColor ?> hover:bg-slate-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if ($rank <= 3): ?>
                                            <span class="text-2xl">
                                                <?= $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : '🥉') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-lg font-medium text-slate-500"><?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="font-medium text-slate-900"><?= htmlspecialchars($row['region_name']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                        <?= htmlspecialchars($row['state']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-sky-50 text-sky-700">
                                            👨‍🌾 <?= htmlspecialchars($row['farmer_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-emerald-600">
                                        ৳<?= number_format($row['total_revenue']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-slate-600">
                                        <?= number_format($row['total_quantity']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-slate-600">
                                        <?= number_format($row['supply_count']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-8">
                <a href="../index.php"
                    class="inline-flex items-center text-slate-600 hover:text-green-600 transition-colors duration-200">
                    <span class="mr-2">←</span> Back to Dashboard
                </a>
            </div>

        <?php else: ?>
            <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-700 p-4 rounded-lg">
                <p class="font-bold">⚠️ No Data Found</p>
                <p>No market supply records found for farmer revenue analysis. Please ensure:</p>
                <ul class="list-disc list-inside mt-2 ml-4">
                    <li>Market supply data exists in the database</li>
                    <li>Farmers are properly linked to regions</li>
                    <li>Quantity and price data is available</li>
                </ul>
            </div>
        <?php endif; ?>

    </main>
</body>

</html>