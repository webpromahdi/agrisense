<?php
/**
 * AgriSense - User Signup Page
 * 
 * Handles new user registration with validation
 */

require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController();
$errors = [];
$success = false;

// Redirect if already logged in
if (AuthController::isLoggedIn()) {
    header('Location: /agrisense/index.php');
    exit;
}

// Process form submission
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
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center space-x-3 mb-2">
                <span class="text-4xl">🌾</span>
                <h1 class="text-3xl font-bold text-green-700">AgriSense</h1>
            </div>
            <p class="text-slate-500">Agricultural Market Intelligence</p>
        </div>

        <!-- Signup Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-100">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Create Account</h2>

            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="bg-emerald-50 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-6">
                    <p class="font-medium">Registration successful!</p>
                    <p class="text-sm">You can now <a href="login.php" class="underline font-semibold">login to your
                            account</a>.</p>
                </div>
            <?php else: ?>

                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <!-- Full Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= isset($errors['name']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                            placeholder="Enter your full name">
                        <?php if (isset($errors['name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= isset($errors['email']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                            placeholder="example@email.com">
                        <?php if (isset($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-3 border <?= isset($errors['password']) ? 'border-red-500' : 'border-slate-200' ?> rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                            placeholder="Create a strong password">
                        <?php if (isset($errors['password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
                        <?php else: ?>
                            <p class="mt-1 text-xs text-slate-500">Min 6 characters: 1 uppercase, 1 lowercase, 1 number, 1
                                special character</p>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                        Create Account
                    </button>
                </form>
            <?php endif; ?>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-slate-500">
                    Already have an account?
                    <a href="login.php" class="text-green-600 hover:text-green-700 font-semibold">Login</a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center">
            <a href="/agrisense/index.php"
                class="text-slate-500 hover:text-green-600 text-sm transition-colors duration-200">
                ← Back to Home
            </a>
        </div>
    </div>
</body>

</html>