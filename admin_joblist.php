<?php
session_start();

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php'; // Include your database connection here

// Fetch job listings from the database
$stmt = $conn->prepare("SELECT * FROM jobs");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f7f7f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        header {
            background-color: #007bff;
            padding: 20px 0;
            color: white;
            text-align: center;
            border-bottom: 1px solid #e5e5e5;
        }

        .job-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background-color: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%; /* Ensure all cards have equal height */
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-body h5 {
            font-size: 1.25rem;
            color: #333;
        }

        .card-body p {
            font-size: 1rem;
            color: #555;
            flex-grow: 1; /* Allow the description to grow */
        }

        .status-active {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .status-disabled {
            background-color: #ffc107;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 8px 16px;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .btn-container {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: flex-start;
        }

        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 40px;
        }

        /* For mobile responsiveness */
        @media (max-width: 768px) {
            .job-card {
                margin-bottom: 10px;
            }
        }
        
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <h1>Job Listings</h1>
        <p>Manage the job listings for your organization</p>
    </header>

    <!-- Back to Dashboard Button -->
    <div class="back-btn">
        <a href="admin_dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card job-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="card-text"><strong>Requirements:</strong> <?php echo htmlspecialchars($row['requirements']); ?></p>
                                <!-- Add Location and Employment Type -->
                                <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']) ?: 'Not specified'; ?></p>
                                <p class="card-text"><strong>Employment Type:</strong> <?php echo htmlspecialchars($row['employment_type']) ?: 'Full-time'; ?></p>
                                <p class="card-text"><strong>Status:</strong>
                                    <span class="badge <?php echo $row['is_active'] ? 'status-active' : 'status-disabled'; ?>">
                                        <?php echo $row['is_active'] ? 'Active' : 'Disabled'; ?>
                                    </span>
                                </p>

                                <!-- Action Buttons -->
                                <div class="btn-container">
                                    <a href="edit_job.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-custom">Edit</a>
                                    <?php if ($row['is_active']): ?>
                                        <a href="disable_job.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-custom">Disable</a>
                                    <?php else: ?>
                                        <a href="enable_job.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-custom">Enable</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No job listings available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> VSM SECURITY. All rights reserved.</p>
    </footer>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
