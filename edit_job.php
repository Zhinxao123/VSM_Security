<?php
session_start();

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch job data
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $location = trim($_POST['location']);
    $employment_type = trim($_POST['employment_type']);

    // Update job in the database
    $stmt = $conn->prepare("UPDATE jobs SET title = ?, description = ?, requirements = ?, location = ?, employment_type = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $title, $description, $requirements, $location, $employment_type, $id);
    
    if ($stmt->execute()) {
        // After successful update, redirect to the job list page (admin_joblist.php)
        header("Location: admin_joblist.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-4">
        <h2>Edit Job</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Job Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Job Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($job['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="requirements">Requirements</label>
                <textarea class="form-control" id="requirements" name="requirements" rows="3" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
            </div>
            <!-- New Location Field -->
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
            </div>
            <!-- New Employment Type Field -->
            <div class="form-group">
                <label for="employment_type">Employment Type</label>
                <select class="form-control" id="employment_type" name="employment_type" required>
                    <option value="Full-time" <?php echo ($job['employment_type'] == 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                    <option value="Part-time" <?php echo ($job['employment_type'] == 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                    <option value="Contract" <?php echo ($job['employment_type'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                    <option value="Internship" <?php echo ($job['employment_type'] == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                    <option value="Temporary" <?php echo ($job['employment_type'] == 'Temporary') ? 'selected' : ''; ?>>Temporary</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Job</button>
            <a href="admin_joblist.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
