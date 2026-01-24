<?php
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();
$errors = [];

if (AuthController::isLoggedIn()) {
    header('Location: /agrisense/index.php');
    exit;
}

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
    <style>
        body {
            background: #FAFAF9;
            min-height: 100vh;
        }

        .glass-card {
            background: #FFFFFF;
            border: 1px solid #E7E5E4;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .btn-primary {
            background: linear-gradient(135deg, #166534 0%, #14532d 100%);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(22, 101, 52, 0.25);
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        }

        .input-field {
            background: #FFFFFF;
            border: 1px solid #D6D3D1;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            background: white;
            border-color: #166534;
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.1);
        }

        .error-card {
            background: #FEE2E2;
            border: 1px solid #FECACA;
        }

        .text-heading {
            color: #1C1917;
        }

        .text-body {
            color: #44403C;
        }

        .text-muted {
            color: #78716C;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div
                    class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center shadow-lg">
                    <span class="text-3xl text-white">🌾</span>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-heading">AgriSense</h1>
            <p class="text-green-700 font-medium mt-2">Agricultural Market Intelligence</p>
        </div>

        <div class="glass-card rounded-2xl p-8">
            <h2 class="text-xl font-bold text-heading mb-6">Welcome Back</h2>

            <?php if (isset($errors['general'])): ?>
                <div class="mb-4 p-4 error-card rounded-xl text-red-700 font-medium">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-heading mb-2">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none text-body"
                            placeholder="example@email.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-heading mb-2">
                            Password
                        </label>
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none text-body"
                            placeholder="Enter your password">
                    </div>

                    <button type="submit" class="w-full btn-primary text-white py-3 px-4 rounded-lg">
                        Sign In
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-body">
                    New to AgriSense?
                    <a href="signup.php" class="font-bold text-green-700 hover:text-green-800">Create Account</a>
                </p>
            </div>
        </div>

        <div class="mt-6 text-center">
            <p class="text-muted text-sm">Agricultural Market Intelligence System</p>
        </div>
    </div>
</body>

</html>