<?php
session_start();
include('../config/db.php');
include('../includes/header.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get student details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT course_id, year_id, session_id FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure notification ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: notify.php");
    exit();
}
$notification_id = $_GET['id'];

// Fetch notification details
$notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND course_id = ? AND year_id = ? AND session_id = ?");
$notif_stmt->execute([$notification_id, $user['course_id'], $user['year_id'], $user['session_id']]);
$notification = $notif_stmt->fetch(PDO::FETCH_ASSOC);

if (!$notification) {
    echo "<p style='color: red; text-align: center;'>Notification not found or not authorized.</p>";
    exit();
}

// Handle new comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $comment_stmt = $pdo->prepare("INSERT INTO notification_comments (notification_id, student_id, comment_text) VALUES (?, ?, ?)");
        $comment_stmt->execute([$notification_id, $user_id, $comment]);
    }
}

// Fetch comments
$comments_stmt = $pdo->prepare("SELECT nc.comment_text, nc.created_at, s.name FROM notification_comments nc JOIN students s ON nc.student_id = s.id WHERE nc.notification_id = ? ORDER BY nc.created_at DESC");
$comments_stmt->execute([$notification_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            /* padding: 20px; */
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            text-align: center;
        }
        .message {
            margin-top: 10px;
            padding: 15px;
            border-left: 4px solid #007bff;
            background: #f9f9f9;
        }
        .date {
            font-size: 14px;
            color: #555;
            text-align: right;
        }
        .back-btn {
            display: block;
            margin: 20px auto;
            text-align: center;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #0056b3;
        }
        .comment-section {
            margin-top: 20px;
        }
        .comment-box {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .comment-list {
            margin-top: 10px;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 5px;
        }
        .comment {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment strong {
            color: #333;
        }
    </style>
</head>
<body><br><br>
    <div class="container">
        <h2><?= htmlspecialchars($notification['title']); ?></h2>
        <p class="date">Posted on: <?= htmlspecialchars($notification['created_at']); ?></p>
        <div class="message">
            <p><?= nl2br(htmlspecialchars($notification['message'])); ?></p>
        </div>

        <!-- Comment Section
        <div class="comment-section">
            <h3>Comments</h3>
            <form method="POST">
                <textarea name="comment" class="comment-box" placeholder="Write a comment..." required></textarea>
                <button type="submit" class="back-btn">Post Comment</button>
            </form>
            <div class="comment-list">
                <?php foreach ($comments as $comment) { ?>
                    <div class="comment">
                        <strong><?= htmlspecialchars($comment['name']); ?></strong> (<?= htmlspecialchars($comment['created_at']); ?>)
                        <p><?= nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div> -->

        <a href="notify.php" class="back-btn">Back to Notifications</a>
        <!-- Comment Section -->
        <div class="comment-section">
            <h3>Comments</h3>
            <form method="POST">
                <textarea name="comment" class="comment-box" placeholder="Write a comment..." required></textarea>
                <button type="submit" class="back-btn">Post Comment</button>
            </form>
            <div class="comment-list">
                <?php foreach ($comments as $comment) { ?>
                    <div class="comment">
                        <strong><?= htmlspecialchars($comment['name']); ?></strong> (<?= htmlspecialchars($comment['created_at']); ?>)
                        <p><?= nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>
</body>
</html>