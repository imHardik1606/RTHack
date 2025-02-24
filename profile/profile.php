<?php
session_start();
include('../config/db.php');
include('../includes/header.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT students.name, students.email, courses.course_name, sessions.session_name, years.year_name 
                       FROM students 
                       LEFT JOIN courses ON students.course_id = courses.id
                       LEFT JOIN sessions ON students.session_id = sessions.id
                       LEFT JOIN years ON students.year_id = years.id
                       WHERE students.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle case where user data is missing
if (!$user) {
    $user = [
        'name' => 'N/A',
        'email' => 'N/A',
        'course_name' => 'N/A',
        'session_name' => 'N/A',
        'year_name' => 'N/A'
    ];
    $error_message = "User details not found. Please contact the administrator.";
} else {
    $error_message = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg p-6 w-96 text-center">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">My Profile</h2>

        <!-- Error Message -->
        <?php if ($error_message): ?>
            <p class="bg-red-100 text-red-700 p-3 rounded-md mb-4"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- User Details -->
        <p class="text-lg"><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
        <p class="text-lg"><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        <p class="text-lg"><strong>Course:</strong> <?= htmlspecialchars($user['course_name']); ?></p>
        <p class="text-lg"><strong>Session:</strong> <?= htmlspecialchars($user['session_name']); ?></p>
        <p class="text-lg"><strong>Year:</strong> <?= htmlspecialchars($user['year_name']); ?></p>

        <!-- Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="../student/dashboard.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">Dashboard</a>
            <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">Logout</a>
        </div>
    </div>

</body>
</html>
