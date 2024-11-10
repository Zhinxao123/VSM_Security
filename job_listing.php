<?php
session_start();

// Include your database connection here
include 'db.php';

// Fetch only active job listings from the database
$stmt = $conn->prepare("SELECT * FROM jobs WHERE is_active = 1");
$stmt->execute();
$result = $stmt->get_result();

// Get user_id from session
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Openings</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        /* General Styling */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 40px;
            max-width: 1200px;
        }

        h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .back-btn {
            margin-bottom: 20px;
            text-align: center;
        }

        .back-btn a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border-radius: 30px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .back-btn a:hover {
            background-color: #5a6268;
            position: left;
        }

        /* Job Listings Layout */
        .job-listings {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .job-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .job-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #007bff;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .job-title:hover {
            color: #0056b3;
        }

        .job-details p {
            font-size: 1rem;
            color: #555;
            margin: 5px 0;
        }

        .job-meta {
            font-size: 1rem;
            color: #777;
            margin-top: 10px;
        }

        .btn-apply, .btn-progress {
            background-color: #007bff;
            color: #fff;
            font-size: 1.1rem;
            padding: 12px 20px;
            border-radius: 30px;
            width: 100%;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn-apply:hover, .btn-progress:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Modal Styling */
        .job-details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: opacity 0.3s ease;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close-btn {
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
            font-weight: 600;
        }

        .modal-close-btn:hover {
            color: #007bff;
        }

        .modal-header h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 2rem;
            }

            .btn-apply, .btn-progress {
                font-size: 1rem;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Back to Home Button -->
        <div class="back-btn">
            <a href="user_dashboard.php" class="btn btn-secondary">Back to Home</a>
        </div>

        <h2>Available Careers</h2>

        <!-- Job Listings -->
        <div class="job-listings">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Check if the user has already applied for the current job
                    $job_id = $row['id'];
                    $stmt_check = $conn->prepare("SELECT * FROM applications WHERE user_id = ? AND job_id = ?");
                    $stmt_check->bind_param("ii", $user_id, $job_id);
                    $stmt_check->execute();
                    $application_result = $stmt_check->get_result();
                    $application = $application_result->fetch_assoc();
                    ?>

                    <div class="job-card" onclick="showJobDetails(<?php echo htmlspecialchars(json_encode($row)); ?>, <?php echo $application_result->num_rows > 0 ? 'true' : 'false'; ?>, '<?php echo isset($application['status']) ? $application['status'] : 'pending'; ?>')">
                        <div class="job-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="job-details">
                            <p><strong>Location:</strong> <?php echo isset($row['location']) ? htmlspecialchars($row['location']) : 'Not specified'; ?></p>
                            <p><strong>Employment Type:</strong> <?php echo isset($row['employment_type']) ? htmlspecialchars($row['employment_type']) : 'Not specified'; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No job openings are currently available. Please check back later.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Job Details Modal -->
    <div class="job-details-modal" id="job-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Job Details</h4>
                <span class="modal-close-btn" onclick="closeModal()">&times;</span>
            </div>
            <div id="job-info">
                <p id="job-location"></p>
                <p id="job-type"></p>
                <p><strong>Description:</strong> <span id="job-desc"></span></p>
                <p><strong>Requirements:</strong></p>
                <ul id="job-reqs" style="padding-left: 20px;"></ul>
                <!-- Apply Now Button or Track My Progress Button -->
                <div id="job-action-buttons"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function showJobDetails(job, isApplied, status) {
            const modal = document.getElementById('job-details-modal');
            modal.style.display = 'flex';

            // Populate the job details
            document.getElementById('job-location').innerHTML = `<strong>Location:</strong> ${job.location || 'Not specified'}`;
            document.getElementById('job-type').innerHTML = `<strong>Employment Type:</strong> ${job.employment_type || 'Full-time'}`;
            document.getElementById('job-desc').textContent = job.description;

            // Create bullet points for requirements
            const requirements = job.requirements ? job.requirements.split(',') : [];
            const reqList = document.getElementById('job-reqs');
            reqList.innerHTML = '';  // Clear previous requirements
            requirements.forEach(req => {
                const li = document.createElement('li');
                li.textContent = req.trim();
                reqList.appendChild(li);
            });

            const actionButtons = document.getElementById('job-action-buttons');
            
            if (isApplied) {
                if (status === 'declined') {
                    actionButtons.innerHTML = `<a href="apply.php?job_id=${job.id}" class="btn-apply">Apply Now</a>`;
                } else if (status === 'accepted') {
                    actionButtons.innerHTML = `<a href="progress.php?job_id=${job.id}" class="btn-progress">Track My Progress</a>`;
                } else {
                    actionButtons.innerHTML = `<a href="progress.php?job_id=${job.id}" class="btn-progress">Track My Progress</a>`;
                }
            } else {
                actionButtons.innerHTML = `<a href="apply.php?job_id=${job.id}" class="btn-apply">Apply Now</a>`;
            }
        }

        function closeModal() {
            document.getElementById('job-details-modal').style.display = 'none';
        }
    </script>

</body>
</html>
