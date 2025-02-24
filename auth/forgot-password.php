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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 items-center justify-center min-h-screen p-4">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-md text-center">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Forgot Password</h2>
        <?php if(isset($message)) { echo "<p class='text-sm text-red-500'>$message</p>"; } ?>
        <form method="POST" action="" class="space-y-4">
            <div class="text-left">
                <label for="email" class="font-medium text-gray-600">Enter Your Email:</label>
                <input type="email" name="email" id="email" required class="w-full p-2 mt-1 border rounded-lg focus:ring focus:ring-blue-300">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600 transition">Send Reset Link</button>
        </form>
        <a href="login.php" class="block mt-4 text-blue-500 hover:underline">Back to Login</a>
    </div>
</body>
</html>

