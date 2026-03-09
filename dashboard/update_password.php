<?php
include __DIR__ . '/../includes/config.php';

// Check if either user or admin is logged in
if (!isLoggedIn() && !isAdminLoggedIn()) {
    header("Location: ../includes/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = "All password fields are required";
        header("Location: " . (isAdminLoggedIn() ? "dashboard.php" : "../dashboard.php"));
        exit();
    }
    
    // Verify passwords match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "New passwords do not match";
        header("Location: " . (isAdminLoggedIn() ? "dashboard.php" : "../dashboard.php"));
        exit();
    }
    
    // Password strength validation
    if (strlen($newPassword) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: " . (isAdminLoggedIn() ? "dashboard.php" : "../dashboard.php"));
        exit();
    }
    
    try {
        $pdo = getDBConnection();
        
        if (isAdminLoggedIn()) {
            // Admin password change
            $stmt = $pdo->prepare("SELECT password FROM admin WHERE username = ?");
            $stmt->execute([$_SESSION['admin_username']]);
            $user = $stmt->fetch();
            
            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = ?");
                
                if ($updateStmt->execute([$hashedPassword, $_SESSION['admin_username']])) {
                    $_SESSION['success'] = "Password updated successfully";
                } else {
                    $_SESSION['error'] = "Failed to update password";
                }
            } else {
                $_SESSION['error'] = "Current password is incorrect";
            }
        } else {
            // Regular user password change
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($updateStmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                    $_SESSION['success'] = "Password updated successfully";
                } else {
                    $_SESSION['error'] = "Failed to update password";
                }
            } else {
                $_SESSION['error'] = "Current password is incorrect";
            }
        }
    } catch (PDOException $e) {
        error_log("Password Update Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating password";
    }
    
    // Redirect based on user type
    header("Location: " . (isAdminLoggedIn() ? "dashboard.php" : "../dashboard.php"));
    exit();
}

// If not POST request, redirect back
header("Location: " . (isAdminLoggedIn() ? "dashboard.php" : "../dashboard.php"));
exit();
?>