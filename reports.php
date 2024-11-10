<?php
session_start();
include 'db.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch reports for hired and declined applicants
$stmt = $conn->prepare("SELECT * FROM reports WHERE status IN ('Hired', 'Declined')");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Hired and Declined Applicants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
        }
        .card {
            margin-bottom: 30px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="text-center mb-4">Reports - Hired and Declined Applicants</h2>
            
            <!-- Table for displaying reports -->
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Resume</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($applicant = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['status']); ?></td>
                            <td>
                                <?php if ($applicant['resume']): ?>
                                    <a href="<?php echo htmlspecialchars($applicant['resume']); ?>" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-file-pdf btn-icon"></i> View Resume
                                    </a>
                                <?php else: ?>
                                    No Resume
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <a href="admin_dashboard.php" class="btn btn-secondary btn-lg mt-4">Back to Dashboard</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
