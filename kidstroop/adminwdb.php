<?php
session_start();

// Sample static data
$stats = [
    'users' => 1234,
    'activities' => 56,
    'videos' => 89,
    'achievements' => 123
];

$recentActivities = [
    ['title' => 'Daily Exercise', 'type' => 'exercise', 'status' => 'Active', 'created_at' => '2024-01-05'],
    ['title' => 'Reading Time', 'type' => 'learning', 'status' => 'Completed', 'created_at' => '2024-01-04'],
    ['title' => 'Art Project', 'type' => 'creative', 'status' => 'Pending', 'created_at' => '2024-01-03']
];

$activityStats = [
    ['date' => '2024-01-01', 'count' => 45],
    ['date' => '2024-01-02', 'count' => 52],
    ['date' => '2024-01-03', 'count' => 49],
    ['date' => '2024-01-04', 'count' => 58],
    ['date' => '2024-01-05', 'count' => 55]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kidstroop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        :root {
            --primary: #4154f1;
            --secondary: #717ff5;
            --success: #2eca6a;
            --info: #4154f1;
            --warning: #ffce3a;
            --danger: #ff4b4b;
        }

        body { background: #f6f9ff; }

        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            background: white;
            box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
            z-index: 999;
            transition: all 0.3s;
        }

        .main {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s;
        }

        .card {
            border: none;
            box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .card:hover { transform: translateY(-5px); }

        .nav-link {
            padding: 10px 15px;
            color: #012970;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        @media (max-width: 1199px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.active {
                left: 0;
            }
            .main {
                margin-left: 0;
            }
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
        }

        .table > :not(caption) > * > * {
            padding: 1rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="d-flex justify-content-between align-items-center p-3">
            <img src="/logo.png" alt="Logo" class="img-fluid">
            <button class="btn d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="nav flex-column p-3">
            <a href="#" class="nav-link active">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-users me-2"></i> Users
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-tasks me-2"></i> Activities
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-video me-2"></i> Videos
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-trophy me-2"></i> Achievements
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Dashboard</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">
                <i class="fas fa-plus me-2"></i>Add Content
            </button>
        </div>

        <!-- Stats -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-primary me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0">Total Users</p>
                                <h4><?php echo number_format($stats['users']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Similar cards for activities, videos, achievements -->
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5>Activity Overview</h5>
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5>User Distribution</h5>
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Recent Activities</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control" placeholder="Search...">
                        <select class="form-select">
                            <option>All Types</option>
                            <option>Exercise</option>
                            <option>Learning</option>
                            <option>Creative</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo $activity['title']; ?></td>
                                <td><span class="badge bg-primary"><?php echo $activity['type']; ?></span></td>
                                <td><span class="badge bg-success"><?php echo $activity['status']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Content Modal -->
    <div class="modal fade" id="addContentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="contentForm">
                        <div class="mb-3">
                            <label>Content Type</label>
                            <select class="form-select" name="type">
                                <option value="activity">Activity</option>
                                <option value="video">Video</option>
                                <option value="achievement">Achievement</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title">
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Upload File</label>
                            <input type="file" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Charts
        const activityData = <?php echo json_encode($activityStats); ?>;
        
        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: activityData.map(item => item.date),
                datasets: [{
                    label: 'Daily Activities',
                    data: activityData.map(item => item.count),
                    borderColor: '#4154f1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        new Chart(document.getElementById('userChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'New'],
                datasets: [{
                    data: [65, 20, 15],
                    backgroundColor: ['#4154f1', '#dc3545', '#ffc107']
                }]
            }
        });

        // Form handling
        document.querySelector('select[name="type"]').addEventListener('change', function(e) {
            const fileInput = document.querySelector('input[type="file"]');
            fileInput.parentElement.style.display = e.target.value === 'video' ? 'block' : 'none';
        });
    </script>
</body>
</html>