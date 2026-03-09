<?php
include __DIR__ . '/../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../includes/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = h($_POST['full_name']);
    $mobile = h($_POST['mobile']);
    
    // Basic validation
    if (empty($fullName) || empty($mobile)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: user-dashboard.php");
        exit();
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if mobile number is already used by another user
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? AND id != ?");
        $checkStmt->execute([$mobile, $_SESSION['user_id']]);
        
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "This mobile number is already in use";
        } else {
            // Update user profile
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, mobile = ? WHERE id = ?");
            if ($stmt->execute([$fullName, $mobile, $_SESSION['user_id']])) {
                $_SESSION['success'] = "Profile updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update profile";
            }
        }
    } catch (PDOException $e) {
        error_log("Profile Update Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating profile";
    }
}

header("Location: user-dashboard.php");
exit();
?>