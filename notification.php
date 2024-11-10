<?php
session_start();

// Mock notifications (In a real-world scenario, these would come from a database)
$notifications = [
    ["message" => "Your job application has been received.", "time" => "2 minutes ago"],
    ["message" => "New job openings available!", "time" => "5 minutes ago"],
    ["message" => "You have a new message from HR.", "time" => "10 minutes ago"],
];

// Store notifications in the session (this is just for demonstration)
$_SESSION['notifications'] = $notifications;

header('Content-Type: application/json');
echo json_encode($notifications);
?>
