<?php
/**
 * AgriSense - Advanced Aggregation Engine (Virtual Cooperative)
 * 
 * Allows users to aggregate supply from multiple farmers to meet a specific market demand.
 * Integrates with stored procedure sp_auto_aggregate_order.
 */

// Include navigation header and database connection
require '../dashboard/partials/header.php';
require '../db/connection.php';

$pdo = getConnection();

// Initialize variables
$message = '';
$messageType = '';
$dealData = null;
$participants = [];
$aggregationSuccess = false;

// Form state variables
$selected_crop_id = '';
$selected_market_id = '';
$input_quantity = '';

// Fetch dropdown data safely
try {
    $crops = $pdo->query("SELECT crop_id, crop_name FROM crops ORDER BY crop_name")->fetchAll();
    $markets = $pdo->query("SELECT market_id, market_name FROM markets ORDER BY market_name")->fetchAll();
} catch (PDOException $e) {
    error_log("Database Error (Fetch Dropdowns): " . $e->getMessage());
    die("System error. Please contact administrator.");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and Validate Inputs
    $submit_crop_id = filter_input(INPUT_POST, 'crop_id', FILTER_VALIDATE_INT);
    $submit_market_id = filter_input(INPUT_POST, 'market_id', FILTER_VALIDATE_INT);
    $submit_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);

    // Retain values in case of error
    $selected_crop_id = $submit_crop_id;
    $selected_market_id = $submit_market_id;
    $input_quantity = $submit_quantity;

    if (!$submit_quantity || $submit_quantity <= 0) {
        $message = "Please enter a valid quantity greater than 0.";
        $messageType = 'error';
    } elseif (!$submit_crop_id || !$submit_market_id) {
        $message = "Invalid crop or market selection.";
        $messageType = 'error';
    } else {
        try {
            // Prepare and call stored procedure
            // We use prepared statements to prevent SQL injection
            $stmt = $pdo->prepare("CALL sp_auto_aggregate_order(:crop_id, :market_id, :quantity)");
            $stmt->execute([
                ':crop_id' => $submit_crop_id,
                ':market_id' => $submit_market_id,
                ':quantity' => $submit_quantity
            ]);

            // Clean up stored procedure call result set to allow further queries
            $stmt->closeCursor();

            $aggregationSuccess = true;
            $message = "Aggregation order processed successfully.";
            $messageType = 'success';

            // Safe Deal Fetching: Filter by the parameters we just used to reduce concurrency risks
            $stmt = $pdo->prepare("
                SELECT * 
                FROM virtual_coop_deals 
                WHERE crop_id = :crop_id 
                  AND market_id = :market_id 
                ORDER BY deal_id DESC 
                LIMIT 1
            ");
            $stmt->execute([
                ':crop_id' => $submit_crop_id,
                ':market_id' => $submit_market_id
            ]);
            $dealData = $stmt->fetch();

            if ($dealData) {
                // Fetch participants for the specific deal
                $stmt = $pdo->prepare("
                    SELECT dp.quantity_allocated, f.farmer_name 
                    FROM deal_participants dp 
                    JOIN farmers f ON dp.farmer_id = f.farmer_id 
                    WHERE dp.deal_id = :deal_id
                    ORDER BY dp.quantity_allocated DESC
                ");
                $stmt->execute([':deal_id' => $dealData['deal_id']]);
                $participants = $stmt->fetchAll();
            }

            // Clear form inputs on success to prevent accidental re-submission
            $selected_crop_id = '';
            $selected_market_id = '';
            $input_quantity = '';

        } catch (PDOException $e) {
            // Log the actual error internally
            error_log("Database Error (Aggregation): " . $e->getMessage());
            // Show generic user-friendly message
            $message = "Aggregation failed due to a database error. Please try again.";
            $messageType = 'error';
        } catch (Exception $e) {
            error_log("General Error: " . $e->getMessage());
            $message = "An unexpected error occurred.";
            $messageType = 'error';
        }
    }
}
?>

<div class="container mx-auto px-6 py-8">
    <div class="flex items-center gap-3 mb-6">
        <span class="text-3xl">🤝</span>
        <h1 class="text-3xl font-bold text-gray-800">Advanced Aggregation Engine (Virtual Cooperative)</h1>
    </div>

    <!-- Aggregation Form -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8 border border-gray-100">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <span>📝</span> Create Aggregation Order
        </h2>

        <?php if ($message): ?>
            <div
                class="p-4 mb-4 rounded border-l-4 <?php echo $messageType === 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700'; ?>">
                <div class="flex items-center gap-2">
                    <span><?php echo $messageType === 'success' ? '✅' : '⚠️'; ?></span>
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Crop Selection -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="crop_id">Select Crop</label>
                    <div class="relative">
                        <select name="crop_id" id="crop_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-primary focus:border-primary p-2.5 border bg-white"
                            required>
                            <option value="" disabled <?php echo empty($selected_crop_id) ? 'selected' : ''; ?>>--
                                Choose a Crop --</option>
                            <?php foreach ($crops as $crop): ?>
                                <option value="<?php echo htmlspecialchars($crop['crop_id']); ?>" <?php echo $selected_crop_id == $crop['crop_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($crop['crop_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Market Selection -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="market_id">Select Market</label>
                    <div class="relative">
                        <select name="market_id" id="market_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-primary focus:border-primary p-2.5 border bg-white"
                            required>
                            <option value="" disabled <?php echo empty($selected_market_id) ? 'selected' : ''; ?>>--
                                Choose a Market --</option>
                            <?php foreach ($markets as $market): ?>
                                <option value="<?php echo htmlspecialchars($market['market_id']); ?>" <?php echo $selected_market_id == $market['market_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($market['market_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Quantity Input -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="quantity">Target Quantity (Units)</label>
                    <input type="number" name="quantity" id="quantity"
                        value="<?php echo htmlspecialchars($input_quantity); ?>" min="1" step="0.01"
                        placeholder="e.g. 500"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-primary focus:border-primary p-2.5 border"
                        required>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="bg-primary hover:bg-primary-dark text-white font-bold py-2.5 px-8 rounded-lg transition duration-200 flex items-center gap-2 shadow-lg">
                    <span>⚡</span> Auto Aggregate Supply
                </button>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <?php if ($dealData): ?>
        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-100 animate-fade-in">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h2 class="text-xl font-semibold text-gray-700 flex items-center gap-2">
                    <span>📊</span> Aggregation Results
                </h2>

                <?php
                $statusColor = 'bg-gray-100 text-gray-800 border-gray-200';
                $statusIcon = '⚪';

                if ($dealData['status'] === 'fulfilled') {
                    $statusColor = 'bg-green-100 text-green-800 border-green-200';
                    $statusIcon = '✅';
                } elseif ($dealData['status'] === 'partial') {
                    $statusColor = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    $statusIcon = '⚠️';
                } elseif ($dealData['status'] === 'cancelled') {
                    $statusColor = 'bg-red-100 text-red-800 border-red-200';
                    $statusIcon = '❌';
                }
                ?>
                <span
                    class="px-4 py-1.5 rounded-full text-sm font-bold border <?php echo $statusColor; ?> flex items-center gap-2 shadow-sm">
                    <span><?php echo $statusIcon; ?></span>
                    <?php echo ucfirst(htmlspecialchars($dealData['status'])); ?>
                </span>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 text-center">
                <!-- Deal ID -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Deal ID</p>
                    <p class="text-2xl font-bold text-gray-800">#<?php echo htmlspecialchars($dealData['deal_id']); ?></p>
                </div>

                <!-- Target Qty -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Target Quantity</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?php echo number_format($dealData['target_quantity'], 2); ?></p>
                </div>

                <!-- Aggregated Qty -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Aggregated</p>
                    <p
                        class="text-2xl font-bold <?php echo $dealData['aggregated_quantity'] >= $dealData['target_quantity'] ? 'text-green-600' : 'text-yellow-600'; ?>">
                        <?php echo number_format($dealData['aggregated_quantity'], 2); ?>
                    </p>
                </div>

                <!-- Participant Count -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Participating Farmers</p>
                    <p class="text-2xl font-bold text-blue-600">
                        <?php echo count($participants); ?> 👨‍🌾
                    </p>
                </div>
            </div>

            <!-- Partial Fulfillment Warning -->
            <?php if ($dealData['status'] === 'partial'): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-700">Warning: Insufficient supply to fully meet the target
                                demand.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Participants Table -->
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span>📋</span> Allocation Details
            </h3>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Farmer
                                Name</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Quantity Contributed</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($participants) > 0): ?>
                            <?php foreach ($participants as $index => $participant): ?>
                                <tr
                                    class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-100 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($participant['farmer_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-mono">
                                        <?php echo number_format($participant['quantity_allocated'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center italic">
                                    No participants found for this deal.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require '../dashboard/partials/footer.php'; ?>