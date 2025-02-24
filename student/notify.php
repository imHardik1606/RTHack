<?php
session_start();
include('../config/db.php'); // Database connection
include('../includes/header.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch student details to get session_id, course_id, and year_id
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT session_id, course_id, year_id FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$session_id = $student['session_id'];
$course_id = $student['course_id'];
$year_id = $student['year_id'];

// Fetch notifications relevant to the student
$notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE session_id = ? AND course_id = ? AND year_id = ? ORDER BY created_at DESC");
$notif_stmt->execute([$session_id, $course_id, $year_id]);
$notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* margin: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h3 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        .notification-list {
            list-style: none;
            padding: 0;
        }
        .notification-list li {
            background: #e9ecef;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .notification-list a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .notification-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body><br><br>
    <div class="container">
        <h3>Notifications</h3>
        <ul class="notification-list">
            <?php if (count($notifications) > 0) { ?>
                <?php foreach ($notifications as $notification) { ?>
                    <li>
                        <a href="view_notification.php?id=<?= $notification['id']; ?>">
                            <?= htmlspecialchars($notification['title']); ?>
                        </a>
                        <p><small><?= date("d M Y, H:i", strtotime($notification['created_at'])); ?></small></p>
                    </li>
                <?php } ?>
            <?php } else { ?>
                <p>No notifications available.</p>
            <?php } ?>
        </ul>
    </div>
</body>
</html>
