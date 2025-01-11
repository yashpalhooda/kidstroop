<?php
// Use an absolute path to include the configuration file from the includes directory
include __DIR__ . '/includes/config.php';

// Try to get the database connection
try {
    $pdo = getDBConnection();
    echo "Connection successful!";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
