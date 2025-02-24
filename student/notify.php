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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold border-b-2 border-blue-500 pb-2 mb-4">Notifications</h3>
        <ul class="space-y-4">
            <?php if (count($notifications) > 0) { ?>
                <?php foreach ($notifications as $notification) { ?>
                    <li class="bg-gray-200 p-4 rounded-lg">
                        <a href="view_notification.php?id=<?= $notification['id']; ?>" class="text-blue-600 font-semibold hover:underline">
                            <?= htmlspecialchars($notification['title']); ?>
                        </a>
                        <p class="text-gray-600 text-sm mt-1"> <?= date("d M Y, H:i", strtotime($notification['created_at'])); ?></p>
                    </li>
                <?php } ?>
            <?php } else { ?>
                <p class="text-gray-500">No notifications available.</p>
            <?php } ?>
        </ul>
    </div>
</body>
</html>

