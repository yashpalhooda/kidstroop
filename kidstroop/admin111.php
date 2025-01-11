<?php
include './includes/config.php';

try {
    $pdo = getDBConnection();
    
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $query = "INSERT INTO admin (username, password) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $password]);
    
    echo "Admin user added successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
