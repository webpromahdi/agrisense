<?php
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();
$errors = [];
$success = false;

if (AuthController::isLoggedIn()) {
    header('Location: /agrisense/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($auth->register($name, $email, $password)) {
        $success = true;
    } else {
        $errors = $auth->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - AgriSense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 20px 60px rgba(34, 197, 94, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .input-field {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(34, 197, 94, 0.2);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            background: white;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .error-card {
            background: rgba(254, 226, 226, 0.9);
            border: 1px solid rgba(248, 113, 113, 0.3);
        }
        
        .success-card {
            background: rgba(209, 250, 229, 0.9);
            border: 1px solid rgba(52, 211, 153, 0.3);
        }
        
        .input-error {
            border-color: #f87171;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-lg">
                    <span class="text-3xl text-white">🌾</span>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">AgriSense</h1>
            <p class="text-emerald-600 mt-2">Agricultural Market Intelligence</p>
        </div>

        <div class="glass-card rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Create Account</h2>

            <?php if ($success): ?>
                <div class="mb-6 p-4 success-card rounded-xl text-emerald-800">
                    <p class="font-medium">Registration successful!</p>
                    <p class="text-sm mt-1">You can now <a href="login.php" class="underline font-medium text-emerald-700">login to your account</a>.</p>
                </div>
            <?php else: ?>

                <?php if (isset($errors['general'])): ?>
                    <div class="mb-4 p-4 error-card rounded-xl text-red-700">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name
                            </label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none <?= isset($errors['name']) ? 'input-error' : '' ?>"
                                placeholder="Enter your full name">
                            <?php if (isset($errors['name'])): ?>
                                <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none <?= isset($errors['email']) ? 'input-error' : '' ?>"
                                placeholder="example@email.com">
                            <?php if (isset($errors['email'])): ?>
                                <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <input type="password" id="password" name="password"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none <?= isset($errors['password']) ? 'input-error' : '' ?>"
                                placeholder="Create a strong password">
                            <?php if (isset($errors['password'])): ?>
                                <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
                            <?php else: ?>
                                <p class="mt-2 text-xs text-gray-500">Min 6 characters with uppercase, lowercase, number, and special character</p>
                            <?php endif; ?>
                        </div>

                        <button type="submit"
                            class="w-full btn-primary text-white font-medium py-3 px-4 rounded-lg">
                            Create Account
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-8 pt-6 border-t border-emerald-100">
                <p class="text-center text-gray-600">
                    Already have an account?
                    <a href="login.php" class="font-semibold text-emerald-600 hover:text-emerald-700">Sign In</a>
                </p>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="/agrisense/index.php" class="text-emerald-600 hover:text-emerald-700 text-sm">
                ← Back to Home
            </a>
        </div>
    </div>
</body>
</html>