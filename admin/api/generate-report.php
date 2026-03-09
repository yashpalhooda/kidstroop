<?php
include __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['report_type'])) {
    echo json_encode(['success' => false, 'message' => 'Report type not specified']);
    exit();
}

try {
    $pdo = getDBConnection();
    $report = [];

    switch ($data['report_type']) {
        case 'user-activity':
            // Get user activity completion statistics
            $stmt = $pdo->query("
                SELECT 
                    u.full_name,
                    COUNT(ua.id) as total_activities,
                    SUM(CASE WHEN ua.completed = 1 THEN 1 ELSE 0 END) as completed_activities,
                    ROUND((SUM(CASE WHEN ua.completed = 1 THEN 1 ELSE 0 END) / COUNT(ua.id)) * 100, 2) as completion_rate
                FROM users u
                LEFT JOIN user_activities ua ON u.id = ua.user_id
                GROUP BY u.id, u.full_name
                ORDER BY completion_rate DESC"
            );
            $report['user_activity'] = $stmt->fetchAll();

            // Get activity type distribution
            $stmt = $pdo->query("
                SELECT 
                    a.type,
                    COUNT(*) as total,
                    SUM(CASE WHEN ua.completed = 1 THEN 1 ELSE 0 END) as completed
                FROM activities a
                LEFT JOIN user_activities ua ON a.id = ua.activity_id
                GROUP BY a.type"
            );
            $report['activity_types'] = $stmt->fetchAll();
            break;

        case 'achievements':
            // Get achievement completion statistics
            $stmt = $pdo->query("
                SELECT 
                    a.name as achievement_name,
                    COUNT(DISTINCT ua.user_id) as total_users,
                    ROUND(AVG(ua.progress), 2) as avg_progress
                FROM achievements a
                LEFT JOIN user_achievements ua ON a.id = ua.achievement_id
                GROUP BY a.id, a.name
                ORDER BY avg_progress DESC"
            );
            $report['achievement_stats'] = $stmt->fetchAll();

            // Get top achievers
            $stmt = $pdo->query("
                SELECT 
                    u.full_name,
                    COUNT(DISTINCT ua.achievement_id) as achievements_completed
                FROM users u
                JOIN user_achievements ua ON u.id = ua.user_id
                WHERE ua.progress = 100
                GROUP BY u.id, u.full_name
                ORDER BY achievements_completed DESC
                LIMIT 10"
            );
            $report['top_achievers'] = $stmt->fetchAll();
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid report type']);
            exit();
    }

    echo json_encode([
        'success' => true,
        'report' => $report
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>