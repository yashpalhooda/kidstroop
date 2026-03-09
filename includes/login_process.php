<?php
include __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging output to verify the request method
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'CSRF token validation failed.';
        header("Location: " . BASE_URL . "includes/login.php");
        exit();
    }

    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = filter_var(h($_POST['email']), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        // Debugging output
        error_log("Email: $email");

        if ($email) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                error_log("Login successful for user ID: " . $user['id']);
                header("Location: " . BASE_URL . "dashboard/user-dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = 'Invalid email or password.';
                error_log("Invalid login attempt for email: $email");
            }
        } else {
            $_SESSION['error'] = 'Invalid email format.';
        }
    } else {
        $_SESSION['error'] = 'Email and password are required.';
    }
    header("Location: " . BASE_URL . "includes/login.php");
    exit();
} else {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: " . BASE_URL . "includes/login.php");
    exit();
}
?>
