<?php
include __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['activity_id'])) {
        $activityId = $data['activity_id'];
        $userId = $_SESSION['user_id'];
        
        try {
            $pdo = getDBConnection();
            
            // Check if activity exists and get its details
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
            $stmt->execute([$activityId]);
            $activity = $stmt->fetch();
            
            if (!$activity) {
                echo json_encode(['success' => false, 'message' => 'Activity not found']);
                exit();
            }
            
            // Update or insert user activity record
            $stmt = $pdo->prepare("
                INSERT INTO user_activities (user_id, activity_id, completed, completed_at)
                VALUES (?, ?, TRUE, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                completed = TRUE,
                completed_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$userId, $activityId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Activity marked as complete'
            ]);
            exit();
        } catch (PDOException $e) {
            error_log("Activity Update Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
            exit();
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit();
?>