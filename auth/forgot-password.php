<?php
session_start();
include('../config/db.php');
include('..\includes/header.php');
include('email-template.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is included

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token valid for 1 hour

            // Store token in the database
            $update_stmt = $pdo->prepare("UPDATE students SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update_stmt->execute([$token, $expiry, $email]);

            // Send reset email using PHPMailer
            if (sendPasswordResetEmail($email, $token)) {
                $message = "<div class='success'>A reset link has been sent to your email.</div>";
            } else {
                $message = "<div class='error'>Error sending email. Please try again.</div>";
            }
        } else {
            $message = "<div class='error'>Email not found.</div>";
        }
    } else {
        $message = "<div class='error'>Please enter a valid email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Attendance System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 90%; max-width: 500px; margin: 50px auto; background: white; padding: 20px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); border-radius: 5px; text-align: center; }
        h2 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { display: block; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border: none; cursor: pointer; border-radius: 5px; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .success, .error { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background: #28a745; color: white; }
        .error { background: #dc3545; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Forgot Password</h2>

    <?= $message; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Enter Your Email:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <button type="submit" class="btn">Send Reset Link</button>
    </form>

    <br>
    <a href="login.php" class="btn">Back to Login</a>
</div>

</body>
</html>
