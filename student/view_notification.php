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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-2xl w-full bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-blue-600 text-center">
            <?= htmlspecialchars($notification['title']); ?>
        </h2>
        <p class="text-sm text-gray-500 text-right mt-2">
            Posted on: <?= htmlspecialchars($notification['created_at']); ?>
        </p>
        <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-600">
            <p class="text-gray-700"> <?= nl2br(htmlspecialchars($notification['message'])); ?> </p>
        </div>
        
        <a href="notify.php" class="block mt-6 text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
            Back to Notifications
        </a>
        
        <div class="mt-6">
            <h3 class="text-lg font-semibold">Comments</h3>
            <form method="POST" class="mt-4">
                <textarea name="comment" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="Write a comment..." required></textarea>
                <button type="submit" class="mt-3 w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                    Post Comment
                </button>
            </form>
            
            <div class="mt-4 bg-gray-50 p-4 rounded-md">
                <?php foreach ($comments as $comment) { ?>
                    <div class="p-3 border-b border-gray-200 last:border-b-0">
                        <strong class="text-gray-800"> <?= htmlspecialchars($comment['name']); ?> </strong>
                        <span class="text-xs text-gray-500"> (<?= htmlspecialchars($comment['created_at']); ?>) </span>
                        <p class="text-gray-700 mt-2"> <?= nl2br(htmlspecialchars($comment['comment_text'])); ?> </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
