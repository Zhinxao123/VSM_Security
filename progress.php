    <?php
    session_start();
    include 'db.php';

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in");
    }

    // Check if job_id is set and is a valid integer
    if (!isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) {
        die("Invalid job ID");
    }

    $job_id = $_GET['job_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT announcement, announcement_date, status FROM applications WHERE user_id = ? AND job_id = ?");
    $stmt->bind_param("ii", $user_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();
        $status = $application['status'];
        $announcement = $application['announcement'];  // Retrieve the announcement
        $announcement_date = $application['announcement_date']; // Retrieve the announcement date

        // Define progress percentages for each status
        $progress_map = [
            'Resume Review' => 20,
            'Exam' => 40,
            'Interview' => 60,
            'Final Exam' => 80,
            'Final Interview' => 90,
            'Accepted' => 100,
            'Hired' => 100, // Added Hired status to have 100% progress
            'Declined' => 0 // Added Declined status to have 0% progress
        ];

        $progress = $progress_map[$status] ?? 0;

        // Check if the application is in the next phase and set notification flag
        $next_phase = false;
        if ($status == 'Resume Review') {
            $_SESSION['has_notification'] = true;
            $next_phase = true;
        }
    } else {
        // If application not found, we need to check if the user is hired or declined already
        $stmt = $conn->prepare("SELECT * FROM reports WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ii", $user_id, $job_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            $status = $report['status'];
            $message = ($status === 'Hired') ? 'Congratulations! You\'ve been hired!' : 'Sorry, you have been declined.';
        } else {
            // No application or report found
            $message = "No application found for this job.";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Application Progress</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .notification-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                width: 12px;
                height: 12px;
                background-color: red;
                color: white;
                font-size: 10px;
                display: none;
                border-radius: 50%;
                text-align: center;
                line-height: 12px;
            }
        </style>
    </head>
    <body>

    <!-- Navigation Bar with Notification Badge -->
    <header>
        <nav>
            <!-- Include the notification icon in your header -->
            <div class="logo-notification-container">
                <div class="notification-logo" onclick="toggleNotificationDetails()"></div>
                <div class="notification-badge" id="notification-badge">!</div>
            </div>
        </nav>
    </header>

    <div class="container my-4">
        <h2>Application Progress</h2>

        <?php if (isset($message)): ?>
            <!-- Display the status message (Hired, Declined, or No Application Found) -->
            <div class="alert alert-info mt-3">
                <strong><?php echo htmlspecialchars($message); ?></strong>
            </div>
            <!-- Button to redirect to the user dashboard -->
            <a href="user_dashboard.php" class="btn btn-primary mt-3">Go to Dashboard</a>
        <?php else: ?>
            <!-- Progress Bar Section -->
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Application progress">
                    <?php echo $progress; ?>%
                </div>
            </div>

            <p class="mt-3">Current Status: <?php echo htmlspecialchars($status); ?></p>

            <?php if ($status === 'Hired'): ?>
                <div class="alert alert-success mt-3">
                    <strong>Congratulations!</strong> You've been hired! Welcome to the team.
                </div>
                <a href="user_dashboard.php" class="btn btn-primary mt-3">Go to Dashboard</a>
            <?php else: ?>
                <?php if (!empty($announcement)): ?>
                    <div class="alert alert-info mt-3">
                        <strong>Announcement:</strong> <?php echo nl2br(htmlspecialchars($announcement));  ?>
                    </div>
                    <?php if (!empty($announcement_date)): ?>
                        <div class="alert alert-info mt-2">
                            <strong>Announcement Date:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($announcement_date))); ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">
                        No announcement available for this application.
                    </div>
                <?php endif; ?>
                <a href="job_listing.php" class="btn btn-secondary mt-3">Go to Job Listing</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBadge = document.getElementById('notification-badge');
            
            // Check if the user has a notification
            <?php if(isset($_SESSION['has_notification']) && $_SESSION['has_notification'] == true): ?>
                notificationBadge.style.display = 'block'; // Show the notification badge
                <?php unset($_SESSION['has_notification']); ?> // Reset the session notification flag
            <?php endif; ?>
        });

        function toggleNotificationDetails() {
            // Placeholder action for notification click
            alert('You have a new notification!');  // Replace with actual redirection or modal for details
        }
    </script>

    </body>
    </html>
