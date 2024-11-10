<?php
session_start();
include 'db.php'; // Include your database connection here

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_otp = $_POST['otp']; // Get the OTP input from the form

    // Retrieve the stored OTP for the user
    $stmt = $conn->prepare("SELECT otp_code FROM otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($stored_otp);
    $stmt->fetch();
    $stmt->close();

    // Check if the OTP is valid
    if ($input_otp == $stored_otp) {
        // Check if user role exists in the session
        if (isset($_SESSION['user_role'])) {
            // Redirect based on the user role
            if ($_SESSION['user_role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('User role is not set. Please login again.'); window.location.href = 'login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid OTP. Please try again.'); window.location.href = 'verify_otp.php';</script>";
        exit();
    }
}
?>

<!-- OTP Verification Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            margin-bottom: 1rem;
            color: #333;
        }
        input[type="text"] {
            width: 93%;
            padding: 10px;
            margin: 0.5rem 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 1rem;
            font-size: 14px;
            color: #777;
        }
        
    </style>
</head>
<body>
    <div class="container">
  
        <h2>Verify OTP</h2>
        <form method="POST" action="verify_otp.php">
            <input type="text" name="otp" required placeholder="Enter OTP">
            <button type="submit">Verify OTP</button>
        </form>
        <div class="footer">© VSM Security</div>
    </div>
</body>
</html>
