<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;

$pdo = getConnection();
if ($pdo) {
    try {
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
    
    .gold-bg {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
    }
    
    .silver-bg {
        background: linear-gradient(135deg, rgba(156, 163, 175, 0.1) 0%, rgba(107, 114, 128, 0.1) 100%);
    }
    
    .bronze-bg {
        background: linear-gradient(135deg, rgba(180, 83, 9, 0.1) 0%, rgba(146, 64, 14, 0.1) 100%);
    }

    /* Text Colors */
    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">🏆 Top Performing Crop by Region</h1>
            <p class="text-body">Highest revenue generating crops in each region</p>
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
            $totalRevenue = array_sum(array_column($results, 'total_revenue'));
            $totalQuantity = array_sum(array_column($results, 'total_quantity'));
            $regionCount = count($results);
            $topRegion = $results[0] ?? null;
            ?>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-muted">Total Regions</p>
                    <p class="text-2xl font-bold text-primary"><?= $regionCount ?></p>
                    <p class="text-xs text-muted">with crop data</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-bold text-emerald-600">৳<?= number_format($totalRevenue) ?></p>
                    <p class="text-xs text-gray-500">from top crops</p>
                </div>
                <div class="glass-card rounded-xl p-4">
                    <p class="text-sm text-gray-500">Total Quantity</p>
                    <p class="text-2xl font-bold text-amber-600"><?= number_format($totalQuantity) ?></p>
                    <p class="text-xs text-gray-500">kg traded</p>
                </div>
                <?php if ($topRegion): ?>
                    <div class="glass-card rounded-xl p-4">
                        <p class="text-sm text-gray-500">Top Region</p>
                        <p class="text-xl font-bold text-emerald-600"><?= htmlspecialchars($topRegion['region_name']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($topRegion['crop_name']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Results Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Most Profitable Crops by Region
                        <span class="text-sm font-normal text-gray-500">
                            (Highest revenue generating crop in each region)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Top Crop</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $index => $row): ?>
                                <?php
                                $rank = $index + 1;
                                $bgClass = '';
                                if ($rank == 1) $bgClass = 'gold-bg';
                                elseif ($rank == 2) $bgClass = 'silver-bg';
                                elseif ($rank == 3) $bgClass = 'bronze-bg';
                                ?>
                                <tr class="hover:bg-gray-50 <?= $bgClass ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if ($rank <= 3): ?>
                                            <span class="text-2xl">
                                                <?= $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : '🥉') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-lg font-medium text-gray-500"><?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['region_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($row['state']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-50 text-emerald-700">
                                            🌾 <?= htmlspecialchars($row['crop_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-emerald-600">
                                        ৳<?= number_format($row['total_revenue']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                        <?= number_format($row['total_quantity']) ?> kg
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                        ৳<?= number_format($row['avg_price'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="glass-card rounded-xl p-6">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No data found</h3>
                    <p class="text-gray-600">No market supply records found for revenue analysis.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>