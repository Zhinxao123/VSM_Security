<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vsmsec');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); // Hash the password
    $mobile = trim($_POST['mobile']); // Ensure this matches your database column name

    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($mobile)) {
        echo "<script>
                alert('Error: All fields are required.');
                window.location.href = 'register.php';
              </script>";
        exit();
    }

    // Check if the email already exists
    $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        // Email already exists, show an error message
        echo "<script>
                alert('Error: This email is already registered.');
                window.location.href = 'register.php';
              </script>";
    } else {
        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, mobile_number) VALUES (?, ?, ?, ?)"); // Ensure mobile_number matches your table schema
        $stmt->bind_param("ssss", $name, $email, $password, $mobile);

        if ($stmt->execute()) {
            // Registration successful, redirect to login.html after user clicks OK
            echo "<script>
                    alert('Registration successful!');
                    window.location.href = 'login.html';
                  </script>";
        } else {
            // Handle SQL execution error
            echo "<script>
                    alert('Error: Something went wrong during registration. Please try again.');
                    window.location.href = 'register.php';
                  </script>";
        }

        $stmt->close();
    }

    $check_email->close();
}

$conn->close();
?>
