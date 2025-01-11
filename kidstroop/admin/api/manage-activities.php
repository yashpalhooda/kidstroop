<?php
require_once __DIR__ . '/../../includes/config.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check authentication
if (!isLoggedIn() || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get database connection
try {
    $pdo = getDBConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Helper function to validate activity data
function validateActivityData($data) {
    $required_fields = ['title', 'type', 'duration', 'content'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = "Field '$field' is required";
        }
    }
    
    if (isset($data['type'])) {
        $valid_types = ['exercise', 'learning', 'creative'];
        if (!in_array($data['type'], $valid_types)) {
            $errors[] = "Invalid activity type. Must be one of: " . implode(', ', $valid_types);
        }
    }
    
    return $errors;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get all activities or specific activity
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $activity = $stmt->fetch();
                
                if (!$activity) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Activity not found']);
                    exit();
                }
                
                echo json_encode(['success' => true, 'activity' => $activity]);
            } else {
                $stmt = $pdo->query("
                    SELECT 
                        a.*, 
                        COUNT(DISTINCT ua.user_id) as total_users,
                        SUM(CASE WHEN ua.completed = 1 THEN 1 ELSE 0 END) as completed_count
                    FROM activities a
                    LEFT JOIN user_activities ua ON a.id = ua.activity_id
                    GROUP BY a.id
                    ORDER BY a.created_at DESC
                ");
                $activities = $stmt->fetchAll();
                echo json_encode(['success' => true, 'activities' => $activities]);
            }
            break;

        case 'POST':
            // Add new activity
            $data = json_decode(file_get_contents('php://input'), true);
            $errors = validateActivityData($data);
            
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
                exit();
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO activities (title, type, duration, content) 
                VALUES (:title, :type, :duration, :content)
            ");
            
            $stmt->execute([
                ':title' => trim($data['title']),
                ':type' => trim($data['type']),
                ':duration' => trim($data['duration']),
                ':content' => trim($data['content'])
            ]);
            
            $newId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
            $stmt->execute([$newId]);
            $newActivity = $stmt->fetch();
            
            echo json_encode(['success' => true, 'message' => 'Activity added successfully', 'activity' => $newActivity]);
            break;

        case 'PUT':
            // Update activity
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Activity ID is required']);
                exit();
            }
            
            $errors = validateActivityData($data);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
                exit();
            }
            
            // Check if activity exists
            $stmt = $pdo->prepare("SELECT id FROM activities WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Activity not found']);
                exit();
            }
            
            $stmt = $pdo->prepare("
                UPDATE activities 
                SET title = :title, 
                    type = :type, 
                    duration = :duration, 
                    content = :content,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $data['id'],
                ':title' => trim($data['title']),
                ':type' => trim($data['type']),
                ':duration' => trim($data['duration']),
                ':content' => trim($data['content'])
            ]);
            
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedActivity = $stmt->fetch();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Activity updated successfully',
                'activity' => $updatedActivity
            ]);
            break;

        case 'DELETE':
            // Delete activity
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Activity ID is required']);
                exit();
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Delete related user activities first
                $stmt = $pdo->prepare("DELETE FROM user_activities WHERE activity_id = ?");
                $stmt->execute([$data['id']]);
                
                // Delete the activity
                $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception('Activity not found');
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code($e->getMessage() === 'Activity not found' ? 404 : 500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'debug' => $e->getMessage()
    ]);
}
?>