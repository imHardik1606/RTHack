<?php
// define('APP_URL', 'http://localhost/attendance'); // Ensure APP_URL is set
// session_start();

// Function to check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="bg-blue-700 text-white">
    <div class="container mx-auto flex justify-between items-center py-4 px-6">
        <div class="flex space-x-4">
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole == 'admin'): ?>
                    <a href="<?= APP_URL; ?>/admin/dashboard.php" class="hover:underline">🔧 Dashboard</a>
                    <a href="<?= APP_URL; ?>/admin/manage_faculty.php" class="hover:underline">👨‍🏫 Faculty</a>
                    <a href="<?= APP_URL; ?>/admin/manage_student.php" class="hover:underline">🎓 Students</a>
                    <a href="<?= APP_URL; ?>/admin/manage_courses.php" class="hover:underline">📚 Courses</a>
                    <a href="<?= APP_URL; ?>/admin/manage_year.php" class="hover:underline">🚀 Years</a>
                    <a href="<?= APP_URL; ?>/admin/manage_sessions.php" class="hover:underline">📅 Sessions</a>
                    <a href="<?= APP_URL; ?>/admin/show_schedule.php" class="hover:underline">📊 Schedule</a>
                    <a href="<?= APP_URL; ?>/admin/manage_subjects.php" class="hover:underline">📝 Subjects</a>
                    <a href="<?= APP_URL; ?>/admin/notification.php" class="hover:underline">🔔 Notifications</a>
                    <a href="<?= APP_URL; ?>/admin/report.php" class="hover:underline">📌 Reports</a>
                <?php elseif ($userRole == 'faculty'): ?>
                    <a href="<?= APP_URL; ?>/faculty/dashboard.php" class="hover:underline">📊 Dashboard</a>
                    <a href="<?= APP_URL; ?>/faculty/update.php" class="hover:underline">📝 View Attendance</a>
                    <a href="<?= APP_URL; ?>/faculty/qr.php" class="hover:underline">📷 QR Generator</a>
                    <a href="<?= APP_URL; ?>/faculty/mark.php" class="hover:underline">✅ Mark Attendance</a>
                <?php elseif ($userRole == 'student'): ?>
                    <a href="<?= APP_URL; ?>/student/dashboard.php" class="hover:underline">🏠 Home</a>
                    <a href="<?= APP_URL; ?>/student/notify.php" class="hover:underline">📊 Notifications</a>
                    <a href="#" class="hover:underline">📊 Exam</a>
                    <a href="<?= APP_URL; ?>/student/scan_qr.php" class="hover:underline">📷 Scan QR</a>
                    <a href="<?= APP_URL; ?>/student/details.php" class="hover:underline">📄 Details</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="flex items-center space-x-4">
            <?php if ($isLoggedIn): ?>
                <div class="relative group">
                    <button class="bg-white text-blue-700 px-3 py-2 rounded-lg font-semibold focus:outline-none">
                        Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?> ⬇
                    </button>
                    <div class="absolute right-0 mt-2 hidden group-hover:block bg-white text-black rounded-lg shadow-lg w-48">
                        <a href="<?= APP_URL; ?>/auth/forgot-password.php" class="block px-4 py-2 hover:bg-gray-200">🔑 Forgot Password</a>
                        <a href="<?= APP_URL; ?>/auth/change-password.php" class="block px-4 py-2 hover:bg-gray-200">🔒 Change Password</a>
                        <a href="<?= APP_URL; ?>/profile/edit-profile.php" class="block px-4 py-2 hover:bg-gray-200">✏ Edit Profile</a>
                    </div>
                </div>
                <a href="<?= APP_URL; ?>/auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700">Logout</a>
            <?php else: ?>
                <a href="<?= APP_URL; ?>/auth/login.php" class="bg-white text-blue-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-200">Login</a>
            <?php endif; ?>
        </div>
    </div>
            </header>
</body>
</html>
