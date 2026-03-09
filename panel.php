<?php
// Configuration
include 'includes/config.php';

// Database connection
$servername = "localhost";  // Your server, typically "localhost" for XAMPP
$dbusername = "root";  // Your database username
$dbpassword = "";  // Your database password, typically empty for XAMPP
$dbname = "kidstroop";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Debugging: Check values before executing the query
    if (empty($username) || empty($password)) {
        $error = "Username or password is empty.";
    } else {
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = 'admin'";
        $result = $conn->query($sql);

        if ($result === false) {
            $error = "Error: " . $conn->error;
        } elseif ($result->num_rows > 0) {
            $_SESSION['loggedin'] = true;
        } else {
            $error = "Invalid login credentials";
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Check if logged in
if (!isset($_SESSION['loggedin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="text-center">Admin Login</h2>
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Handle file actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

if ($action == 'delete' && $file) {
    unlink($file);
}

if ($action == 'update' && $file && isset($_POST['content'])) {
    file_put_contents($file, $_POST['content']);
}

// List files
$files = scandir(__DIR__);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2 class="text-center">Admin Panel</h2>
    <a href="?logout" class="btn btn-danger">Logout</a>
    <h3>Files:</h3>
    <ul class="list-group">
        <?php foreach ($files as $file): ?>
            <?php if ($file != '.' && $file != '..'): ?>
                <li class="list-group-item">
                    <?php echo $file; ?>
                    <div class="float-right">
                        <a href="?action=delete&file=<?php echo $file; ?>" class="btn btn-danger btn-sm">Delete</a>
                        <a href="?action=edit&file=<?php echo $file; ?>" class="btn btn-warning btn-sm">Edit</a>
                    </div>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php if ($action == 'edit' && $file): ?>
        <h3>Edit File: <?php echo $file; ?></h3>
        <form method="post">
            <div class="form-group">
                <textarea class="form-control" name="content" rows="10"><?php echo file_get_contents($file); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    <?php endif; ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
