<?php
include __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all achievements
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM achievements ORDER BY created_at DESC");
            $achievements = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'achievements' => $achievements
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
        break;

    case 'POST':
        // Add new achievement
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['name'], $data['description'], $data['required_progress'])) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("INSERT INTO achievements (name, description, required_progress) VALUES (?, ?, ?)");
                $stmt->execute([
                    $data['name'],
                    $data['description'],
                    $data['required_progress']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Achievement added successfully'
                ]);
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error occurred'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
        }
        break;

    case 'PUT':
        // Update achievement
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'], $data['name'], $data['description'], $data['required_progress'])) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE achievements SET name = ?, description = ?, required_progress = ? WHERE id = ?");
                $stmt->execute([
                    $data['name'],
                    $data['description'],
                    $data['required_progress'],
                    $data['id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Achievement updated successfully'
                ]);
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error occurred'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
        }
        break;

    case 'DELETE':
        // Delete achievement
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'])) {
            try {
                $pdo = getDBConnection();
                // Delete achievement and related user achievements
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM user_achievements WHERE achievement_id = ?");
                $stmt->execute([$data['id']]);
                
                $stmt = $pdo->prepare("DELETE FROM achievements WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Achievement deleted successfully'
                ]);
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error occurred'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Achievement ID not provided'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
        break;
}
?>