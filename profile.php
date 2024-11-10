<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'vsm_security');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database of user
$user_id = $_SESSION['user_id'];
$query = "SELECT name, email, mobile, skills, experience, education, awards, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

if ($stmt->error) {
    die("Execution failed: " . $stmt->error);
}

$stmt->bind_result($user_name, $user_email, $user_mobile, $user_skills, $user_experience, $user_education, $user_awards, $profile_image);
$stmt->fetch();
$stmt->close();

// Check if user exists
if (!$user_name) {
    // User not found
    header("Location: login.html");
    exit();
}

// JSON files
$user_skills = json_decode($user_skills, true) ?: [];
$user_experience = json_decode($user_experience, true) ?: [];
$user_education = json_decode($user_education, true) ?: [];
$user_awards = json_decode($user_awards, true) ?: [];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - VSM Security</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="resume-container">
        <header>
            <img src="<?php echo htmlspecialchars($profile_image ?: 'path/to/default_image.jpg'); ?>" alt="Profile Image" class="profile-image">
            <h1><?php echo htmlspecialchars($user_name); ?></h1>
            <p><?php echo htmlspecialchars($user_email); ?></p>
            <p><?php echo htmlspecialchars($user_mobile ?: 'Not Provided'); ?></p>
            <a href="updateprofile.php" class="edit-button">Edit Profile</a>
            <a href="dashboard.php" class="dashboard-button">Go to Dashboard</a>
        </header>
        
        <section class="skills">
            <h2>Skills</h2>
            <ul>
                <?php foreach ($user_skills as $skill): ?>
                    <li><?php echo htmlspecialchars($skill); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="experience">
            <h2>Experience</h2>
            <ul>
                <?php foreach ($user_experience as $job): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($job['job_title']); ?></strong> at <?php echo htmlspecialchars($job['company']); ?> (<?php echo htmlspecialchars($job['years']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="education">
            <h2>Education</h2>
            <ul>
                <?php foreach ($user_education as $edu): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($edu['degree']); ?></strong> from <?php echo htmlspecialchars($edu['institution']); ?> (<?php echo htmlspecialchars($edu['years']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="awards">
            <h2>Awards</h2>
            <ul>
                <?php foreach ($user_awards as $award): ?>
                    <li><?php echo htmlspecialchars($award); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</body>
</html>
