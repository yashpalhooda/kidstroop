<?php
include __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = "All password fields are required";
        header("Location: dashboard.php");
        exit();
    }
    
    // Verify passwords match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "New passwords do not match";
        header("Location: dashboard.php");
        exit();
    }
    
    // Password strength validation
    if (strlen($newPassword) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: dashboard.php");
        exit();
    }
    
    try {
        // Verify current password and update
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT password FROM admin WHERE username = ?");
        $stmt->execute([$_SESSION['admin_username']]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($currentPassword, $admin['password'])) {
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
    } catch (PDOException $e) {
        error_log("Admin Password Update Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating password";
    }
}

header("Location: admin/dashboard.php");
exit();
?>