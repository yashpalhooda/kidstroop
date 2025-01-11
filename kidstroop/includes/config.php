<?php
// Session security configuration must be before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
session_regenerate_id(true);

// Server environment detection (VSCode vs XAMPP)
$server_port = $_SERVER['SERVER_PORT'] ?? 80;
$is_vscode = $server_port == 51733;

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kidstroop');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Kidstroop');
define('BASE_URL', $is_vscode ? 'http://localhost:51733/' : 'http://localhost/');

// Error reporting (for development environment)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Database connection function
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Unable to connect to the database. Please try again later.");
    }
}

// Helper function to check if menu item is active
function isActiveMenu($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page) ? 'active' : '';
}

// Helper function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    return true;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true;
}

// Function to get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, full_name, email, mobile FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return null;
    }
}

// Function to redirect with message
function redirectWith($path, $message, $type = 'error') {
    $_SESSION[$type] = $message;
    header("Location: $path");
    exit();
}

// Initialize database if needed
function initDatabase() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Select the database
        $pdo->exec("USE `" . DB_NAME . "`");

        // Create users table if it doesn't exist (removed is_admin column)
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE,
            mobile VARCHAR(20) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (email),
            INDEX (mobile)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Create remember_tokens table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX (token),
            INDEX (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        return true;
    } catch (PDOException $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

// Initialize the database
initDatabase();
?>