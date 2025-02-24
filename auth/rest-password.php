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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .success, .error {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .success {
            background: #28a745;
            color: white;
        }

        .error {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <?= $message; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button type="submit" class="btn">Reset Password</button>
    </form>
</div>

</body>
</html>
