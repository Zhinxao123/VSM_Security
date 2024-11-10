<?php
// Start session and include database connection
session_start();
include 'db_connect.php';

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php"); // Redirect to login if not an admin
    exit();
}

// Handle enable/disable request
if (isset($_GET['toggle_job_id'])) {
    $job_id = $_GET['toggle_job_id'];
    $stmt = $conn->prepare("UPDATE jobs SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
}

// Handle new job submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_job'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $is_active = isset($_POST['is_active']) ? 1 : 0; // 1 for active, 0 for inactive

    $stmt = $conn->prepare("INSERT INTO jobs (title, description, is_active) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $description, $is_active);
    $stmt->execute();
}

// Fetch all job listings
$stmt = $conn->prepare("SELECT id, title, description, is_active FROM jobs");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management</title>
    <style>
        /* Add some CSS similar to the main dashboard */
        .main-content { padding: 20px; }
        .job-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .job-table th, .job-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .job-table th { background-color: #333; color: #fff; }
        .toggle-button { color: #007bff; text-decoration: none; cursor: pointer; }
        .form-container { margin-bottom: 20px; }
        .form-container input, .form-container textarea { width: 100%; margin-bottom: 10px; padding: 10px; }
        .form-container button { padding: 10px 15px; }
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Job Listings Management</h1>

        <!-- Form to Add New Job -->
        <div class="form-container">
            <h2>Add New Job</h2>
            <form method="POST" action="job_management.php">
                <input type="text" name="title" placeholder="Job Title" required>
                <textarea name="description" placeholder="Job Description" rows="4" required></textarea>
                <label>
                    <input type="checkbox" name="is_active" checked>
                    Active
                </label>
                <button type="submit" name="add_job">Add Job</button>
            </form>
        </div>

        <table class="job-table">
            <tr>
                <th>Job ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($job = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['id']); ?></td>
                    <td><?php echo htmlspecialchars($job['title']); ?></td>
                    <td><?php echo htmlspecialchars($job['description']); ?></td>
                    <td><?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <a class="toggle-button" href="job_management.php?toggle_job_id=<?php echo $job['id']; ?>">
                            <?php echo $job['is_active'] ? 'Disable' : 'Enable'; ?>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
