<?php
// Include your configuration file
include __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form query parameter is set
$showRegistrationForm = isset($_GET['form']) && $_GET['form'] === 'signup';

// Check if there's an error message in the session
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear the error message after displaying it
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        footer {
            margin-top: auto;
        }

        .card {
            border-radius: 50px;
            border: 1px solid rgb(211, 206, 206);
            transition: transform 0.3s ease-in-out;
        }

        .card.move-up {
            transform: translateY(-50px);
        }

        hr {
            border: 1px solid rgb(211, 206, 206);
        }

        .logo {
            display: block;
            margin: 0 auto 1rem;
            width: 140px;
            /* Adjust the size as needed */
            height: auto;
        }

        .form-floating .form-control:not(:placeholder-shown)+.form-label {
            opacity: 0;
        }

        .form-floating .form-control:focus+.form-label {
            opacity: 1;
            transform: translateY(-1.5rem);
        }

        .hidden-text {
            display: none;
        }

        .hidden {
            display: none;
        }

        .alert {
            display: none;
            transition: opacity 1s ease-in-out;
        }

        .alert.show {
            display: block;
            opacity: 1;
        }

        .text-danger.hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <!-- Error Alert -->
                <?php if ($error): ?>
                    <div id="errorAlert" class="alert alert-danger show" role="alert">
                        <?php echo h($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Sign In Card -->
                <div class="card p-4 shadow <?php echo $showRegistrationForm ? 'hidden' : ''; ?>" id="signInCard">
                    <img src="/logo.png" alt="Kids" class="logo">
                    <h2 class="text-center mb-4 hidden-text">Kidstroop<span style="color:#0a66c2;"></span></h2>
                    <form action="/includes/login_process.php" method="post" onsubmit="moveUp()">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="text-center mb-3">
                            <span class="text-muted">or</span>
                            <hr>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="email" name="email" placeholder="Email or phone" required>
                            <label for="email">Email or phone</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Keep me logged in</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign in</button>
                        <div class="text-center mt-3">
                            <a href="#" class="text-decoration-none">Forgot password?</a>
                        </div>
                        <hr>
                        <div class="text-center">
                            New to Kidstroop? <a href="#" class="text-decoration-none" onclick="showRegistrationForm()">Join now</a>
                        </div>
                    </form>
                </div>

                <!-- Registration Card -->
                <div class="card p-4 shadow <?php echo $showRegistrationForm ? '' : 'hidden'; ?>" id="registrationCard">
                    <img src="/logo.png" alt="Kids" class="logo">
                    <h2 class="text-center mb-4 hidden-text">Kidstroop<span style="color:#0a66c2;"></span></h2>
                    <form action="/includes/register_process.php" method="post" onsubmit="moveUp()">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="text-center mb-3">
                            <span class="text-muted">or</span>
                            <hr>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
                            <label for="name">Full Name</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="email" class="form-control" id="regEmail" name="email" placeholder="Email" required pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$">
                            <label for="regEmail">Email</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Mobile" required pattern="^\d{10}$" title="Please enter a valid 10-digit mobile number.">
                            <label for="mobile">Mobile</label>
                            <div id="mobileError" class="text-danger hidden">Mobile number must be exactly 10 digits.</div>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="password" class="form-control" id="regPassword" name="password" placeholder="Password" required>
                            <label for="regPassword">Password</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">I agree to the Terms and Conditions</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                        <div class="text-center mt-3">
                            <a href="#" class="text-decoration-none" onclick="showSignInForm()">Already a member? Sign in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <footer class="mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">Kidstroop</span>
            <a href="#" class="text-decoration-none text-muted mx-2">User Agreement</a>
            <a href="#" class="text-decoration-none text-muted mx-2">Privacy Policy</a>
            <a href="#" class="text-decoration-none text-muted mx-2">Community Guidelines</a>
            <a href="#" class="text-decoration-none text-muted mx-2">Cookie Policy</a>
            <a href="#" class="text-decoration-none text-muted mx-2">Copyright Policy</a>
            <a href="#" class="text-decoration-none text-muted mx-2">Send Feedback</a>
            <a href="#" class="text-decoration-none text-muted mx-2">Language</a>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function moveUp() {
            const card = document.querySelector('.card');
            card.classList.toggle('move-up');
        }

        function showRegistrationForm() {
            document.getElementById('signInCard').classList.add('hidden');
            document.getElementById('registrationCard').classList.remove('hidden');
        }

        function showSignInForm() {
            document.getElementById('signInCard').classList.remove('hidden');
            document.getElementById('registrationCard').classList.add('hidden');
        }

        // Real-time validation for mobile number
        document.getElementById('mobile').addEventListener('input', function() {
            const mobileInput = document.getElementById('mobile');
            const mobileError = document.getElementById('mobileError');
            const validMobile = /^\d{10}$/;
            if (!validMobile.test(mobileInput.value)) {
                mobileError.classList.remove('hidden');
            } else {
                mobileError.classList.add('hidden');
            }
        });

        // Hide the error alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const errorAlert = document.getElementById('errorAlert');
            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.opacity = '0';
                }, 5000); // 5 seconds
            }
        });
    </script>
</body>

</html>