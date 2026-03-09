<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kidstroop Admin Dashboard</title>
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
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            transition: width 0.3s;
            position: fixed;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar .toggle-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .sidebar .section {
            width: 100%;
            padding: 10px;
            text-align: center;
            cursor: pointer;
        }

        .sidebar .section:hover {
            background: #ff7f7f;
        }

        .sidebar svg {
            width: 30px;
            height: 30px;
        }

        .sidebar span {
            display: inline-block;
            margin-left: 10px;
        }

        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 70px;
        }

        .card-custom {
            border-radius: 15px;
            border: none;
            transition: transform 0.3s;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .navbar-custom {
            background-color: var(--primary-color);
            padding: 15px 0;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
            color: white;
        }

        .icon-users { background-color: var(--primary-color); }
        .icon-activity { background-color: var(--secondary-color); }
        .icon-settings { background-color: var(--accent-color); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
        <div class="section">
            <svg><!-- SVG for Manage Users --></svg>
            <span>Manage Users</span>
        </div>
        <div class="section">
            <svg><!-- SVG for Daily Activities --></svg>
            <span>Daily Activities</span>
        </div>
    </div>

    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container">
                <a class="navbar-brand fs-3" href="#"><img src="/logo.png" alt="Logo" style="height: 70px;"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#"><i class="fas fa-user-cog"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fas fa-users"></i> Manage Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fas fa-cogs"></i> Settings</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container py-4">
            <h2 class="mb-4">📊 Admin Dashboard</h2>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card-custom card mb-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle icon-users">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Total Users</h5>
                                <p class="card-text text-muted mb-0">1,235</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card-custom card mb-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle icon-activity">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Active Activities</h5>
                                <p class="card-text text-muted mb-0">47</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card-custom card mb-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle icon-settings">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Settings</h5>
                                <p class="card-text text-muted mb-0">Manage</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <h2 class="mb-4">👥 Manage Users</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">User Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>John Doe</td>
                        <td>john@example.com</td>
                        <td>User</td>
                        <td>
                            <button class="btn btn-secondary btn-sm">Edit</button>
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                    <!-- Add more user rows as needed -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
    </script>
</body>
</html>
