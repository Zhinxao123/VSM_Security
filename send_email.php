<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Start the session if needed
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Create a new PHPMailer instance
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

        // Enable debugging
        $mail->SMTPDebug = 0; // Set to 0 for production

        // Recipients
        $mail->setFrom('no-reply@yourdomain.com', 'VSM Security'); 
        $mail->addAddress('justinmarcusantiag@gmail.com', 'Justin'); // Add a recipient

        // Set the Reply-To header to the sender's email address
        $mail->addReplyTo($email, $name); // This allows the recipient to reply to the sender

        // Email content
        $mail->isHTML(true); 
        $mail->Subject = 'New Message from Contact Form';
        $mail->Body    = "<strong>Name:</strong> $name<br>
                          <strong>Email:</strong> $email<br>
                          <strong>Message:</strong> $message";

        // Send the email
        $mail->send();
        echo 'Message has been sent successfully!';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo 'Invalid request method.';
}
?>
