<?php
/**
 * AgriSense - Feature A4: Market Saturation Index
 * 
 * Identifies markets where supply is too high, indicating possible price drops.
 * Uses SUM(), COUNT(DISTINCT), and calculated saturation ratios.
 */

require_once __DIR__ . '/../db/connection.php';

$results = [];
$error = null;
$filterCrop = null;
$crops = [];

// Fetch all crops for filter dropdown
$crops = getAllCrops();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['analyze'])) {
    $filterCrop = isset($_POST['crop_id']) ? (int) $_POST['crop_id'] : null;

    // SQL Query: Market Saturation Index
    // Uses SUM for total supply, COUNT(DISTINCT) for unique farmers
    // Calculates saturation index as supply per farmer ratio

    $sql = "
        SELECT 
            m.market_name,
            r.region_name,
            c.crop_name,
            SUM(ms.quantity) AS total_supply,
            COUNT(DISTINCT ms.farmer_id) AS farmer_count,
            ROUND(
                SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id), 
                2
            ) AS saturation_index,
            ROUND(AVG(ms.price_per_unit), 2) AS avg_price,
            CASE 
                WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 150 THEN 'HIGH'
                WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 100 THEN 'MEDIUM'
                ELSE 'LOW'
            END AS saturation_level
        FROM 
            market_supply ms
            JOIN markets m ON ms.market_id = m.market_id
            JOIN regions r ON m.region_id = r.region_id
            JOIN crops c ON ms.crop_id = c.crop_id
    ";

    // Add crop filter if selected
    $params = [];
    if ($filterCrop) {
        $sql .= " WHERE ms.crop_id = :crop_id ";
        $params['crop_id'] = $filterCrop;
    }

    $sql .= "
        GROUP BY 
            m.market_id, 
            m.market_name, 
            r.region_name,
            c.crop_id,
            c.crop_name
        ORDER BY 
            SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) DESC
    ";

    $pdo = getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
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
    <title>Market Saturation Index - AgriSense</title>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">📦 Market Saturation Index</h1>
            <p class="text-gray-600">
                Identify markets with high supply concentration. High saturation indicates
                potential price drops due to oversupply from fewer farmers.
            </p>

            <!-- Saturation Index Explanation -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-2">Understanding Saturation Index</h3>
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Formula:</strong> Saturation Index = Total Supply ÷ Number of Unique Farmers
                </p>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full">HIGH: &gt;150 (Risk of price
                        crash)</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full">MEDIUM: 100-150 (Monitor
                        closely)</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full">LOW: &lt;100 (Healthy
                        market)</span>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Filter Options</h2>
            <form method="POST" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[250px]">
                    <label for="crop_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Filter by Crop (Optional)
                    </label>
                    <select id="crop_id" name="crop_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- All Crops --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= $crop['crop_id'] ?>" <?= $filterCrop == $crop['crop_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop['crop_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    📊 Calculate Saturation
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
            $highCount = count(array_filter($results, fn($r) => $r['saturation_level'] === 'HIGH'));
            $mediumCount = count(array_filter($results, fn($r) => $r['saturation_level'] === 'MEDIUM'));
            $lowCount = count(array_filter($results, fn($r) => $r['saturation_level'] === 'LOW'));
            $avgSaturation = count($results) > 0 ? array_sum(array_column($results, 'saturation_index')) / count($results) : 0;
            ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-red-500">
                    <p class="text-sm text-gray-500">High Saturation</p>
                    <p class="text-2xl font-bold text-red-600"><?= $highCount ?></p>
                    <p class="text-xs text-gray-400">markets at risk</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500">Medium Saturation</p>
                    <p class="text-2xl font-bold text-yellow-600"><?= $mediumCount ?></p>
                    <p class="text-xs text-gray-400">need monitoring</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Low Saturation</p>
                    <p class="text-2xl font-bold text-green-600"><?= $lowCount ?></p>
                    <p class="text-xs text-gray-400">healthy markets</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500">Avg Index</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($avgSaturation, 1) ?></p>
                    <p class="text-xs text-gray-400">kg/farmer</p>
                </div>
            </div>

            <!-- Saturation Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-700">
                        🏪 Market Saturation Analysis
                        <span class="text-sm font-normal text-gray-500">
                            (<?= count($results) ?> market-crop combinations)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Market Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Region
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Crop
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Total Supply (Q)
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Farmers
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Saturation Index
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Level
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <?php
                                $levelClass = match ($row['saturation_level']) {
                                    'HIGH' => 'bg-red-100 text-red-800',
                                    'MEDIUM' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-green-100 text-green-800'
                                };
                                $levelIcon = match ($row['saturation_level']) {
                                    'HIGH' => '🔴',
                                    'MEDIUM' => '🟡',
                                    default => '🟢'
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-900">
                                            <?= htmlspecialchars($row['market_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <?= htmlspecialchars($row['region_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                        <?= htmlspecialchars($row['crop_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900">
                                        <?= number_format($row['total_supply']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-700">
                                        <?= $row['farmer_count'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="font-mono font-bold text-gray-900">
                                            <?= number_format($row['saturation_index'], 2) ?>
                                        </span>
                                        <span class="text-xs text-gray-500">Q/farmer</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium <?= $levelClass ?>">
                                            <?= $levelIcon ?>         <?= $row['saturation_level'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                <p class="font-bold">⚠️ No Supply Data Found</p>
                <p>No market supply records found for the selected criteria.</p>
            </div>
        <?php else: ?>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
                <p class="font-bold">ℹ️ Getting Started</p>
                <p>Click "Calculate Saturation" to analyze market saturation levels. Optionally filter by crop.</p>
            </div>
        <?php endif; ?>

        <!-- SQL Query Reference -->
        <div class="mt-8 bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-900 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-gray-200">📝 SQL Query Used</h3>
            </div>
            <pre class="p-6 text-sm text-green-400 overflow-x-auto"><code>SELECT 
    m.market_name,
    r.region_name,
    c.crop_name,
    SUM(ms.quantity) AS total_supply,
    COUNT(DISTINCT ms.farmer_id) AS farmer_count,
    ROUND(
        SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id), 
        2
    ) AS saturation_index,
    CASE 
        WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 150 THEN 'HIGH'
        WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 100 THEN 'MEDIUM'
        ELSE 'LOW'
    END AS saturation_level
FROM 
    market_supply ms
    JOIN markets m ON ms.market_id = m.market_id
    JOIN regions r ON m.region_id = r.region_id
    JOIN crops c ON ms.crop_id = c.crop_id
GROUP BY 
    m.market_id, m.market_name, r.region_name, c.crop_id, c.crop_name
ORDER BY 
    saturation_index DESC;</code></pre>
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