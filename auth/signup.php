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
        /* AgriSense - Professional Agriculture Theme */
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

        .success-card {
            background: #DCFCE7;
            border: 1px solid #BBF7D0;
        }

        .input-error {
            border-color: #f87171;
        }

        /* Text Colors */
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
            <h2 class="text-xl font-bold text-heading mb-6">Create Account</h2>

            <?php if ($success): ?>
                <div class="mb-6 p-4 success-card rounded-xl text-green-800">
                    <p class="font-bold">Registration successful!</p>
                    <p class="text-sm mt-1">You can now <a href="login.php" class="underline font-bold text-green-700">login
                            to your account</a>.</p>
                </div>
            <?php else: ?>

                <?php if (isset($errors['general'])): ?>
                    <div class="mb-4 p-4 error-card rounded-xl text-red-700 font-medium">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-heading mb-2">
                                Full Name
                            </label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none text-body <?= isset($errors['name']) ? 'input-error' : '' ?>"
                                placeholder="Enter your full name">
                            <?php if (isset($errors['name'])): ?>
                                <p class="mt-2 text-sm text-red-600 font-medium"><?= htmlspecialchars($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-heading mb-2">
                                Email Address
                            </label>
                            <input type="email" id="email" name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none text-body <?= isset($errors['email']) ? 'input-error' : '' ?>"
                                placeholder="example@email.com">
                            <?php if (isset($errors['email'])): ?>
                                <p class="mt-2 text-sm text-red-600 font-medium"><?= htmlspecialchars($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-heading mb-2">
                                Password
                            </label>
                            <input type="password" id="password" name="password"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none text-body <?= isset($errors['password']) ? 'input-error' : '' ?>"
                                placeholder="Create a strong password">
                            <?php if (isset($errors['password'])): ?>
                                <p class="mt-2 text-sm text-red-600 font-medium"><?= htmlspecialchars($errors['password']) ?>
                                </p>
                            <?php else: ?>
                                <p class="mt-2 text-xs text-muted">Min 6 characters with uppercase, lowercase, number, and
                                    special character</p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="w-full btn-primary text-white py-3 px-4 rounded-lg">
                            Create Account
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-body">
                    Already have an account?
                    <a href="login.php" class="font-bold text-green-700 hover:text-green-800">Sign In</a>
                </p>
            </div>
        </div>

        <div class="mt-6 text-center">
            <p class="text-muted text-sm">Agricultural Market Intelligence System</p>
        </div>
    </div>
</body>

</html>