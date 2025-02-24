<?php
session_start();
include('..\config/db.php');
include('..\includes/header.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ..\auth/login.php");
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
        .logout-btn, .dashboard-btn {
            padding: 10px 20px;
            display: inline-block;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
        .logout-btn {
            background: #d9534f;
        }
        .logout-btn:hover {
            background: #c9302c;
        }
        .dashboard-btn:hover {
            background: #0056b3;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>My Profile</h2>
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
    <p><strong>Course:</strong> <?= htmlspecialchars($user['course_name']); ?></p>
    <p><strong>Session:</strong> <?= htmlspecialchars($user['session_name']); ?></p>
    <p><strong>Year:</strong> <?= htmlspecialchars($user['year_name']); ?></p>

    <a href="..\student/dashboard.php" class="dashboard-btn">Go to Dashboard</a>
    <a href="..\auth/logout.php" class="logout-btn">Logout</a>
</div>
</body>
</html>
