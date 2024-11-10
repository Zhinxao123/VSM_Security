<?php
// Include your database connection
include 'db.php';

// Define admin account details
$admin_name = 'justin marcus'; 
$admin_email = 'justinmarcusantiag@gmail.com'; // Change to desired email
$admin_password = 'zhinxao123'; // Change to desired password

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if the admin account already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Insert new admin account
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $admin_name, $admin_email, $hashed_password);

    if ($stmt->execute()) {
        echo "Admin account created successfully.";
    } else {
        echo "Error creating admin account: " . $stmt->error;
    }
} else {
    echo "Admin account already exists.";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
