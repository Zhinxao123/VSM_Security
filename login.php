<?php
// Place the use statements before any code
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start the session and include the database connection
session_start();
include 'db.php'; // Include your database connection here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.location.href = 'login.php';</script>";
        exit();
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Generate a random OTP
            $otp = rand(100000, 999999);

            // Store OTP in the database (or you can store it in the session)
            $stmt = $conn->prepare("INSERT INTO otp (user_id, otp_code) VALUES (?, ?)");
            $stmt->bind_param("is", $user['id'], $otp);
            $stmt->execute();

            // Send OTP to user's email using PHPMailer
            require 'vendor/autoload.php'; // Load Composer's autoloader

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'justinmarcusantiag@gmail.com'; // Your Gmail address
            $mail->Password   = 'okcq ispw btis airu'; // Your Gmail password or App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
            $mail->Port       = 587; // TCP port for TLS       

                // Recipients
                $mail->setFrom('no-reply@yourdomain.com', 'VSM Security');
                $mail->addAddress($email, $user['name']); // Add a recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = "Your OTP Code";
                $mail->Body    = "Your OTP code is: <strong>$otp</strong>";

                // Send email
                $mail->send();

                // Store user ID and role in session, then redirect to OTP verification page
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role']; // Store user role in session
                header("Location: verify_otp.php");
                exit();

            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href = 'login.html';</script>";
        }
    } else {
        echo "<script>alert('No user found with that email.'); window.location.href = 'login.html';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
