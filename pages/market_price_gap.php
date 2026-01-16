<?php
/**
 * AgriSense - Feature: Inter-Market Price Gap Analysis
 * 
 * Compare prices of the same crop across different markets
 * to identify significant price differences (arbitrage opportunities).
 * 
 * Uses Self-JOIN on market_prices table.
 */

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

// Require authentication
AuthController::requireAuth();

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
        // SQL Query: Inter-Market Price Gap Analysis
        // Self-JOIN to compare same crop across all market pairs
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
    <title>Inter-Market Price Gap Analysis - AgriSense</title>
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
                <span class="text-3xl">🔄</span>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Inter-Market Price Gap Analysis</h1>
            </div>
            <p class="text-slate-600 leading-relaxed">
                Select a crop to compare its prices across all markets and identify
                significant price gaps for arbitrage opportunities.
            </p>
        </div>

        <!-- Crop Selection Form -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-slate-100">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">Select Crop to Analyze</h2>
            <form method="POST" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[250px]">
                    <label for="crop_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Crop
                    </label>
                    <select id="crop_id" name="crop_id" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white">
                        <option value="">-- Select a Crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $selectedCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?> (<?= ucfirst($crop['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 bg-violet-500 hover:bg-violet-600 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
                    📊 Analyze Price Gaps
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
            $gaps = array_column($results, 'price_gap');
            $percentages = array_column($results, 'gap_percentage');
            $avgGap = count($gaps) > 0 ? array_sum($gaps) / count($gaps) : 0;
            $maxGap = count($gaps) > 0 ? max($gaps) : 0;
            $avgPercentage = count($percentages) > 0 ? array_sum($percentages) / count($percentages) : 0;
            $significantGaps = count(array_filter($percentages, fn($p) => $p > 10));
            ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-emerald-500">
                    <p class="text-sm text-slate-500 font-medium">Crop Analyzed</p>
                    <p class="text-xl font-bold text-emerald-600"><?= htmlspecialchars($cropName) ?></p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-slate-400">
                    <p class="text-sm text-slate-500 font-medium">Average Price Gap</p>
                    <p class="text-xl font-bold text-slate-700">৳<?= number_format($avgGap, 2) ?></p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-rose-400">
                    <p class="text-sm text-slate-500 font-medium">Maximum Gap</p>
                    <p class="text-xl font-bold text-rose-500">৳<?= number_format($maxGap, 2) ?></p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-5 text-center border-l-4 border-amber-500">
                    <p class="text-sm text-slate-500 font-medium">Significant Gaps (&gt;10%)</p>
                    <p class="text-xl font-bold text-amber-600"><?= $significantGaps ?></p>
                </div>
            </div>

            <!-- Price Gap Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-100">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-700">
                        📈 Price Gaps for <?= htmlspecialchars($cropName) ?>
                        <span class="text-sm font-normal text-slate-500">(<?= count($results) ?> market pairs)</span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Crop Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Market A</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Market A Price (৳)</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Market B</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Market B Price (৳)</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Price Gap (৳)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Gap %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($results as $row): ?>
                                <tr class="hover:bg-slate-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="font-medium text-slate-900"><?= htmlspecialchars($row['crop_name']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                        <?= htmlspecialchars($row['market_a_name']) ?>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right font-mono <?= $row['price_comparison'] === 'Market A Higher' ? 'text-rose-500 font-semibold' : 'text-slate-600' ?>">
                                        ৳<?= number_format($row['market_a_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                        <?= htmlspecialchars($row['market_b_name']) ?>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right font-mono <?= $row['price_comparison'] === 'Market B Higher' ? 'text-rose-500 font-semibold' : 'text-slate-600' ?>">
                                        ৳<?= number_format($row['market_b_price'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-slate-900 font-semibold">
                                        ৳<?= number_format($row['price_gap'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php
                                        $gap = $row['gap_percentage'];
                                        $badgeClass = $gap > 20 ? 'bg-rose-50 text-rose-700' :
                                            ($gap > 10 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700');
                                        ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium <?= $badgeClass ?>">
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
            <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-700 p-4 rounded-lg">
                <p class="font-bold">⚠️ No Price Data Found</p>
                <p>No market price records found for the selected crop across multiple markets.</p>
            </div>
        <?php else: ?>
            <div class="bg-sky-50 border-l-4 border-sky-400 text-sky-700 p-4 rounded-lg">
                <p class="font-bold">ℹ️ Getting Started</p>
                <p>Select a crop from the dropdown to compare its prices across all markets.</p>
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