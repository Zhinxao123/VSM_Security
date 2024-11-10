<?php
session_start();
include 'db.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch applications with user and job details, including resume
$stmt = $conn->prepare("SELECT applications.id AS application_id, users.name AS user_name, users.email, jobs.title AS job_title, 
                       applications.status, applications.resume, applications.announcement, applications.announcement_date, applications.hired_date
                       FROM applications
                       JOIN users ON applications.user_id = users.id
                       JOIN jobs ON applications.job_id = jobs.id");
$stmt->execute();
$result = $stmt->get_result();

// Status progression map
$status_map = [
    "Resume Review" => "Exam",
    "Exam" => "Interview",
    "Interview" => "Final Exam",
    "Final Exam" => "Final Interview",
    "Final Interview" => "Accepted",
    "Accepted" => "Hired"
];

// Function to hire an applicant
function hireApplicant($application_id) {
    global $conn;

    // Set status to 'Hired' and record hiring date
    $hired_date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE applications SET status = 'Hired', hired_date = ? WHERE id = ?");
    $stmt->bind_param("si", $hired_date, $application_id);
    $stmt->execute();

    // Fetch applicant's details
    $stmt = $conn->prepare("SELECT users.name, users.email, jobs.title, applications.status, applications.resume, applications.announcement, applications.announcement_date
                            FROM applications
                            JOIN users ON applications.user_id = users.id
                            JOIN jobs ON applications.job_id = jobs.id
                            WHERE applications.id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $applicant = $result->fetch_assoc();

    if ($applicant) {
        // Insert into reports table with "Hired" status
        $stmt = $conn->prepare("INSERT INTO reports (name, email, job_title, status, resume, announcement, announcement_date) 
                                VALUES (?, ?, ?, 'Hired', ?, ?, ?)");
        $stmt->bind_param("ssssss", $applicant['name'], $applicant['email'], $applicant['title'], $applicant['resume'], 
                          $applicant['announcement'], $applicant['announcement_date']);
        $stmt->execute();
    }

    // Delete from applications table
    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
}

// Function to decline an applicant
function declineApplicant($application_id) {
    global $conn;

    // Fetch applicant's details
    $stmt = $conn->prepare("SELECT users.name, users.email, jobs.title, applications.status, applications.resume, applications.announcement, applications.announcement_date
                            FROM applications
                            JOIN users ON applications.user_id = users.id
                            JOIN jobs ON applications.job_id = jobs.id
                            WHERE applications.id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $applicant = $result->fetch_assoc();

    if ($applicant) {
        // Insert into reports table with "Declined" status
        $stmt = $conn->prepare("INSERT INTO reports (name, email, job_title, status, resume, announcement, announcement_date) 
                                VALUES (?, ?, ?, 'Declined', ?, ?, ?)");
        $stmt->bind_param("ssssss", $applicant['name'], $applicant['email'], $applicant['title'], $applicant['resume'], 
                          $applicant['announcement'], $applicant['announcement_date']);
        $stmt->execute();
        
        // Set the status to 'Declined' before deleting
        $stmt = $conn->prepare("UPDATE applications SET status = 'Declined' WHERE id = ?");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
    }

    // Delete from applications table
    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
}

// Handle status update (Move to Next Phase)
if (isset($_POST['move_to_next']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $current_status = $_POST['current_status'];
    
    if (isset($status_map[$current_status])) {
        $new_status = $status_map[$current_status];
        
        // Update the status to the next phase
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $application_id);
        $stmt->execute();
    }
}

// Handle hire action
if (isset($_POST['hire']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    hireApplicant($application_id);
}

// Handle decline action
if (isset($_POST['decline']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    declineApplicant($application_id);
}

// Handle announcement update
if (isset($_POST['update_announcement']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $announcement = $_POST['announcement'];
    $announcement_date = $_POST['announcement_date'];

    // Validate input
    if (!empty($announcement) && !empty($announcement_date)) {
        // Update announcement and announcement date in the applications table
        $stmt = $conn->prepare("UPDATE applications SET announcement = ?, announcement_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $announcement, $announcement_date, $application_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Announcement updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update announcement.";
        }
    } else {
        $_SESSION['error_message'] = "Announcement and date cannot be empty.";
    }
    header("Location: admin_manage.php"); // Redirect to the same page to avoid form resubmission
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
        }
        .card {
            margin-bottom: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-lg {
            padding: 12px 24px;
            font-size: 16px;
        }
        .btn-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">Manage Applications</h2>

    <!-- Display Success Message if Announcement is Updated -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Display Error Message if Announcement is Empty -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-warning">
            <?php echo $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Job Title</th>
                                <th>Current Status</th>
                                <th>Resume</th>
                                <th>Announcement</th>
                                <th>Announcement Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($application = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($application['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($application['email']); ?></td>
                                    <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($application['status']); ?></td>
                                    <td>
                                        <?php if ($application['resume']): ?>
                                            <a href="<?php echo htmlspecialchars($application['resume']); ?>" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-file-pdf btn-icon"></i> View Resume
                                            </a>
                                        <?php else: ?>
                                            No Resume
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="admin_manage.php" class="d-inline-block">
                                            <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                            <input type="text" name="announcement" value="<?php echo htmlspecialchars($application['announcement']); ?>" class="form-control mb-2" placeholder="Update Announcement">
                                            <input type="date" name="announcement_date" value="<?php echo htmlspecialchars($application['announcement_date']); ?>" class="form-control mb-2">
                                            <button type="submit" name="update_announcement" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit btn-icon"></i> Update Announcement
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($application['announcement_date']) ?: 'Not available'; ?>
                                    </td>
                                    <td>
                                        <?php if ($application['status'] === "100%" || $application['status'] === "Accepted"): ?>
                                            <form method="POST" action="admin_manage.php" class="d-inline-block">
                                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                                <button type="submit" name="hire" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check-circle btn-icon"></i> Hire
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="admin_manage.php" class="d-inline-block">
                                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $application['status']; ?>">
                                                <button type="submit" name="move_to_next" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-arrow-right btn-icon"></i> Move to Next Phase
                                                </button>
                                            </form>
                                            <form method="POST" action="admin_manage.php" class="d-inline-block">
                                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                                <button type="submit" name="decline" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times-circle btn-icon"></i> Decline
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <a href="admin_dashboard.php" class="btn btn-secondary btn-lg mt-3">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>








