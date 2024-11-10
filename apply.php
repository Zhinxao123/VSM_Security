<?php
session_start();
include 'db.php';

// Check if job id is passed
if (!isset($_GET['job_id'])) {
    die("Job ID not specified");
}

$job_id = $_GET['job_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resume = $_FILES['resume'];
    $user_id = $_SESSION['user_id']; // Assuming user is logged in

    // Check if resume is uploaded
    if ($resume['error'] == UPLOAD_ERR_OK) {
        $resume_tmp_name = $resume['tmp_name'];
        $resume_name = $resume['name'];
        $resume_path = 'uploads/' . $resume_name;

        // Move the uploaded resume to the 'uploads' folder
        if (move_uploaded_file($resume_tmp_name, $resume_path)) {
            // Insert the application with resume path and initial progress state
            $stmt = $conn->prepare("INSERT INTO applications (user_id, job_id, resume, status) VALUES (?, ?, ?, ?)");
            $status = 'Resume Review'; // Initial status
            $stmt->bind_param("iiss", $user_id, $job_id, $resume_path, $status);
            if ($stmt->execute()) {
                // Redirect to progress page after successful submission
                header("Location: progress.php?job_id=$job_id");
                exit();
            } else {
                $error_message = "Error submitting your application.";
            }
        } else {
            $error_message = "Error uploading resume.";
        }
    } else {
        $error_message = "Please upload a resume.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .custom-file-input {
            display: none;
        }
        .custom-file-label {
            display: block;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .custom-file-label:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Apply for the Job</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="resume" class="form-label">Upload Your Resume</label>
                        <div class="input-group">
                            <input type="file" class="custom-file-input" id="resume" name="resume" required>
                            <label class="custom-file-label" for="resume">Choose file...</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="applyButton">Apply Now</button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="job_listing.php" class="btn btn-secondary">Back to Job Listings</a>
        </div>
    </div>

    <script>
        // Custom file input label change on file select
        document.getElementById('resume').addEventListener('change', function (event) {
            const fileName = event.target.files[0] ? event.target.files[0].name : 'Choose file...';
            event.target.nextElementSibling.innerHTML = fileName;
        });

        // Form submission loading state
        const applyButton = document.getElementById('applyButton');
        const form = document.querySelector('form');
        
        form.addEventListener('submit', function () {
            applyButton.innerHTML = 'Applying... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            applyButton.setAttribute('disabled', true);
        });
    </script>
</body>
</html>
