<?php
session_start();
include 'db.php';

// Admin check
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];
    $progress_stage = $_POST['progress_stage'];

    // Update the progress stage
    $stmt = $conn->prepare("UPDATE applications SET progress_stage = ? WHERE id = ?");
    $stmt->bind_param("si", $progress_stage, $application_id);
    $stmt->execute();

    // Redirect back to the admin panel
    header("Location: admin_dashboard.php");
    exit;
}
?>
