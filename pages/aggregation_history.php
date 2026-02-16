<?php
/**
 * AgriSense - Aggregation History Dashboard
 * 
 * Displays historical records of aggregated deals from the Virtual Cooperative.
 */

// Include navigation header and database connection
require '../dashboard/partials/header.php';
require '../db/connection.php';

$pdo = getConnection();

// Initialize variables
$history = [];
$error_message = '';

// Fetch Deal History
try {
    $sql = "
        SELECT 
            v.deal_id,
            v.target_quantity,
            COALESCE(v.aggregated_quantity, 0) AS aggregated_quantity,
            v.status,
            COALESCE(v.logistics_cost, 0) AS logistics_cost,
            COALESCE(v.carbon_saved, 0) AS carbon_saved,
            v.created_at,
            c.crop_name,
            m.market_name,
            COUNT(dp.participant_id) AS total_farmers
        FROM virtual_coop_deals v
        JOIN crops c ON v.crop_id = c.crop_id
        JOIN markets m ON v.market_id = m.market_id
        LEFT JOIN deal_participants dp ON v.deal_id = dp.deal_id
        GROUP BY v.deal_id
        ORDER BY v.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $history = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Database Error (History Fetch): " . $e->getMessage());
    $error_message = "Unable to load history at this time.";
}
?>

<div class="container mx-auto px-6 py-8">
    <div class="flex items-center gap-3 mb-6">
        <span class="text-3xl">📜</span>
        <h1 class="text-3xl font-bold text-gray-800">Aggregation History Dashboard</h1>
    </div>

    <!-- History Table Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8 border border-gray-100">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <span>📚</span> Deal Records
        </h2>

        <?php if ($error_message): ?>
            <div class="p-4 mb-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                <p>
                    <?php echo htmlspecialchars($error_message); ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Deal ID
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Crop
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Market
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Aggregated
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Farmers
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Logistics
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            CO₂ Saved
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Created At
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($history) > 0): ?>
                        <?php foreach ($history as $index => $deal): ?>
                            <tr
                                class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-100 transition-colors">
                                <!-- Deal ID -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="deal_detail.php?id=<?php echo htmlspecialchars($deal['deal_id']); ?>"
                                        class="text-primary hover:text-primary-dark underline decoration-dotted underline-offset-2">
                                        #<?php echo htmlspecialchars($deal['deal_id']); ?>
                                    </a>
                                </td>

                                <!-- Crop -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($deal['crop_name']); ?>
                                </td>

                                <!-- Market -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($deal['market_name']); ?>
                                </td>

                                <!-- Aggregated Quantity -->
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold font-mono
                                    <?php echo $deal['aggregated_quantity'] >= $deal['target_quantity'] ? 'text-green-600' : 'text-blue-600'; ?>">
                                    <?php echo number_format($deal['aggregated_quantity'], 2); ?>
                                </td>

                                <!-- Farmers (ADDED) -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-700">
                                    <?php echo $deal['total_farmers']; ?> 👨🌾
                                </td>

                                <!-- Logistics -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-orange-600">
                                    ৳<?php echo number_format($deal['logistics_cost'], 2); ?>
                                </td>

                                <!-- Carbon Saved -->
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-green-700 font-semibold">
                                    <?php echo number_format($deal['carbon_saved'], 2); ?> kg
                                </td>

                                <!-- Status Badge -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php
                                    $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                    $statusLabel = ucfirst($deal['status']);

                                    if ($deal['status'] === 'fulfilled') {
                                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                    } elseif ($deal['status'] === 'partial') {
                                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                    } elseif ($deal['status'] === 'cancelled') {
                                        $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                    }
                                    ?>
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($statusLabel); ?>
                                    </span>
                                </td>

                                <!-- Created At -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    <?php echo date('M d, Y H:i', strtotime($deal['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center italic">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-2xl">📭</span>
                                    <span>No aggregation history found.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require '../dashboard/partials/footer.php'; ?>