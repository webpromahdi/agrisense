<?php
/**
 * AgriSense - Deal Detail View
 * 
 * Displays detailed information about a specific aggregation deal,
 * including participating farmers and fulfillment status.
 */

require '../dashboard/partials/header.php';
require '../db/connection.php';

$pdo = getConnection();

// 1. Validate Deal ID
$deal_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error_message = '';
$deal = null;
$participants = [];

if (!$deal_id) {
    $error_message = "Invalid Deal ID provided.";
} else {
    try {
        // 2. Fetch Deal Information
        $sqlDeal = "
            SELECT 
                v.*,
                c.crop_name,
                m.market_name
            FROM virtual_coop_deals v
            JOIN crops c ON v.crop_id = c.crop_id
            JOIN markets m ON v.market_id = m.market_id
            WHERE v.deal_id = :deal_id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sqlDeal);
        $stmt->execute([':deal_id' => $deal_id]);
        $deal = $stmt->fetch();

        if (!$deal) {
            $error_message = "Deal not found.";
        } else {
            // 3. Fetch Participants
            $sqlParticipants = "
                SELECT 
                dp.quantity_allocated,
                dp.cost_share,
                f.farmer_name
            FROM deal_participants dp
                JOIN farmers f ON dp.farmer_id = f.farmer_id
                WHERE dp.deal_id = :deal_id
                ORDER BY dp.quantity_allocated DESC
            ";

            $stmt = $pdo->prepare($sqlParticipants);
            $stmt->execute([':deal_id' => $deal_id]);
            $participants = $stmt->fetchAll();
        }

    } catch (PDOException $e) {
        error_log("Database Error (Deal Detail): " . $e->getMessage());
        $error_message = "Unable to load deal details at this time.";
    }
}

?>

<div class="container mx-auto px-6 py-8">

    <!-- Back Button -->
    <div class="mb-6">
        <a href="aggregation_history.php"
            class="inline-flex items-center text-gray-600 hover:text-primary transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Back to History
        </a>
    </div>

    <?php if ($error_message): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            <p class="font-bold">Error</p>
            <p>
                <?php echo htmlspecialchars($error_message); ?>
            </p>
        </div>
    <?php elseif ($deal): ?>

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📄</span>
                    <h1 class="text-3xl font-bold text-gray-800">Deal #
                        <?php echo htmlspecialchars($deal['deal_id']); ?>
                    </h1>
                </div>
                <p class="text-gray-500 mt-1 ml-11">
                    Created on
                    <?php echo date('F d, Y \a\t H:i', strtotime($deal['created_at'])); ?>
                </p>
            </div>

            <!-- Status Badge -->
            <?php
            $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
            $statusIcon = '⚪';

            if ($deal['status'] === 'fulfilled') {
                $statusClass = 'bg-green-100 text-green-800 border-green-200';
                $statusIcon = '✅';
            } elseif ($deal['status'] === 'partial') {
                $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                $statusIcon = '⚠️';
            } elseif ($deal['status'] === 'cancelled') {
                $statusClass = 'bg-red-100 text-red-800 border-red-200';
                $statusIcon = '❌';
            }
            ?>
            <span
                class="px-5 py-2 rounded-full text-sm font-bold border <?php echo $statusClass; ?> flex items-center gap-2 shadow-sm">
                <span>
                    <?php echo $statusIcon; ?>
                </span>
                <?php echo ucfirst(htmlspecialchars($deal['status'])); ?>
            </span>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Details & Stats -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Core Info Card -->
                <div class="bg-white shadow-md rounded-lg p-6 border border-gray-100">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <span>ℹ️</span> Deal Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wider">Crop</p>
                            <p class="text-lg font-bold text-gray-800 mt-1">
                                <?php echo htmlspecialchars($deal['crop_name']); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wider">Target Market</p>
                            <p class="text-lg font-bold text-gray-800 mt-1">
                                <?php echo htmlspecialchars($deal['market_name']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Target Qty -->
                    <div class="bg-white p-5 rounded-lg shadow-md border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Target Quantity</p>
                        <p class="text-2xl font-bold text-gray-800 font-mono">
                            <?php echo number_format($deal['target_quantity'], 2); ?>
                        </p>
                    </div>

                    <!-- Aggregated Qty -->
                    <div class="bg-white p-5 rounded-lg shadow-md border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Aggregated Quantity</p>
                        <p
                            class="text-2xl font-bold <?php echo $deal['aggregated_quantity'] >= $deal['target_quantity'] ? 'text-green-600' : 'text-blue-600'; ?> font-mono">
                            <?php echo number_format($deal['aggregated_quantity'], 2); ?>
                        </p>
                    </div>

                    <!-- Carbon Saved -->
                    <div class="bg-white p-5 rounded-lg shadow-md border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">CO₂ Carbon Saved</p>
                        <p class="text-2xl font-bold text-green-600 font-mono">
                            <?php echo number_format($deal['carbon_saved'], 2); ?> kg
                        </p>
                    </div>

                    <!-- Logistics Cost -->
                    <div class="bg-white p-5 rounded-lg shadow-md border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Logistics Cost</p>
                        <p class="text-2xl font-bold text-orange-600 font-mono">
                            ৳<?php echo number_format($deal['logistics_cost'], 2); ?>
                        </p>
                    </div>

                    <!-- Farmers Count -->
                    <div class="bg-white p-5 rounded-lg shadow-md border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Total Farmers</p>
                        <p class="text-2xl font-bold text-indigo-600">
                            <?php echo count($participants); ?> 👨🌾
                        </p>
                    </div>

                    <!-- Fulfillment % -->
                    <?php
                    $fulfillment = ($deal['target_quantity'] > 0)
                        ? ($deal['aggregated_quantity'] / $deal['target_quantity']) * 100
                        : 0;
                    $fulfillment = min($fulfillment, 100); // Cap at 100 for display if needed, though over-fulfillment is possible logic-wise
                    ?>
                    <div class="bg-white p-5 rounded-lg shadow-md border border-gray-100 relative overflow-hidden">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Fulfillment</p>
                        <div class="flex items-end gap-2">
                            <p class="text-2xl font-bold text-gray-800">
                                <?php echo number_format($fulfillment, 1); ?>%
                            </p>
                        </div>
                        <!-- Progress Bar Background -->
                        <div class="absolute bottom-0 left-0 h-1bg-gray-100 w-full">
                            <div class="h-1 bg-primary" style="width: <?php echo $fulfillment; ?>%"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Participants List -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-md rounded-lg p-6 border border-gray-100 h-full">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <span>👥</span> Participants
                    </h2>

                    <div class="overflow-y-auto max-h-[500px] pr-2 custom-scrollbar">
                        <?php if (count($participants) > 0): ?>
                            <table class="w-full">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Farmer</th>
                                        <th
                                            class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Qty</th>
                                        <th
                                            class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Cost Share</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($participants as $p): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                                <?php echo htmlspecialchars($p['farmer_name']); ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right font-mono">
                                                <?php echo number_format($p['quantity_allocated'], 2); ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right font-mono text-orange-700">
                                                ৳<?php echo number_format($p['cost_share'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500 italic">
                                No farmers participated in this deal.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>
</div>

<?php require '../dashboard/partials/footer.php'; ?>