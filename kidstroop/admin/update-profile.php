<?php
include __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = h($_POST['name']);
    $email = h($_POST['email']);
    
    // Basic validation
    if (empty($name) || empty($email)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: dashboard.php");
        exit();
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if email is already used by another admin
        $checkStmt = $pdo->prepare("SELECT id FROM admin WHERE email = ? AND username != ?");
        $checkStmt->execute([$email, $_SESSION['admin_username']]);
        
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "This email is already in use";
        } else {
            // Update admin profile
            $stmt = $pdo->prepare("UPDATE admin SET name = ?, email = ? WHERE username = ?");
            if ($stmt->execute([$name, $email, $_SESSION['admin_username']])) {
                $_SESSION['success'] = "Profile updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update profile";
            }
        }
    } catch (PDOException $e) {
        error_log("Admin Profile Update Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating profile";
    }
}

header("Location: admin/dashboard.php");
exit();
?>