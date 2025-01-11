<?php
include __DIR__ . '/includes/config.php';

// Only allow this script to run locally
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('This script can only be run locally');
}

try {
    $pdo = getDBConnection();
    
    // Get all users
    $stmt = $pdo->query("SELECT id, full_name, email, is_admin FROM users");
    $users = $stmt->fetchAll();
    
    // If form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        
        // Update user to admin
        $stmt = $pdo->prepare("UPDATE users SET is_admin = TRUE WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo "<div style='color: green; margin-bottom: 20px;'>User has been made admin successfully!</div>";
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set Admin User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .admin { color: green; }
        .not-admin { color: red; }
    </style>
</head>
<body>
    <h2>Set Admin User</h2>
    <p>Select a user to make them an admin:</p>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Admin Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td class="<?php echo $user['is_admin'] ? 'admin' : 'not-admin'; ?>">
                <?php echo $user['is_admin'] ? 'Admin' : 'Not Admin'; ?>
            </td>
            <td>
                <?php if (!$user['is_admin']): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit">Make Admin</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

<?php
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>