<?php
include __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, full_name, email, mobile, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'User ID not provided'
    ]);
}
?>