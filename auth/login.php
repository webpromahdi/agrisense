<?php
/**
 * AgriSense - User Login Page
 * 
 * Handles user authentication and session creation
 */

require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController();
$errors = [];

// Redirect if already logged in
if (AuthController::isLoggedIn()) {
    header('Location: /agrisense/index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($auth->login($email, $password)) {
        header('Location: /agrisense/index.php');
        exit;
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
    <title>Login - AgriSense</title>
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

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-100">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Welcome Back</h2>

            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                        placeholder="example@email.com">
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-slate-50 focus:bg-white"
                        placeholder="Enter your password">
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                    Login
                </button>
            </form>

            <!-- Signup Link -->
            <div class="mt-6 text-center">
                <p class="text-slate-500">
                    Don't have an account?
                    <a href="signup.php" class="text-green-600 hover:text-green-700 font-semibold">Sign Up</a>
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