<!-- verify_code.php -->
<?php
require_once __DIR__ . '/../controllers/FarmerUpdateController.php';

if (isset($_GET['logout'])) {
    FarmerUpdateController::clearFarmerSession();
    header('Location: /agrisense/index.php');
    exit;
}

$controller = new FarmerUpdateController();
$errors = [];

if (FarmerUpdateController::isFarmerVerified()) {
    header('Location: update_crop.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['farmer_code'] ?? '');
    $farmer = $controller->verifyCode($code);

    if ($farmer) {
        FarmerUpdateController::startFarmerSession($farmer);
        header('Location: update_crop.php');
        exit;
    } else {
        $errors = $controller->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Verification - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* AgriSense - Professional Agriculture Theme */
        body {
            background: #FAFAF9;
            min-height: 100vh;
        }
        
        .glass-nav {
            background: #166534;
            box-shadow: 0 2px 8px rgba(22, 101, 52, 0.15);
        }
        
        .glass-card {
            background: #FFFFFF;
            border: 1px solid #E7E5E4;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
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
        
        .farmer-portal {
            background: #DCFCE7;
            border: 2px solid #BBF7D0;
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.1);
        }

        /* Text Colors */
        .text-heading { color: #1C1917; }
        .text-body { color: #44403C; }
        .text-muted { color: #78716C; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation - Deep Forest Green -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">AgriSense</h1>
                            <p class="text-xs text-white/80 font-medium">Farmer Verification</p>
                        </div>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/agrisense/index.php" 
                       class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white hover:bg-white/20 transition-colors font-medium">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-heading">👨‍🌾 Farmer Verification</h1>
            <p class="text-body mt-1">Enter your unique 6-digit code to access the supply portal</p>
        </div>

        <!-- Verification Card -->
        <div class="glass-card rounded-2xl p-8">
            <h2 class="text-lg font-bold text-heading mb-4">6-Digit Security Code</h2>
            <p class="text-body text-sm mb-6">Enter the code provided to you for secure access</p>

            <?php if (isset($errors['general'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="font-semibold"><?= htmlspecialchars($errors['general']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="farmer_code" class="block text-sm font-semibold text-heading mb-2">
                        Farmer Code
                    </label>
                    <div class="relative">
                        <input type="text" id="farmer_code" name="farmer_code" maxlength="6" pattern="\d{6}"
                            inputmode="numeric" value="<?= htmlspecialchars($_POST['farmer_code'] ?? '') ?>"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-center text-2xl font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                            placeholder="000000" autofocus>
                        <div class="absolute inset-y-0 left-0 flex items-center px-3 pointer-events-none">
                            <span class="text-green-700">🔒</span>
                        </div>
                    </div>
                    <?php if (isset($errors['code'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?= htmlspecialchars($errors['code']) ?>
                        </p>
                    <?php else: ?>
                        <p class="mt-2 text-sm text-muted flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Enter the 6-digit code provided to you
                        </p>
                    <?php endif; ?>
                </div>

                <button type="submit"
                    class="w-full btn-primary px-6 py-3 rounded-xl text-lg">
                    Verify & Continue
                </button>
            </form>
        </div>
    </main>
</body>
</html>