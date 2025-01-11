<?php
include __DIR__ . '/../includes/config.php';

// Check for logout POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit();
}

// Regular login check
if (!isLoggedIn()) {
    header("Location: ../includes/login.php");
    exit();
}

$currentUser = getCurrentUser();

$dailyActivities = [
    [
        'id' => 1,
        'title' => 'Exercise Time',
        'type' => 'exercise',
        'duration' => '15 mins',
        'completed' => false,
        'content' => 'Do 10 jumping jacks and 5 push-ups'
    ],
    [
        'id' => 2,
        'title' => 'Story Time',
        'type' => 'learning',
        'duration' => '20 mins',
        'completed' => false,
        'content' => 'Read a story and answer questions'
    ],
    [
        'id' => 3,
        'title' => 'Art Time',
        'type' => 'creative',
        'duration' => '30 mins',
        'completed' => false,
        'content' => 'Draw your favorite animal'
    ],
    [
        'id' => 3,
        'title' => 'Math Challenge',
        'type' => 'mathematics',
        'duration' => '30 mins',
        'completed' => false,
        'content' => 'Draw your favorite animal'
    ],
];

$videos = [
    [
        'title' => 'Learn Colors',
        'duration' => '5:20',
        'thumbnail' => '/api/placeholder/200/120'
    ],
    [
        'title' => 'Count with Animals',
        'duration' => '4:15',
        'thumbnail' => '/api/placeholder/200/120'
    ],
    [
        'title' => 'Easy Science',
        'duration' => '6:30',
        'thumbnail' => '/api/placeholder/200/120'
    ],
];

$achievements = [
    [
        'name' => 'Reading Star',
        'progress' => 80,
        'description' => 'Complete 5 reading activities'
    ],
    [
        'name' => 'Exercise Champion',
        'progress' => 60,
        'description' => 'Complete 10 exercise activities'
    ],
    [
        'name' => 'Art Master',
        'progress' => 45,
        'description' => 'Complete 3 art projects'
    ],
    [
        'name' => 'Math Champion',
        'progress' => 45,
        'description' => 'Complete 3 art projects'
    ],
];

$stats = [
    'totalActivities' => count($dailyActivities),
    'completedActivities' => array_reduce($dailyActivities, function ($carry, $item) {
        return $carry + ($item['completed'] ? 1 : 0);
    }, 0),
    'achievementProgress' => array_reduce($achievements, function ($carry, $item) {
        return $carry + $item['progress'];
    }, 0) / count($achievements)
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kidstroop User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --accent-color: rgb(252, 5, 67);
            --success-color: #95E1D3;
        }

        body {
            background-color: #f8f9fe;
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }

        .activity-card {
            border-radius: 15px;
            border: none;
            transition: transform 0.3s;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .activity-card:hover {
            transform: translateY(-5px);
        }

        .video-card {
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
        }

        .achievement-bar {
            height: 20px;
            border-radius: 10px;
            background: var(--accent-color);
        }

        .navbar-custom {
            background-color: var(--primary-color);
            padding: 15px 0;
        }

        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }

        .exercise {
            background-color: var(--primary-color);
        }

        .learning {
            background-color: var(--secondary-color);
        }

        .creative {
            background-color: var(--accent-color);
        }

        .completion-badge {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            width: auto;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fs-3" href="#"><img src="/logo.png" alt="logo" style="height: 70px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-trophy"></i> Achievements</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                                    <i class="fas fa-id-card"></i> View Profile
                                </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                    <i class="fas fa-key"></i> Change Password
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h1 class="display-6">👋 Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</h1>
            <p class="mb-0">Ready for today's exciting adventures?</p>
        </div>

        <!-- Daily Activities Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4">📅 Today's Fun Activities</h2>
                <?php foreach ($dailyActivities as $activity): ?>
                    <div class="activity-card card mb-3 position-relative">
                        <div class="card-body d-flex align-items-center">
                            <div class="activity-icon <?php echo $activity['type']; ?> text-white">
                                <?php
                                $icon = $activity['type'] === 'exercise' ? 'running' : ($activity['type'] === 'learning' ? 'book' : 'paint-brush');
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($activity['title']); ?></h5>
                                <p class="card-text text-muted mb-0"><?php echo htmlspecialchars($activity['duration']); ?></p>
                            </div>
                            <div class="completion-badge">
                                <?php if ($activity['completed']): ?>
                                    <i class="fas fa-check-circle text-success fs-3"></i>
                                <?php else: ?>
                                    <button class="btn btn-primary rounded-pill" onclick="completeActivity(this, <?php echo $activity['id']; ?>)">
                                        Start Now!
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Learning Videos Section -->
        <h2 class="mb-4">🎥 Fun Learning Videos</h2>
        <div class="row mb-4">
            <?php foreach ($videos as $video): ?>
                <div class="col-md-4 mb-3">
                    <div class="video-card card">
                        <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" class="card-img-top" alt="Video thumbnail">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h5>
                            <p class="card-text"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($video['duration']); ?></p>
                            <button class="btn btn-primary w-100 rounded-pill">
                                <i class="fas fa-play me-2"></i> Watch Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Achievements Section -->
        <h2 class="mb-4">🏆 Your Achievements</h2>
        <div class="row">
            <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-4 mb-3">
                    <div class="card activity-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($achievement['name']); ?></h5>
                            <p class="card-text small text-muted"><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <div class="progress">
                                <div class="progress-bar achievement-bar" role="progressbar"
                                    style="width: <?php echo $achievement['progress']; ?>%">
                                    <?php echo $achievement['progress']; ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">My Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="update_profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="tel" class="form-control" name="mobile" value="<?php echo htmlspecialchars($currentUser['mobile']); ?>">
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="update_password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logout Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to logout?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="handleLogout()">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script>
        function completeActivity(button, activityId) {
            button.outerHTML = '<i class="fas fa-check-circle text-success fs-3"></i>';

            // Trigger confetti animation
            confetti({
                particleCount: 100,
                spread: 70,
                origin: {
                    y: 0.6
                }
            });
        }

        function handleLogout() {
            fetch('user-dashboard.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create and show success alert
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert alert-success alert-dismissible fade show';
                        successAlert.innerHTML = `
                        Logout successful! Redirecting...
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                        document.body.appendChild(successAlert);

                        // Hide the logout modal
                        const logoutModal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
                        logoutModal.hide();

                        // Redirect after showing message
                        setTimeout(() => {
                            window.location.href = '../includes/login.php';
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during logout. Please try again.');
                });
        }

        // Show success/error messages if they exist in session
        <?php if (isset($_SESSION['success'])): ?>
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show';
            successAlert.innerHTML = `
                <?php echo addslashes($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(successAlert);
            setTimeout(() => successAlert.remove(), 3000);
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
            errorAlert.innerHTML = `
                <?php echo addslashes($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(errorAlert);
            setTimeout(() => errorAlert.remove(), 3000);
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>

</html>