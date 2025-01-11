<?php
include __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

// Debug: Log request
error_log("Delete user request received");

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    error_log("Admin authentication failed");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Debug: Log request data
$rawData = file_get_contents('php://input');
error_log("Received data: " . $rawData);

$data = json_decode($rawData, true);

if (isset($data['user_id'])) {
    $userId = (int)$data['user_id'];
    error_log("Attempting to delete user ID: " . $userId);
    
    try {
        $pdo = getDBConnection();
        
        // First verify if user exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$userId]);
        
        if (!$checkStmt->fetch()) {
            error_log("User not found: " . $userId);
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            exit();
        }
        
        // Start transaction
        $pdo->beginTransaction();
        error_log("Starting deletion transaction for user: " . $userId);
        
        // Delete user's activities
        $stmt = $pdo->prepare("DELETE FROM user_activities WHERE user_id = ?");
        $result = $stmt->execute([$userId]);
        error_log("Activities deletion result: " . ($result ? "success" : "failed"));
        
        // Delete user's achievements
        $stmt = $pdo->prepare("DELETE FROM user_achievements WHERE user_id = ?");
        $result = $stmt->execute([$userId]);
        error_log("Achievements deletion result: " . ($result ? "success" : "failed"));
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$userId]);
        error_log("User deletion result: " . ($result ? "success" : "failed") . ", Rows affected: " . $stmt->rowCount());
        
        // Commit transaction
        $pdo->commit();
        error_log("Transaction committed successfully");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully',
            'user_id' => $userId
        ]);
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Database error during deletion: " . $e->getMessage());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred',
            'error' => $e->getMessage()
        ]);
    }
} else {
    error_log("No user_id provided in request");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID not provided'
    ]);
}
?>