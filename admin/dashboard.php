<?php
session_start();
// include('../config/db.php');
include('../config/db.php');
include('..\includes/header.php'); // Database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get admin details
$stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$adminName = $admin['name'] ?? 'Admin';

// Fetch dashboard statistics
// 1️⃣ Total Students
$stmt = $pdo->prepare("SELECT COUNT(*) as total_students FROM students WHERE role = 'student'");
$stmt->execute();
$totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'] ?? 0;

// 2️⃣ Total Faculty Members
$stmt = $pdo->prepare("SELECT COUNT(*) as total_faculty FROM students WHERE role = 'faculty'");
$stmt->execute();
$totalFaculty = $stmt->fetch(PDO::FETCH_ASSOC)['total_faculty'] ?? 0;

// 3️⃣ Total Courses
$stmt = $pdo->prepare("SELECT COUNT(*) as total_courses FROM courses");
$stmt->execute();
$totalCourses = $stmt->fetch(PDO::FETCH_ASSOC)['total_courses'] ?? 0;

// 4️⃣ Total Subjects
$stmt = $pdo->prepare("SELECT COUNT(*) as total_subjects FROM subjects");
$stmt->execute();
$totalSubjects = $stmt->fetch(PDO::FETCH_ASSOC)['total_subjects'] ?? 0;

// 5️⃣ Total Attendance Records
$stmt = $pdo->prepare("SELECT COUNT(*) as total_attendance FROM attendance");
$stmt->execute();
$totalAttendance = $stmt->fetch(PDO::FETCH_ASSOC)['total_attendance'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            text-align: center;
        }

        .header {
            background: #0056b3;
            color: white;
            padding: 15px;
            font-size: 24px;
            text-align: center;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            width: 200px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }

        .stat-box h2 {
            margin: 10px 0;
        }

        .logout-btn {
            background: red;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .logout-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

    <!-- <div class="header">
       
    </div> -->

    <div class="container">
        <!-- <h2>Admin Dashboard</h2> -->

        <div class="stats">
            <div class="stat-box">
                <h2><?= $totalStudents; ?></h2>
                <p>Total Students</p>
            </div>

            <div class="stat-box">
                <h2><?= $totalFaculty; ?></h2>
                <p>Total Faculty</p>
            </div>

            <div class="stat-box">
                <h2><?= $totalCourses; ?></h2>
                <p>Total Courses</p>
            </div>

            <div class="stat-box">
                <h2><?= $totalSubjects; ?></h2>
                <p>Total Subjects</p>
            </div>

            <div class="stat-box">
                <h2><?= $totalAttendance; ?></h2>
                <p>Total Attendance Records</p>
            </div>
        </div>

        <form action="../auth/logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

</body>
</html>
