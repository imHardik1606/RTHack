<?php
session_start();
include('../config/db.php'); // Database connection
include('../includes/header.php');

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $session_id = $_POST['session_id'];

    if (!empty($title) && !empty($message) && !empty($course_id) && !empty($year_id) && !empty($session_id)) {
        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, course_id, year_id, session_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $message, $course_id, $year_id, $session_id])) {
            $success_message = "Notification sent successfully!";
        } else {
            $error_message = "Error sending notification. Please try again.";
        }
    } else {
        $error_message = "All fields are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        h2 {
            text-align: center;
            color: #007bff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
        }
        select, input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 10px;
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success, .error {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
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
    <h2>Send Notification</h2>
    
    <?php if ($success_message) echo "<div class='success'>$success_message</div>"; ?>
    <?php if ($error_message) echo "<div class='error'>$error_message</div>"; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required>
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="course_id">Course:</label>
            <select name="course_id" id="course_id" required>
                <option value="1">BCA</option>
                <!-- Add more courses if needed -->
            </select>
        </div>
        <div class="form-group">
            <label for="year_id">Year:</label>
            <select name="year_id" id="year_id" required>
                <?php for ($i = 1; $i <= 8; $i++) echo "<option value='$i'>Semester $i</option>"; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="session_id">Session:</label>
            <select name="session_id" id="session_id" required>
                <option value="1">Batch 2022-2025</option>
                <!-- Add more sessions if needed -->
            </select>
        </div>
        <button type="submit" class="btn">Send Notification</button>
    </form>
</div>

</body>
</html>
