<?php
include __DIR__ . '/includes/config.php';

try {
    $pdo = getDBConnection();
    

    // Create activities table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        type ENUM('exercise', 'learning', 'creative') NOT NULL,
        duration VARCHAR(20) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create user_activities table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        activity_id INT NOT NULL,
        completed BOOLEAN DEFAULT FALSE,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
        UNIQUE KEY user_activity (user_id, activity_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create videos table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        duration VARCHAR(20) NOT NULL,
        thumbnail VARCHAR(255) NOT NULL,
        video_url VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create achievements table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        required_progress INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create user_achievements table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        achievement_id INT NOT NULL,
        progress INT DEFAULT 0,
        completed BOOLEAN DEFAULT FALSE,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
        UNIQUE KEY user_achievement (user_id, achievement_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Database schema updated successfully!";
} catch (PDOException $e) {
    die("Error updating database schema: " . $e->getMessage());
}
?>