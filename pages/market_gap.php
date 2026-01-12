<?php
/**
 * AgriSense - Feature A2: Inter-Market Price Gap Analysis
 * 
 * Identifies crops where price difference between two markets is significant.
 * Uses self-JOIN to compare prices across markets.
 */

require_once __DIR__ . '/../db/connection.php';

$results = [];
$error = null;
$markets = [];
$selectedMarketA = null;
$selectedMarketB = null;

// Fetch all markets for dropdown
$markets = getAllMarkets();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedMarketA = isset($_POST['market_a']) ? (int)$_POST['market_a'] : null;
    $selectedMarketB = isset($_POST['market_b']) ? (int)$_POST['market_b'] : null;
    
    if ($selectedMarketA && $selectedMarketB && $selectedMarketA !== $selectedMarketB) {
        // SQL Query: Inter-Market Price Gap Analysis
        // Uses Self-JOIN to compare same crop prices across two markets
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
                    AND mp_a.market_id != mp_b.market_id
                JOIN crops c ON mp_a.crop_id = c.crop_id
                JOIN markets ma ON mp_a.market_id = ma.market_id
                JOIN markets mb ON mp_b.market_id = mb.market_id
            WHERE 
                mp_a.market_id = :market_a_id
                AND mp_b.market_id = :market_b_id
            ORDER BY 
                ABS(mp_a.current_price - mp_b.current_price) DESC
        ";
        
        $pdo = getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'market_a_id' => $selectedMarketA,
                    'market_b_id' => $selectedMarketB
                ]);
                $results = $stmt->fetchAll();
            } catch (PDOException $e) {
                $error = "Query Error: " . $e->getMessage();
            }
        } else {
            $error = "Database connection failed. Please check your configuration.";
        }
    } elseif ($selectedMarketA === $selectedMarketB) {
        $error = "Please select two different markets for comparison.";
    } else {
        $error = "Please select both markets.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inter-Market Price Gap - AgriSense</title>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔄 Inter-Market Price Gap Analysis</h1>
            <p class="text-gray-600">
                Compare crop prices between two markets to identify arbitrage opportunities 
                and understand regional price variations.
            </p>
        </div>

        <!-- Market Selection Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Select Markets to Compare</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="market_a" class="block text-sm font-medium text-gray-700 mb-2">
                        Market A
                    </label>
                    <select 
                        id="market_a" 
                        name="market_a" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">-- Select Market A --</option>
                        <?php foreach ($markets as $market): ?>
                        <option 
                            value="<?= $market['market_id'] ?>"
                            <?= $selectedMarketA == $market['market_id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($market['market_name']) ?> (<?= htmlspecialchars($market['region_name']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="market_b" class="block text-sm font-medium text-gray-700 mb-2">
                        Market B
                    </label>
                    <select 
                        id="market_b" 
                        name="market_b" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">-- Select Market B --</option>
                        <?php foreach ($markets as $market): ?>
                        <option 
                            value="<?= $market['market_id'] ?>"
                            <?= $selectedMarketB == $market['market_id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($market['market_name']) ?> (<?= htmlspecialchars($market['region_name']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors h-[42px]"
                >
                    📊 Compare Prices
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
                    📈 Price Comparison Results
                    <span class="text-sm font-normal text-gray-500">
                        (<?= count($results) ?> crops compared)
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
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <?= htmlspecialchars($results[0]['market_a_name'] ?? 'Market A') ?> (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <?= htmlspecialchars($results[0]['market_b_name'] ?? 'Market B') ?> (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Price Gap (৳)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Gap %
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Higher Price
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
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono <?= $row['price_comparison'] === 'Market A Higher' ? 'text-red-600 font-semibold' : 'text-gray-700' ?>">
                                ৳<?= number_format($row['market_a_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono <?= $row['price_comparison'] === 'Market B Higher' ? 'text-red-600 font-semibold' : 'text-gray-700' ?>">
                                ৳<?= number_format($row['market_b_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900">
                                ৳<?= number_format($row['price_gap'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <?php 
                                $gap = $row['gap_percentage'];
                                $badgeClass = $gap > 20 ? 'bg-red-100 text-red-800' : 
                                             ($gap > 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium <?= $badgeClass ?>">
                                    <?= $gap ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if ($row['price_comparison'] === 'Market A Higher'): ?>
                                    <span class="text-blue-600">← Market A</span>
                                <?php elseif ($row['price_comparison'] === 'Market B Higher'): ?>
                                    <span class="text-purple-600">Market B →</span>
                                <?php else: ?>
                                    <span class="text-gray-500">Equal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Statistics -->
            <?php
            $totalGap = array_sum(array_column($results, 'price_gap'));
            $avgGap = count($results) > 0 ? $totalGap / count($results) : 0;
            $maxGap = count($results) > 0 ? max(array_column($results, 'price_gap')) : 0;
            ?>
            <div class="px-6 py-4 bg-gray-50 border-t">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-sm text-gray-500">Average Price Gap</p>
                        <p class="text-xl font-bold text-gray-800">৳<?= number_format($avgGap, 2) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Maximum Gap</p>
                        <p class="text-xl font-bold text-red-600">৳<?= number_format($maxGap, 2) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Crops Compared</p>
                        <p class="text-xl font-bold text-gray-800"><?= count($results) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
            <p class="font-bold">⚠️ No Common Crops Found</p>
            <p>The selected markets don't have any common crops for comparison.</p>
        </div>
        <?php else: ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
            <p class="font-bold">ℹ️ Getting Started</p>
            <p>Select two different markets to compare crop prices and identify price gaps.</p>
        </div>
        <?php endif; ?>

        <!-- SQL Query Reference -->
        <div class="mt-8 bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-900 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-gray-200">📝 SQL Query Used (Self-JOIN)</h3>
            </div>
            <pre class="p-6 text-sm text-green-400 overflow-x-auto"><code>SELECT 
    c.crop_name,
    ma.market_name AS market_a_name,
    mp_a.current_price AS market_a_price,
    mb.market_name AS market_b_name,
    mp_b.current_price AS market_b_price,
    ABS(mp_a.current_price - mp_b.current_price) AS price_gap,
    ROUND(
        (ABS(mp_a.current_price - mp_b.current_price) / 
         LEAST(mp_a.current_price, mp_b.current_price)) * 100, 2
    ) AS gap_percentage
FROM 
    market_prices mp_a
    -- Self JOIN: Compare same crop across different markets
    JOIN market_prices mp_b 
        ON mp_a.crop_id = mp_b.crop_id 
        AND mp_a.market_id != mp_b.market_id
    JOIN crops c ON mp_a.crop_id = c.crop_id
    JOIN markets ma ON mp_a.market_id = ma.market_id
    JOIN markets mb ON mp_b.market_id = mb.market_id
WHERE 
    mp_a.market_id = :market_a_id
    AND mp_b.market_id = :market_b_id
ORDER BY price_gap DESC;</code></pre>
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
