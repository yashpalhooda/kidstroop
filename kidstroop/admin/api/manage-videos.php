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
        // Get all videos
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC");
            $videos = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'videos' => $videos
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
        break;

    case 'POST':
        // Add new video
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['title'], $data['duration'], $data['thumbnail'], $data['video_url'])) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("INSERT INTO videos (title, duration, thumbnail, video_url) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $data['title'],
                    $data['duration'],
                    $data['thumbnail'],
                    $data['video_url']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Video added successfully'
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
        // Update video
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'], $data['title'], $data['duration'], $data['thumbnail'], $data['video_url'])) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE videos SET title = ?, duration = ?, thumbnail = ?, video_url = ? WHERE id = ?");
                $stmt->execute([
                    $data['title'],
                    $data['duration'],
                    $data['thumbnail'],
                    $data['video_url'],
                    $data['id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Video updated successfully'
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
        // Delete video
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'])) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Video deleted successfully'
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
                'message' => 'Video ID not provided'
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