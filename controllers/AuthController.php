<?php
/**
 * AgriSense - Authentication Controller
 * 
 * Handles user registration, login, logout, and session management
 * Uses PDO prepared statements for security
 */

require_once __DIR__ . '/../db/connection.php';

class AuthController
{

    private $pdo;
    private $errors = [];

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    /**
     * Get validation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    public function validateEmail($email)
    {
        if (empty($email)) {
            $this->errors['email'] = 'Email is required';
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address';
            return false;
        }

        return true;
    }

    /**
     * Validate password strength
     * Requirements: 6+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        if (empty($password)) {
            $this->errors['password'] = 'Password is required';
            return false;
        }

        if (strlen($password) < 6) {
            $this->errors['password'] = 'Password must be at least 6 characters';
            return false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors['password'] = 'Password must contain at least 1 uppercase letter';
            return false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            $this->errors['password'] = 'Password must contain at least 1 lowercase letter';
            return false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            $this->errors['password'] = 'Password must contain at least 1 number';
            return false;
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $this->errors['password'] = 'Password must contain at least 1 special character (!@#$%^&*(),.?":{}|<>)';
            return false;
        }

        return true;
    }

    /**
     * Validate name
     * @param string $name
     * @return bool
     */
    public function validateName($name)
    {
        if (empty($name)) {
            $this->errors['name'] = 'Full name is required';
            return false;
        }

        if (strlen($name) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters';
            return false;
        }

        return true;
    }

    /**
     * Check if email already exists
     * @param string $email
     * @return bool
     */
    public function emailExists($email)
    {
        if ($this->pdo === null) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Email check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register a new user
     * @param string $name
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function register($name, $email, $password)
    {
        $this->errors = [];

        // Validate all fields
        $nameValid = $this->validateName($name);
        $emailValid = $this->validateEmail($email);
        $passwordValid = $this->validatePassword($password);

        if (!$nameValid || !$emailValid || !$passwordValid) {
            return false;
        }

        // Check if email already exists
        if ($this->emailExists($email)) {
            $this->errors['email'] = 'This email is already registered';
            return false;
        }

        if ($this->pdo === null) {
            $this->errors['general'] = 'Database connection failed';
            return false;
        }

        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hashedPassword]);

            return true;

        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->errors['general'] = 'Registration failed. Please try again.';
            return false;
        }
    }

    /**
     * Authenticate user and start session
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login($email, $password)
    {
        $this->errors = [];

        if (empty($email) || empty($password)) {
            $this->errors['general'] = 'Email and password are required';
            return false;
        }

        if ($this->pdo === null) {
            $this->errors['general'] = 'Database connection failed';
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, name, email, password FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->errors['general'] = 'Invalid email or password';
                return false;
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->errors['general'] = 'Invalid email or password';
                return false;
            }

            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['logged_in'] = true;

            return true;

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $this->errors['general'] = 'Login failed. Please try again.';
            return false;
        }
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current logged-in user data
     * @return array|null
     */
    public static function getCurrentUser()
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name']
        ];
    }

    /**
     * Logout user and destroy session
     */
    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();
    }

    /**
     * Require authentication - redirect to login if not logged in
     */
    public static function requireAuth()
    {
        if (!self::isLoggedIn()) {
            header('Location: /agrisense/auth/login.php');
            exit;
        }
    }
}
?>