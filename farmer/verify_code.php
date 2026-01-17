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
        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
        }
        
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.1);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .farmer-portal {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(34, 197, 94, 0.3);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.1);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-lg">
                            <span class="text-xl text-white">🌾</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">AgriSense</h1>
                            <p class="text-xs text-emerald-600">Farmer Verification</p>
                        </div>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/agrisense/index.php" 
                       class="px-4 py-2 glass-card rounded-lg text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50 transition-colors">
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
            <h1 class="text-2xl font-bold text-gray-800">Farmer Verification</h1>
            <p class="text-gray-600 mt-1">Enter your unique 6-digit code to access the supply portal</p>
        </div>

        <!-- Verification Card -->
        <div class="glass-card rounded-2xl p-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">6-Digit Security Code</h2>
            <p class="text-gray-600 text-sm mb-6">Enter the code provided to you for secure access</p>

            <?php if (isset($errors['general'])): ?>
                <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl text-red-700">
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="font-medium"><?= htmlspecialchars($errors['general']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="farmer_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Farmer Code
                    </label>
                    <div class="relative">
                        <input type="text" id="farmer_code" name="farmer_code" maxlength="6" pattern="\d{6}"
                            inputmode="numeric" value="<?= htmlspecialchars($_POST['farmer_code'] ?? '') ?>"
                            class="w-full px-4 py-3 glass-card rounded-xl text-center text-2xl font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            placeholder="000000" autofocus>
                        <div class="absolute inset-y-0 left-0 flex items-center px-3 pointer-events-none">
                            <span class="text-emerald-600">🔒</span>
                        </div>
                    </div>
                    <?php if (isset($errors['code'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?= htmlspecialchars($errors['code']) ?>
                        </p>
                    <?php else: ?>
                        <p class="mt-2 text-sm text-emerald-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Enter the 6-digit code provided to you
                        </p>
                    <?php endif; ?>
                </div>

                <button type="submit"
                    class="w-full btn-primary px-6 py-3 rounded-xl font-medium text-lg">
                    Verify & Continue
                </button>
            </form>
        </div>
    </main>
</body>
</html>