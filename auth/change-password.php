<?php
session_start();
include('../config/db.php');
include('..\includes/header.php');


// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        $message = "<div class='error'>New passwords do not match.</div>";
    } else {
        // Fetch current hashed password from database
        $stmt = $pdo->prepare("SELECT password FROM student WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Verify old password
        if (password_verify($old_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password in database
            $update_stmt = $pdo->prepare("UPDATE student SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$hashed_password, $user_id])) {
                $message = "<div class='success'>Password updated successfully!</div>";
            } else {
                $message = "<div class='error'>Error updating password. Please try again.</div>";
            }
        } else {
            $message = "<div class='error'>Incorrect old password.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Attendance System</title>
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
    <h2>Change Password</h2>

    <?= $message; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="old_password">Old Password:</label>
            <input type="password" name="old_password" id="old_password" required>
        </div>

        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button type="submit" class="btn">Update Password</button>
    </form>

    <br>
    <a href="profile.php" class="btn">Back to Profile</a>
</div>

</body>
</html>
