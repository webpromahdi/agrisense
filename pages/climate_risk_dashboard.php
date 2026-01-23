<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

$results = [];
$error = null;
$regions = [];
$selectedRegion = null;
$tableExists = false;

$regions = getAllRegions();

// Check if climate_risk table exists and has data
$pdo = getConnection();
if ($pdo) {
    try {
        // Check if table exists
        $checkTable = $pdo->query("SHOW TABLES LIKE 'climate_risk'");
        $tableExists = $checkTable->rowCount() > 0;

        if (!$tableExists) {
            $error = "Climate risk table not found. Please run the sql/climate_risk_advisory.sql script first.";
        }
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

if ($tableExists && ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['view']))) {
    $selectedRegion = isset($_POST['region_id']) ? (int) $_POST['region_id'] : null;

    $sql = "
        SELECT 
            r.region_id,
            r.region_name,
            r.state,
            cr.risk_type,
            cr.severity,
            cr.advisory_text,
            cr.season
        FROM 
            climate_risk cr
            JOIN regions r ON cr.region_id = r.region_id
    ";

    $params = [];
    if ($selectedRegion) {
        $sql .= " WHERE r.region_id = :region_id ";
        $params['region_id'] = $selectedRegion;
    }

    $sql .= "
        ORDER BY 
            FIELD(cr.severity, 'Critical', 'High', 'Moderate', 'Low'),
            r.region_name,
            cr.risk_type
    ";

    if ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = "Query Error: " . $e->getMessage();
        }
    }
} elseif ($tableExists) {
    // Load all by default
    $sql = "
        SELECT 
            r.region_id,
            r.region_name,
            r.state,
            cr.risk_type,
            cr.severity,
            cr.advisory_text,
            cr.season
        FROM 
            climate_risk cr
            JOIN regions r ON cr.region_id = r.region_id
        ORDER BY 
            FIELD(cr.severity, 'Critical', 'High', 'Moderate', 'Low'),
            r.region_name,
            cr.risk_type
    ";

    if ($pdo) {
        try {
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = "Query Error: " . $e->getMessage();
        }
    }
}

