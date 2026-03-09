<?php
include __DIR__ . '../../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin username from session
$admin_username = $_SESSION['admin_username'] ?? '';

// Fetch statistics
$pdo = getDBConnection();

try {
    // Get total users count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];

    // Get users registered in last 7 days
    $stmt = $pdo->query("SELECT COUNT(*) as recent FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentUsers = $stmt->fetch()['recent'];

    // Get activities completion rate
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_activities,
        SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_activities
        FROM user_activities");
    $activityStats = $stmt->fetch();
    $completionRate = $activityStats['total_activities'] > 0
        ? round(($activityStats['completed_activities'] / $activityStats['total_activities']) * 100)
        : 0;

    // Get monthly user registrations for chart
    $stmt = $pdo->query("SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count 
        FROM users 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC 
        LIMIT 12");
    $monthlyRegistrations = $stmt->fetchAll();

    // Fetch latest registered users
    $stmt = $pdo->query("SELECT id, full_name, email, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5");
    $latestUsers = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Log error and show generic message
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while fetching dashboard data.");
}

// Rest of your HTML remains the same...

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kidstroop Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4154f1;
            --secondary-color: #717ff5;
            --success-color: #2eca6a;
            --info-color: #4154f1;
            --warning-color: #ffbf00;
            --danger-color: #ff4444;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 80px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .navbar {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            border-radius: 15px;
            border: none;
            margin-top: 50px;
            transition: transform 0.3s;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .bg-users {
            background-color: var(--primary-color);
        }

        .bg-activities {
            background-color: var(--success-color);
        }

        .bg-videos {
            background-color: var(--warning-color);
        }

        .bg-achievements {
            background-color: var(--info-color);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="/logo.png" alt="logo" style="height: 40px;">
                <span class="ms-2">Admin Dashboard</span>
            </a>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-home me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="showUsers">
                                <i class="fas fa-users me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#activitiesModal">
                                <i class="fas fa-tasks me-2"></i>
                                Activities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#videosModal">
                                <i class="fas fa-video me-2"></i>
                                Videos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#achievementsModal">
                                <i class="fas fa-trophy me-2"></i>
                                Achievements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#reportsModal">
                                <i class="fas fa-chart-bar me-2"></i>
                                Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="activity-icon bg-users">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title mb-0">Total Users</h5>
                                        <h2 class="mt-2 mb-0"><?php echo $totalUsers; ?></h2>
                                        <p class="text-success mb-0">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            <?php echo $recentUsers; ?> new this week
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="activity-icon bg-activities">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title mb-0">Completion Rate</h5>
                                        <h2 class="mt-2 mb-0"><?php echo $completionRate; ?>%</h2>
                                        <p class="text-muted mb-0">Activities completed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add more stat cards here -->
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-12 col-xl-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Registration Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="registrationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Activity Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="activityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestUsers as $user): ?>
                                        <tr>
                                            <td><?php echo h($user['full_name']); ?></td>
                                            <td><?php echo h($user['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- Add Users Section -->
                            <div id="usersSection" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">All Users</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Joined Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($latestUsers as $user): ?>
                                                        <tr>
                                                            <td><?php echo h($user['full_name']); ?></td>
                                                            <td><?php echo h($user['email']); ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Initialize Charts -->
                            <script>
                                // Registration Chart
                                const registrationCtx = document.getElementById('registrationChart').getContext('2d');
                                new Chart(registrationCtx, {
                                    type: 'line',
                                    data: {
                                        labels: <?php echo json_encode(array_column(array_reverse($monthlyRegistrations), 'month')); ?>,
                                        datasets: [{
                                            label: 'New Users',
                                            data: <?php echo json_encode(array_column(array_reverse($monthlyRegistrations), 'count')); ?>,
                                            borderColor: '#4154f1',
                                            tension: 0.3,
                                            fill: true,
                                            backgroundColor: 'rgba(65, 84, 241, 0.1)'
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    stepSize: 1
                                                }
                                            }
                                        }
                                    }
                                });

                                // Activity Distribution Chart
                                const activityCtx = document.getElementById('activityChart').getContext('2d');
                                new Chart(activityCtx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Exercise', 'Learning', 'Creative'],
                                        datasets: [{
                                            data: [30, 40, 30],
                                            backgroundColor: ['#FF6B6B', '#4ECDC4', '#FFD93D']
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom'
                                            }
                                        }
                                    }
                                });

                                // Functions for user management
                                function viewUser(userId) {
                                    // Fetch user details and show in modal
                                    fetch(`admin-api/get-user.php?id=${userId}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                document.getElementById('viewUserName').textContent = data.user.full_name;
                                                document.getElementById('viewUserEmail').textContent = data.user.email;
                                                document.getElementById('viewUserMobile').textContent = data.user.mobile;
                                                document.getElementById('viewUserJoined').textContent = data.user.created_at;
                                                new bootstrap.Modal(document.getElementById('viewUserModal')).show();
                                            } else {
                                                showAlert(data.message, 'danger');
                                            }
                                        });
                                }

                                function deleteUser(userId) {
                                    if (confirm('Are you sure you want to delete this user?')) {
                                        // Debug log
                                        console.log('Attempting to delete user:', userId);

                                        fetch('api/delete-user.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({
                                                    user_id: userId
                                                })
                                            })
                                            .then(response => {
                                                console.log('Response status:', response.status);
                                                return response.json();
                                            })
                                            .then(data => {
                                                console.log('Response data:', data);
                                                if (data.success) {
                                                    alert('User deleted successfully');
                                                    location.reload();
                                                } else {
                                                    alert(data.message || 'Failed to delete user');
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                alert('An error occurred while deleting the user');
                                            });
                                    }
                                }



                                function showAlert(message, type = 'success') {
                                    const alert = document.createElement('div');
                                    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
                                    alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
                                    document.body.appendChild(alert);
                                    setTimeout(() => alert.remove(), 3000);
                                }

                                function handleLogout() {
                                    window.location.href = 'logout.php';
                                }


                                // Add this to your existing script section
                                document.getElementById('showUsers').addEventListener('click', function(e) {
                                    e.preventDefault();
                                    // Hide dashboard sections
                                    document.querySelector('.row.g-4.mb-4').style.display = 'none';
                                    document.querySelector('.row.mb-4').style.display = 'none';
                                    document.querySelector('.card:last-child').style.display = 'none';
                                    // Show users section
                                    document.getElementById('usersSection').style.display = 'block';
                                });

                                // Add dashboard button functionality
                                document.querySelector('a.nav-link[href="#"]').addEventListener('click', function(e) {
                                    e.preventDefault();
                                    // Show dashboard sections
                                    document.querySelector('.row.g-4.mb-4').style.display = 'flex';
                                    document.querySelector('.row.mb-4').style.display = 'flex';
                                    document.querySelector('.card:last-child').style.display = 'block';
                                    // Hide users section
                                    document.getElementById('usersSection').style.display = 'none';
                                });

                                // Activity Management Functions
                                function showUploadModal(activityType) {
                                    $('#uploadActivityModal').modal('show');
                                    // Set hidden field for activity type
                                    $('#uploadActivityForm').data('activityType', activityType);
                                }

                                function toggleActive(activityType) {
                                    // Example AJAX call to toggle activity status
                                    fetch('api/toggle-activity.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                type: activityType
                                            })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                showAlert('Activity status updated successfully', 'success');
                                                updateActivityStats(activityType);
                                            } else {
                                                showAlert('Failed to update activity status', 'danger');
                                            }
                                        });
                                }

                                function pushToUsers(activityType) {
                                    if (confirm('Are you sure you want to push this activity to all users?')) {
                                        fetch('api/push-activity.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({
                                                    type: activityType
                                                })
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    showAlert('Activity pushed to users successfully', 'success');
                                                } else {
                                                    showAlert('Failed to push activity to users', 'danger');
                                                }
                                            });
                                    }
                                }

                                function updateActivityStats(activityType) {
                                    // Fetch updated statistics from server
                                    fetch(`api/get-activity-stats.php?type=${activityType}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                $(`#${activityType}Active`).text(data.active);
                                                $(`#${activityType}Total`).text(data.total);
                                            }
                                        });
                                }

                                // Initialize activity list
                                function loadActivityList() {
                                    fetch('api/get-activities.php')
                                        .then(response => response.json())
                                        .then(data => {
                                            const activityList = document.getElementById('activityList');
                                            activityList.innerHTML = data.activities.map(activity => `
                <tr>
                    <td>${activity.title}</td>
                    <td>${activity.type}</td>
                    <td>
                        <span class="badge bg-${activity.active ? 'success' : 'secondary'}">
                            ${activity.active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>${activity.last_updated}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary" onclick="editActivity(${activity.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger" onclick="deleteActivity(${activity.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
                                        });
                                }

                                // Initialize when modal is shown
                                $('#activitiesModal').on('shown.bs.modal', function() {
                                    loadActivityList();
                                });
                            </script>

                            <!-- Replace the existing activities modal content with this -->
                            <div class="modal fade" id="activitiesModal" tabindex="-1">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Activity Management</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Activity Cards Row -->
                                            <div class="row g-4 mb-4">
                                                <!-- Exercise Time Card -->
                                                <div class="col-md-6 col-xl-3">
                                                    <div class="card bg-primary text-white h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h5 class="card-title mb-0">Exercise Time</h5>
                                                                <i class="fas fa-running fa-2x"></i>
                                                            </div>
                                                            <p class="card-text">Active: <span id="exerciseActive">15</span></p>
                                                            <p class="card-text">Total: <span id="exerciseTotal">25</span></p>
                                                            <div class="btn-group w-100">
                                                                <button class="btn btn-light btn-sm" onclick="showUploadModal('exercise')">
                                                                    <i class="fas fa-upload"></i> Upload
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="toggleActive('exercise')">
                                                                    <i class="fas fa-power-off"></i> Toggle Active
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="pushToUsers('exercise')">
                                                                    <i class="fas fa-users"></i> Push to Users
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Story Time Card -->
                                                <div class="col-md-6 col-xl-3">
                                                    <div class="card bg-success text-white h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h5 class="card-title mb-0">Story Time</h5>
                                                                <i class="fas fa-book-reader fa-2x"></i>
                                                            </div>
                                                            <p class="card-text">Active: <span id="storyActive">20</span></p>
                                                            <p class="card-text">Total: <span id="storyTotal">30</span></p>
                                                            <div class="btn-group w-100">
                                                                <button class="btn btn-light btn-sm" onclick="showUploadModal('story')">
                                                                    <i class="fas fa-upload"></i> Upload
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="toggleActive('story')">
                                                                    <i class="fas fa-power-off"></i> Toggle Active
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="pushToUsers('story')">
                                                                    <i class="fas fa-users"></i> Push to Users
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Art Time Card -->
                                                <div class="col-md-6 col-xl-3">
                                                    <div class="card bg-warning text-white h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h5 class="card-title mb-0">Art Time</h5>
                                                                <i class="fas fa-palette fa-2x"></i>
                                                            </div>
                                                            <p class="card-text">Active: <span id="artActive">12</span></p>
                                                            <p class="card-text">Total: <span id="artTotal">18</span></p>
                                                            <div class="btn-group w-100">
                                                                <button class="btn btn-light btn-sm" onclick="showUploadModal('art')">
                                                                    <i class="fas fa-upload"></i> Upload
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="toggleActive('art')">
                                                                    <i class="fas fa-power-off"></i> Toggle Active
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="pushToUsers('art')">
                                                                    <i class="fas fa-users"></i> Push to Users
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Math Challenge Card -->
                                                <div class="col-md-6 col-xl-3">
                                                    <div class="card bg-info text-white h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h5 class="card-title mb-0">Math Challenge</h5>
                                                                <i class="fas fa-calculator fa-2x"></i>
                                                            </div>
                                                            <p class="card-text">Active: <span id="mathActive">18</span></p>
                                                            <p class="card-text">Total: <span id="mathTotal">22</span></p>
                                                            <div class="btn-group w-100">
                                                                <button class="btn btn-light btn-sm" onclick="showUploadModal('math')">
                                                                    <i class="fas fa-upload"></i> Upload
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="toggleActive('math')">
                                                                    <i class="fas fa-power-off"></i> Toggle Active
                                                                </button>
                                                                <button class="btn btn-light btn-sm" onclick="pushToUsers('math')">
                                                                    <i class="fas fa-users"></i> Push to Users
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Activity List Table -->
                                            <div class="card mt-4">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0">Activity List</h5>
                                                    <button class="btn btn-primary btn-sm" onclick="showAddActivityModal()">
                                                        <i class="fas fa-plus"></i> Add New Activity
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Title</th>
                                                                    <th>Type</th>
                                                                    <th>Status</th>
                                                                    <th>Last Updated</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="activityList">
                                                                <!-- Activity list will be populated dynamically -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Activity Modal -->
                            <div class="modal fade" id="uploadActivityModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Upload Activity</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="uploadActivityForm">
                                                <div class="mb-3">
                                                    <label class="form-label">Activity Title</label>
                                                    <input type="text" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Activity File</label>
                                                    <input type="file" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" rows="3"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Upload Activity</button>
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
</body>

</html>