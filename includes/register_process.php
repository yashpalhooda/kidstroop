<?php
// Include your configuration file
include __DIR__ . '/config.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the request method is set and is POST
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    // Check if all required fields are set and not empty
    if (isset($_POST['name'], $_POST['email'], $_POST['mobile'], $_POST['password']) &&
        !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['mobile']) && !empty($_POST['password'])) {

        $name = h($_POST['name']); // Sanitize user input
        $email = h($_POST['email']); // Sanitize user input
        $mobile = h($_POST['mobile']); // Sanitize user input
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

        // Validate mobile number length
        if (strlen($mobile) !== 10) {
            $_SESSION['error'] = "Mobile number must be exactly 10 digits.";
            header("Location: /includes/login.php?form=signup");
            exit();
        }

        // Get the database connection
        $pdo = getDBConnection();

        // Check if email or mobile already exists
        $checkQuery = $pdo->prepare("SELECT * FROM users WHERE email = ? OR mobile = ?");
        $checkQuery->execute([$email, $mobile]);
        $result = $checkQuery->fetchAll();

        if (count($result) > 0) {
            $_SESSION['error'] = "Email or mobile already exists.";
            header("Location: /includes/login.php?form=signup");
            exit();
        } else {
            // Insert new user
            $query = $pdo->prepare("INSERT INTO users (full_name, email, mobile, password) VALUES (?, ?, ?, ?)");
            if ($query->execute([$name, $email, $mobile, $password])) {
                // Display success message
                echo '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Registration Successful</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            min-height: 100vh;
                            background-color: #f0f8ff;
                        }
                        .success-message {
                            text-align: center;
                            padding: 2rem;
                            background-color: #e7f9e9;
                            border: 2px solid #28a745;
                            border-radius: 15px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                            animation: bounce 1s infinite;
                        }
                        .success-message h1 {
                            color: #28a745;
                        }
                        .success-message p {
                            color: #555;
                        }
                        @keyframes bounce {
                            0%, 20%, 50%, 80%, 100% {
                                transform: translateY(0);
                            }
                            40% {
                                transform: translateY(-30px);
                            }
                            60% {
                                transform: translateY(-15px);
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="success-message">
                        <h1>🎉 Registration Successful! 🎉</h1>
                        <p>Thank you for registering!</p>
                        <p>You will be redirected to the login page shortly.</p>
                    </div>
                    <script>
                        // Redirect to login page after 3 seconds
                        setTimeout(function() {
                            window.location.href = "/includes/login.php";
                        }, 3000);
                    </script>
                </body>
                </html>';
                exit();
            } else {
                echo "Error: " . $query->errorInfo()[2];
            }
        }
    } else {
        echo "All form fields are required.";
    }
} else {
    echo "Invalid request.";
}
?>
