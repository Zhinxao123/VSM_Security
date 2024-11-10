<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'];

// Fetch the application progress
$stmt = $conn->prepare("SELECT progress_stage FROM applications WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ii", $user_id, $job_id);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

// Define stages
$stages = ['submitted', 'exam', 'interview', 'final_interview', 'completed'];
$current_stage = array_search($application['progress_stage'], $stages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Application Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Application Progress</h2>
        <div class="progress">
            <?php foreach ($stages as $index => $stage): ?>
                <div class="progress-bar <?= $index <= $current_stage ? 'bg-success' : 'bg-light' ?>" 
                     style="width: 20%;">
                    <?= ucfirst($stage) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="track_progress.php?job_id=<?php echo $job_id; ?>" class="btn btn-primary mt-3">Refresh</a>
    </div>
</body>
</html>
