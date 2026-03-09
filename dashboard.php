<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa; /* Light gray background */
        }

        .sidebar {
            background-color: #fff;
            height: 100vh;
            padding-top: 20px;
            box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-link {
            color: #333;
            padding: 10px 20px;
        }

        .nav-link:hover {
            background-color: #f0f0f0;
        }

        .main-content {
          padding: 20px;
        }
        .card{
          border:none;
          box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
        .chart-container{
          height: 300px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 sidebar">
                <a class="navbar-brand ms-3" href="#">= Loper</a>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">App Pages</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Auth</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">User</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Layouts</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Landing Page</a></li>
                    <li class="nav-item mt-auto"><a class="nav-link" href="#">Night Mode</a></li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Hi, Beni.</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                          This Week
                        </div>
                    </div>
                </div>

                <div class="row">
                  <div class="col-md-3">
                    <div class="card p-3 mb-4">
                      <h3>Teams</h3>
                      <p>8</p>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card p-3 mb-4">
                      <h3>Projects</h3>
                      <p>12</p>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card p-3 mb-4">
                      <h3>Active Tasks</h3>
                      <p>64</p>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card p-3 mb-4">
                      <h3>Ongoing Tasks</h3>
                      <p>8</p>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="card p-3 mb-4">
                      <h3>Completion Tasks</h3>
                      <div class="chart-container">
                        <canvas id="completionChart"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card p-3 mb-4">
                      <h3>Tasks Performance</h3>
                      <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const completionChartCanvas = document.getElementById('completionChart').getContext('2d');
        const completionChart = new Chart(completionChartCanvas, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                datasets: [{
                    label: 'Tasks Completed',
                    data: [120, 450, 280, 220, 320, 100],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        const performanceChartCanvas = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(performanceChartCanvas, {
          type: 'doughnut',
          data: {
            labels: ['100%', '75%', '60%'],
            datasets: [{
              data: [30, 40, 30],
              backgroundColor: ['green', 'orange', 'red'],
            }]
          },
          options:{
            responsive: true,
            maintainAspectRatio: false,
          }
        });
    </script>
</body>

</html>