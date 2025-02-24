<?php
// session_start();
// include '../security.php';

// Define a logging function with role-based log files
function logActivity($message) {
    $role = $_SESSION['role'] ?? 'guest';
    $logDir = __DIR__ . "/logs/$role/";
    $logFile = $logDir . 'logs.txt';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['name'] ?? 'Guest';
    $page = basename($_SERVER['PHP_SELF']);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $url = $_SERVER['REQUEST_URI'] ?? 'Unknown URL';
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'No Referrer';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User-Agent';
    
    $logEntry = "[$timestamp] IP: $ipAddress - URL: $url - Referrer: $referrer - User-Agent: $userAgent - $user accessed $page - $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';

// Log the page visit
logActivity("Visited the header page.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f4f4;
        }

        .navbar {
            background: #0056b3;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .profile-menu {
            position: relative;
            display: inline-block;
        }

        .profile-button {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            border-radius: 5px;
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 10px;
            display: block;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .profile-menu:hover .dropdown-content {
            display: block;
        }

        .logout {
            background: red;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<header>
    <div class="navbar">
        <div>
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole == 'admin'): ?>
                    <a href="<?= APP_URL; ?>/admin/dashboard.php">🔧 Dashboard</a>
                    <a href="<?= APP_URL; ?>/admin/manage_faculty.php">👨‍🏫 Faculty</a>
                    <a href="<?= APP_URL; ?>/admin/manage_student.php">🎓 Students</a>
                    <a href="<?= APP_URL; ?>/admin/manage_courses.php">📚 Courses</a>
                    <a href="<?= APP_URL; ?>/admin/manage_year.php">🚀 Years</a>
                    <a href="<?= APP_URL; ?>/admin/manage_sessions.php">📅 Sessions</a>
                    <a href="<?= APP_URL; ?>/admin/show_schedule.php">📊 Schedule</a>
                    <a href="<?= APP_URL; ?>/admin/manage_subjects.php">📝 Subject</a>
                    <a href="<?= APP_URL; ?>/admin/notification.php">📝 Subject</a>
                    <a href="<?= APP_URL; ?>/admin/report.php">📌 Report</a>
                <?php elseif ($userRole == 'faculty'): ?>
                    <a href="<?= APP_URL; ?>/faculty/dashboard.php">📊 Dashboard</a>
                    <a href="<?= APP_URL; ?>/faculty/update.php">📝 View Attendance</a>
                    <a href="<?= APP_URL; ?>/faculty/qr.php"> QR Generator </a>
                    <a href="<?= APP_URL; ?>/faculty/mark.php">✅ Mark Attendance</a>
                <?php elseif ($userRole == 'student'): ?>

                    <a href="<?= APP_URL; ?>/student/dashboard.php">🏠 Home</a>
                    <a href="<?= APP_URL; ?>/student/notify.php">📊 Notification</a>
                    <a href="#">📊 Exam </a>
                    <a href="<?= APP_URL; ?>/student/scan_qr.php">Scan QR</a>
                    <a href="<?= APP_URL; ?>/student/details.php">📊 Details</a>
                    
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div>
            <?php if ($isLoggedIn): ?>
                <div class="profile-menu">
                    <span class="profile-button">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?> ⬇</span>
                    <div class="dropdown-content">
                        <a href="<?= APP_URL; ?>/auth/forgot-password.php">🔑 Forgot Password</a>
                        <a href="<?= APP_URL; ?>/auth/change-password.php">🔒 Change Password</a>
                        <a href="<?= APP_URL; ?>/profile/edit-profile.php">✏ Edit Profile</a>
                    </div>
                </div>
                <a href="<?= APP_URL; ?>/auth/logout.php" class="logout">Logout</a>
            <?php else: ?>
                <a href="<?= APP_URL; ?>/auth/login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
</body>
</html>
