<?php
session_start(); // Start the session

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.html");
    exit();
}

// Include your database connection here if you need to fetch data
include 'db.php'; // Include your database connection here
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        header {
            background: linear-gradient(45deg, #007bff, #0056b3);
            padding: 40px 0;
            color: white;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        header p {
            font-size: 1.2rem;
            font-weight: 300;
        }

        footer {
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .navbar {
            background-color: #007bff;
        }

        .navbar a {
            color: white;
        }

        .navbar .nav-link:hover {
            background-color: #0056b3;
        }

        .dashboard-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.6rem;
            font-weight: bold;
        }

        .card-description {
            font-size: 1rem;
            color: #6c757d;
        }

        .btn-custom {
            background-color: #28a745;
            color: white;
            border-radius: 30px;
            font-size: 1rem;
            padding: 12px 25px;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #218838;
        }

        .dashboard-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .dashboard-col {
            flex: 1;
            min-width: 280px;
            max-width: 350px;
        }

        .navbar-toggler-icon {
            color: white;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .dashboard-row {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="text-center">
        <h1>Admin Dashboard</h1>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! You are logged in as an admin.</p>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_joblist.php">Job Listings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">View Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="dashboard-row">
            <div class="dashboard-col">
                <div class="dashboard-card">
                    <h5 class="card-title">Manage Users</h5>
                    <p class="card-description">View and manage all the users who have access to the system.</p>
                    <a href="admin_manage.php" class="btn btn-custom">Manage Users</a>
                </div>
            </div>
            <div class="dashboard-col">
                <div class="dashboard-card">
                    <h5 class="card-title">Manage Job Listings</h5>
                    <p class="card-description">Add, update, or disable job listings for your organization.</p>
                    <a href="admin_joblist.php" class="btn btn-custom">Manage Jobs</a>
                </div>
            </div>
            <div class="dashboard-col">
                <div class="dashboard-card">
                    <h5 class="card-title">View Reports</h5>
                    <p class="card-description">Access various reports to monitor the performance of the system.</p>
                    <a href="reports.php" class="btn btn-custom">View Reports</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <p>&copy; <?php echo date("Y"); ?> VSM SECURITY. All rights reserved.</p>
    </footer>

    <!-- Bootstrap 5 and Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
