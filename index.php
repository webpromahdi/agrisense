<?php
/**
 * AgriSense - Agricultural Market Intelligence & Analytical Database System
 * Main Dashboard Homepage
 */

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/connection.php';

AuthController::requireAuth();
$currentUser = AuthController::getCurrentUser();

// ============================================
// FETCH DATABASE STATS
// ============================================

$stats = [
    'crops' => 0,
    'markets' => 0,
    'regions' => 0,
    'supply_records' => 0
];

$pdo = getConnection();

if ($pdo) {
    try {
        $stats['crops'] = $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn();
        $stats['markets'] = $pdo->query("SELECT COUNT(*) FROM markets")->fetchColumn();
        $stats['regions'] = $pdo->query("SELECT COUNT(*) FROM regions")->fetchColumn();
        $stats['supply_records'] = $pdo->query("SELECT COUNT(*) FROM market_supply")->fetchColumn();
    } catch (PDOException $e) {
    }
}

// ============================================
// KPI CARDS DATA
// ============================================
$kpiCards = [
    [
        'label' => 'CROPS TRACKED',
        'value' => $stats['crops'],
        'change' => 'Active',
        'changeText' => 'Crop varieties monitored',
        'icon' => '🌾',
    ],
    [
        'label' => 'ACTIVE MARKETS',
        'value' => $stats['markets'],
        'change' => 'Live',
        'changeText' => 'Market locations covered',
        'icon' => '🏪',
    ],
    [
        'label' => 'REGIONS',
        'value' => $stats['regions'],
        'change' => 'Coverage',
        'changeText' => 'Geographical regions',
        'icon' => '🗺️',
    ],
    [
        'label' => 'SUPPLY RECORDS',
        'value' => number_format($stats['supply_records']),
        'change' => 'Recent',
        'changeText' => 'Data points collected',
        'icon' => '📦',
    ],
];

// ============================================
// FETCH TOP CROPS BY REGION
// ============================================
$topCrops = [];
if ($pdo) {
    try {
        $sql = "
            SELECT 
                r.region_name,
                c.crop_name,
                SUM(ms.quantity) AS total_supply,
                ROUND(SUM(ms.quantity) * COALESCE(
                    (SELECT AVG(ph.price) FROM price_history ph WHERE ph.crop_id = c.crop_id),
                    0
                ), 0) AS revenue
            FROM market_supply ms
            JOIN crops c ON ms.crop_id = c.crop_id
            JOIN markets m ON ms.market_id = m.market_id
            JOIN regions r ON m.region_id = r.region_id
            GROUP BY r.region_id, r.region_name, c.crop_id, c.crop_name
            HAVING total_supply > 0
            ORDER BY total_supply DESC
            LIMIT 5
        ";
        $stmt = $pdo->query($sql);
        $topCrops = $stmt->fetchAll();
    } catch (PDOException $e) {
    }
}

// ============================================
// FETCH TOP FARMERS BY REGION
// ============================================
$topFarmers = [];
if ($pdo) {
    try {
        $sql = "
            SELECT 
                f.farmer_name,
                r.region_name,
                SUM(ms.quantity) AS total_supply,
                COUNT(DISTINCT ms.crop_id) AS crops_count
            FROM farmers f
            JOIN market_supply ms ON f.farmer_id = ms.farmer_id
            JOIN markets m ON ms.market_id = m.market_id
            JOIN regions r ON m.region_id = r.region_id
            GROUP BY f.farmer_id, f.farmer_name, r.region_id, r.region_name
            ORDER BY total_supply DESC
            LIMIT 5
        ";
        $stmt = $pdo->query($sql);
        $topFarmers = $stmt->fetchAll();
    } catch (PDOException $e) {
    }
}
?>

<?php include 'dashboard/partials/header.php'; ?>

