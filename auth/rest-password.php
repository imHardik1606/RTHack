<?php
session_start();
include('../config/db.php');

$message = "";

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid request!");
}

$token = $_GET['token'];

// Verify token in database
$stmt = $pdo->prepare("SELECT id, reset_expires FROM students WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token.");
}

// Check if token has expired
if (strtotime($user['reset_expires']) < time()) {
    die("Reset link expired. Request a new one.");
}

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $message = "<div class='error'>Passwords do not match.</div>";
    } else {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password and remove reset token
        $update_stmt = $pdo->prepare("UPDATE students SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        if ($update_stmt->execute([$hashed_password, $user['id']])) {
            $message = "<div class='success'>Password updated! You can now <a href='login.php'>Login</a>.</div>";
        } else {
            $message = "<div class='error'>Error updating password. Try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Reset Password</h2>
        
        <?= $message; ?>

        <form method="POST" action="" class="space-y-4">
            <div class="text-left">
                <label for="new_password" class="block text-gray-700 font-medium">New Password:</label>
                <input type="password" name="new_password" id="new_password" required class="w-full mt-2 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="text-left">
                <label for="confirm_password" class="block text-gray-700 font-medium">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required class="w-full mt-2 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">Reset Password</button>
        </form>
    </div>
</body>
</html>

