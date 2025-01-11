<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/config.php';

if (isset($_POST['submit'])) {
    $pdo = getDBConnection();  // Get the PDO connection
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin WHERE username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Debugging: Print the fetched password hash and input password
        echo 'Stored password hash: ' . $user['password'] . '<br>';
        echo 'Input password: ' . $password . '<br>';
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_login'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: ./dashboard.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Username not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Admin Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