<style>
    /* ========================================
       AgriSense Dashboard - Professional Agriculture Theme
       ======================================== */

    .section-card {
        background: #FFFFFF;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);
        padding: 1.75rem;
        border: 1px solid #E7E5E4;
    }

    .section-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1C1917;
        margin-bottom: 0;
        letter-spacing: -0.01em;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table thead th {
        font-size: 0.7rem;
        font-weight: 700;
        color: #44403C;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.875rem 0.75rem;
        text-align: left;
        border-bottom: 2px solid #E7E5E4;
        background-color: #FAFAF9;
    }

    .data-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .data-table tbody tr:hover {
        background-color: #FAFAF9;
    }

    .data-table tbody td {
        padding: 1rem 0.75rem;
        font-size: 0.875rem;
        color: #44403C;
        border-bottom: 1px solid #E7E5E4;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background-color: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .badge-info {
        background-color: #DBEAFE;
        color: #1E40AF;
        border: 1px solid #BFDBFE;
    }

    .badge-warning {
        background-color: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    .avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .avatar-primary {
        background-color: #DCFCE7;
        color: #166534;
        border: 2px solid #BBF7D0;
    }

    .avatar-secondary {
        background-color: #FEF3C7;
        color: #92400E;
        border: 2px solid #FDE68A;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #78716C;
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.7;
    }

    .empty-state p {
        color: #78716C;
    }

    .empty-state p.font-medium {
        color: #44403C;
    }

    .text-heading { color: #1C1917; }
    .text-subheading { color: #166534; }
    .text-body { color: #44403C; }
    .text-muted { color: #78716C; }

    .link-primary {
        color: #166534;
        font-weight: 600;
        transition: color 0.15s ease;
    }

    .link-primary:hover {
        color: #14532d;
        text-decoration: underline;
    }
</style>

<!-- Header Banner -->
<div class="relative h-[110px] overflow-hidden" style="background: linear-gradient(135deg, #166534 0%, #14532d 50%, #052e16 100%);">
    <div class="absolute right-0 top-0 w-1/3 h-full opacity-10"
        style="background: #ffffff; clip-path: polygon(30% 0, 100% 0, 100% 100%, 0 100%);"></div>
    <div class="absolute left-0 bottom-0 w-1/4 h-full opacity-10"
        style="background: #ffffff; clip-path: polygon(0 100%, 70% 100%, 100% 0, 0 0);"></div>

    <div class="relative z-10 h-full flex items-center justify-between px-6">
        <div>
            <h1 class="text-white text-xl font-bold tracking-tight">Market Intelligence Dashboard</h1>
            <div class="flex items-center gap-2 text-white/90 text-sm mt-1 font-medium">
                <span>AgriSense</span>
                <span class="opacity-50">›</span>
                <span class="opacity-80">Analytics Overview</span>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 text-white">
                <span class="text-xl">👋</span>
                <div class="text-right">
                    <div class="text-sm font-semibold">Welcome,
                        <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-white/90">
                <span class="text-lg">📅</span>
                <div class="text-right">
                    <div class="text-sm font-medium"><?= date('F j, Y') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-4 gap-6 -mt-10 relative z-10 px-6">
    <?php foreach ($kpiCards as $kpi): ?>
        <div class="bg-white rounded-2xl p-6 shadow-card-strong border border-border hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-text-muted">
                        <?= htmlspecialchars($kpi['label']) ?>
                    </p>
                    <p class="text-3xl font-bold text-text-heading mt-2">
                        <?= htmlspecialchars($kpi['value']) ?>
                    </p>
                    <div class="flex items-center gap-2 mt-3">
                        <span class="bg-primary text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                            <?= htmlspecialchars($kpi['change']) ?>
                        </span>
                        <span class="text-text-muted text-xs font-medium">
                            <?= htmlspecialchars($kpi['changeText']) ?>
                        </span>
                    </div>
                </div>
                <div class="text-3xl">
                    <?= $kpi['icon'] ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Main Content: Data Tables -->
<div class="px-6 py-8 bg-background-alt">
    <div class="grid grid-cols-2 gap-6">

        <!-- Top Crop by Region -->
        <div class="section-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="section-title">🏆 Top Crop by Region</h3>
                <a href="/agrisense/pages/top_crop_region.php" class="link-primary text-sm flex items-center gap-1">
                    View All 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Region</th>
                        <th>Top Crop</th>
                        <th>Supply (kg)</th>
                        <th>Est. Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topCrops)): ?>
                        <?php foreach ($topCrops as $crop): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-primary">
                                            <?= strtoupper(substr($crop['region_name'] ?? 'N', 0, 1)) ?>
                                        </div>
                                        <span class="font-medium text-text-heading"><?= htmlspecialchars($crop['region_name'] ?? 'Unknown') ?></span>
                                    </div>
                                </td>
                                <td class="font-semibold text-text-subheading">
                                    <?= htmlspecialchars($crop['crop_name'] ?? 'Unknown') ?>
                                </td>
                                <td class="text-text-body"><?= number_format($crop['total_supply'] ?? 0) ?></td>
                                <td><span class="badge badge-success">৳<?= number_format($crop['revenue'] ?? 0) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <div class="empty-state-icon">🏆</div>
                                <p class="font-medium">No crop data available</p>
                                <p class="text-sm mt-1">Add supply records to see rankings</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Farmer by Region -->
        <div class="section-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="section-title">👨‍🌾 Top Farmer by Region</h3>
                <a href="/agrisense/pages/top_farmer_region.php" class="link-primary text-sm flex items-center gap-1">
                    View All
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Farmer</th>
                        <th>Region</th>
                        <th>Total Supply</th>
                        <th>Crops</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topFarmers)): ?>
                        <?php foreach ($topFarmers as $farmer): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-secondary">
                                            <?= strtoupper(substr($farmer['farmer_name'] ?? 'N', 0, 1)) ?>
                                        </div>
                                        <span class="font-medium text-text-heading"><?= htmlspecialchars($farmer['farmer_name'] ?? 'Unknown') ?></span>
                                    </div>
                                </td>
                                <td class="text-text-body"><?= htmlspecialchars($farmer['region_name'] ?? 'Unknown') ?></td>
                                <td class="font-semibold text-text-subheading">
                                    <?= number_format($farmer['total_supply'] ?? 0) ?> kg
                                </td>
                                <td><span class="badge badge-info"><?= $farmer['crops_count'] ?? 0 ?> crops</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <div class="empty-state-icon">👨‍🌾</div>
                                <p class="font-medium">No farmer data available</p>
                                <p class="text-sm mt-1">Add supply records to see rankings</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include 'dashboard/partials/footer.php'; ?>