// Group results by region for display
$groupedResults = [];
foreach ($results as $row) {
    $regionId = $row['region_id'];
    if (!isset($groupedResults[$regionId])) {
        $groupedResults[$regionId] = [
            'region_name' => $row['region_name'],
            'state' => $row['state'],
            'risks' => []
        ];
    }
    $groupedResults[$regionId]['risks'][] = $row;
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

    /* Text Colors */
    .text-heading { color: #1C1917; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }

    .severity-critical {
        background: #FEE2E2;
        color: #991b1b;
        border-left: 4px solid #991b1b;
    }

    .severity-high {
        background: #FEE2E2;
        color: #dc2626;
        border-left: 4px solid #dc2626;
    }

    .severity-moderate {
        background: #FEF3C7;
        color: #92400E;
        border-left: 4px solid #D97706;
    }

    .severity-low {
        background: #DCFCE7;
        color: #166534;
        border-left: 4px solid #16A34A;
    }

    .badge-critical {
        background: #991b1b;
        color: white;
        font-weight: 600;
    }

    .badge-high {
        background: #dc2626;
        color: white;
        font-weight: 600;
    }

    .badge-moderate {
        background: #D97706;
        color: white;
        font-weight: 600;
    }

    .badge-low {
        background: #16A34A;
        color: white;
        font-weight: 600;
    }

    .risk-flood {
        background: rgba(59, 130, 246, 0.1);
    }

    .risk-cyclone {
        background: rgba(139, 92, 246, 0.1);
    }

    .risk-drought {
        background: rgba(245, 158, 11, 0.1);
    }

    .risk-salinity {
        background: rgba(236, 72, 153, 0.1);
    }

    .risk-waterlogging {
        background: rgba(20, 184, 166, 0.1);
    }
</style>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-heading mb-2">🌦️ Climate Risk Advisory Dashboard</h1>
            <p class="text-body">Region-wise climate risk information for agricultural planning</p>

            <!-- Disclaimer -->
            <div class="mt-4 p-4 bg-white rounded-xl border border-border shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="text-xl">ℹ️</span>
                    <div>
                        <h3 class="font-bold text-heading mb-1">Informational Data Only</h3>
                        <p class="text-sm text-body">
                            This dashboard displays <strong>static climate risk information</strong> based on historical
                            patterns
                            and known geographical factors. This is NOT real-time weather data and does NOT include
                            predictions.
                            Always consult local weather services for current conditions.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Risk Type Legend -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="font-semibold text-gray-700 mb-3">Risk Types</h3>
                <div class="flex flex-wrap gap-3">
                    <span class="px-3 py-1 rounded-full text-sm risk-flood border border-blue-200">🌊 Flood</span>
                    <span class="px-3 py-1 rounded-full text-sm risk-cyclone border border-purple-200">🌀 Cyclone</span>
                    <span class="px-3 py-1 rounded-full text-sm risk-drought border border-amber-200">☀️ Drought</span>
                    <span class="px-3 py-1 rounded-full text-sm risk-salinity border border-pink-200">🧂 Salinity</span>
                    <span class="px-3 py-1 rounded-full text-sm risk-waterlogging border border-teal-200">💧
                        Waterlogging</span>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label for="region_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Filter by Region (Optional)
                    </label>
                    <select id="region_id" name="region_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">-- All Regions --</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region['region_id'] ?>" <?= $selectedRegion == $region['region_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($region['region_name']) ?> (
                                <?= htmlspecialchars($region['state']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 btn-primary rounded-lg font-medium">
                    🔍 View Advisories
                </button>
            </form>
        </div>

        <!-- Error Display -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                <p class="font-medium">Error</p>
                <p class="text-sm">
                    <?= htmlspecialchars($error) ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <?php if (!empty($groupedResults)): ?>
            <?php
            $criticalCount = count(array_filter($results, fn($r) => $r['severity'] === 'Critical'));
            $highCount = count(array_filter($results, fn($r) => $r['severity'] === 'High'));
            $moderateCount = count(array_filter($results, fn($r) => $r['severity'] === 'Moderate'));
            $lowCount = count(array_filter($results, fn($r) => $r['severity'] === 'Low'));
            ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="glass-card rounded-xl p-4 border-l-4 border-red-900">
                    <p class="text-sm text-gray-500">Critical</p>
                    <p class="text-2xl font-bold text-red-900">
                        <?= $criticalCount ?>
                    </p>
                    <p class="text-xs text-gray-500">advisories</p>
                </div>
                <div class="glass-card rounded-xl p-4 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500">High</p>
                    <p class="text-2xl font-bold text-red-600">
                        <?= $highCount ?>
                    </p>
                    <p class="text-xs text-gray-500">advisories</p>
                </div>
                <div class="glass-card rounded-xl p-4 border-l-4 border-amber-500">
                    <p class="text-sm text-gray-500">Moderate</p>
                    <p class="text-2xl font-bold text-amber-600">
                        <?= $moderateCount ?>
                    </p>
                    <p class="text-xs text-gray-500">advisories</p>
                </div>
                <div class="glass-card rounded-xl p-4 border-l-4 border-emerald-500">
                    <p class="text-sm text-gray-500">Low</p>
                    <p class="text-2xl font-bold text-emerald-600">
                        <?= $lowCount ?>
                    </p>
                    <p class="text-xs text-gray-500">advisories</p>
                </div>
            </div>

            <!-- Region Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($groupedResults as $regionId => $region): ?>
                    <div class="glass-card rounded-xl overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-emerald-100">
                            <h2 class="text-lg font-bold text-gray-800">
                                📍
                                <?= htmlspecialchars($region['region_name']) ?>
                            </h2>
                            <p class="text-sm text-gray-500">
                                <?= htmlspecialchars($region['state']) ?>
                            </p>
                        </div>
                        <div class="p-4 space-y-3">
                            <?php foreach ($region['risks'] as $risk): ?>
                                <?php
                                $riskIcon = match ($risk['risk_type']) {
                                    'Flood' => '🌊',
                                    'Cyclone' => '🌀',
                                    'Drought' => '☀️',
                                    'Salinity' => '🧂',
                                    'Waterlogging' => '💧',
                                    default => '⚠️'
                                };
                                $severityClass = match ($risk['severity']) {
                                    'Critical' => 'severity-critical',
                                    'High' => 'severity-high',
                                    'Moderate' => 'severity-moderate',
                                    default => 'severity-low'
                                };
                                $badgeClass = match ($risk['severity']) {
                                    'Critical' => 'badge-critical',
                                    'High' => 'badge-high',
                                    'Moderate' => 'badge-moderate',
                                    default => 'badge-low'
                                };
                                ?>
                                <div class="p-4 rounded-lg <?= $severityClass ?>">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">
                                                <?= $riskIcon ?>
                                            </span>
                                            <span class="font-semibold">
                                                <?= htmlspecialchars($risk['risk_type']) ?>
                                            </span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-xs font-bold <?= $badgeClass ?>">
                                            <?= $risk['severity'] ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 mb-2">
                                        <?= htmlspecialchars($risk['advisory_text']) ?>
                                    </p>
                                    <?php if ($risk['season']): ?>
                                        <p class="text-xs text-gray-500">
                                            📅 Season:
                                            <?= htmlspecialchars($risk['season']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Full Table View -->
            <div class="glass-card rounded-xl overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        All Climate Risk Advisories
                        <span class="text-sm font-normal text-gray-500">
                            (
                            <?= count($results) ?> total)
                        </span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Region</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk Type</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Severity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Season</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Advisory</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($results as $row): ?>
                                <?php
                                $riskIcon = match ($row['risk_type']) {
                                    'Flood' => '🌊',
                                    'Cyclone' => '🌀',
                                    'Drought' => '☀️',
                                    'Salinity' => '🧂',
                                    'Waterlogging' => '💧',
                                    default => '⚠️'
                                };
                                $badgeClass = match ($row['severity']) {
                                    'Critical' => 'badge-critical',
                                    'High' => 'badge-high',
                                    'Moderate' => 'badge-moderate',
                                    default => 'badge-low'
                                };
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($row['region_name']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= htmlspecialchars($row['state']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?= $riskIcon ?>
                                        <?= htmlspecialchars($row['risk_type']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 rounded text-xs font-bold <?= $badgeClass ?>">
                                            <?= $row['severity'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($row['season'] ?? 'Year-round') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-md">
                                        <?= htmlspecialchars($row['advisory_text']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (!$error): ?>
            <div class="glass-card rounded-xl p-6">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">🌦️</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No climate risk data available</h3>
                    <p class="text-gray-600">Please run the <code
                            class="bg-gray-100 px-2 py-1 rounded">sql/climate_risk_advisory.sql</code> script to populate
                        the climate risk data.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include __DIR__ . '/../dashboard/partials/footer.php'; ?>