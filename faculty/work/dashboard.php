<?php
session_start();
include('../config/db.php');
include('../includes/header.php'); // Database connection

// Check if faculty is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}
$faculty_id = $_SESSION['user_id']; // Faculty ID from session
$today = date('l'); // Get current day

// Get available courses, years, and days for filtering
$courseStmt = $pdo->prepare("SELECT DISTINCT c.id, c.course_name FROM courses c JOIN schedule s ON c.id = s.course_id WHERE s.faculty_id = ?");
$courseStmt->execute([$faculty_id]);
$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

$yearStmt = $pdo->prepare("SELECT DISTINCT y.id, y.year_name FROM years y JOIN schedule s ON y.id = s.year_id WHERE s.faculty_id = ?");
$yearStmt->execute([$faculty_id]);
$years = $yearStmt->fetchAll(PDO::FETCH_ASSOC);

$dayStmt = $pdo->prepare("SELECT DISTINCT day FROM schedule WHERE faculty_id = ?");
$dayStmt->execute([$faculty_id]);
$days = $dayStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedCourse = $_GET['course'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$selectedDay = $_GET['day'] ?? '';

// Fetch faculty schedule based on filters
$query = "SELECT s.id, sub.subject_name AS subject, c.course_name AS course, 
                 se.session_name AS session, y.year_name AS year, 
                 s.start_time, s.end_time, s.day 
          FROM schedule s
          JOIN subjects sub ON s.subject_id = sub.id
          JOIN courses c ON s.course_id = c.id
          JOIN sessions se ON s.session_id = se.id
          JOIN years y ON s.year_id = y.id
          WHERE s.faculty_id = ?";
$params = [$faculty_id];

if (!empty($selectedCourse)) {
    $query .= " AND s.course_id = ?";
    $params[] = $selectedCourse;
}

if (!empty($selectedYear)) {
    $query .= " AND s.year_id = ?";
    $params[] = $selectedYear;
}

if (!empty($selectedDay)) {
    $query .= " AND s.day = ?";
    $params[] = $selectedDay;
}

$query .= " ORDER BY s.day, s.start_time";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch faculty details
$stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
$stmt->execute([$faculty_id]);
$faculty = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
    }
    .container {
        width: 80%;
        margin: auto;
        padding: 20px;
        background: white;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        margin-top: 20px;
    }
    h2 {
        text-align: center;
    }
    .filters {
        margin-bottom: 20px;
        text-align: center;
    }
    select {
        padding: 8px;
        margin: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table, th, td {
        border: 1px solid #ddd;
    }
    th, td {
        padding: 10px;
        text-align: center;
    }
    th {
        background: #0056b3;
        color: white;
    }
    .status-present {
        color: green;
        font-weight: bold;
    }
    .status-absent {
        color: red;
        font-weight: bold;
    }
    .status-late {
        color: orange;
        font-weight: bold;
    }
</style>
    <script>
        function applyFilters() {
            let course = document.getElementById('courseFilter').value;
            let year = document.getElementById('yearFilter').value;
            let day = document.getElementById('dayFilter').value;
            window.location.href = "?course=" + course + "&year=" + year + "&day=" + day;
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($faculty['name']); ?> (Faculty)</h2>
    <h2>Schedule</h2>

    <!-- Filter Options -->
    <div class="filters">
        <label for="courseFilter">Course:</label>
        <select id="courseFilter" onchange="applyFilters()">
            <option value="">All Courses</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id']; ?>" <?= $selectedCourse == $course['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($course['course_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="yearFilter">Year:</label>
        <select id="yearFilter" onchange="applyFilters()">
            <option value="">All Years</option>
            <?php foreach ($years as $year): ?>
                <option value="<?= $year['id']; ?>" <?= $selectedYear == $year['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($year['year_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="dayFilter">Day:</label>
        <select id="dayFilter" onchange="applyFilters()">
            <option value="">All Days</option>
            <?php foreach ($days as $day): ?>
                <option value="<?= $day['day']; ?>" <?= $selectedDay == $day['day'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($day['day']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <table border="1">
        <tr>
            <th>Day</th>
            <th>Subject</th>
            <th>Course</th>
            <th>Session</th>
            <th>Year</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
        <?php foreach ($schedule as $row) { ?>
            <tr>
                <td><?= htmlspecialchars($row['day']); ?></td>
                <td><?= htmlspecialchars($row['subject']); ?></td>
                <td><?= htmlspecialchars($row['course']); ?></td>
                <td><?= htmlspecialchars($row['session']); ?></td>
                <td><?= htmlspecialchars($row['year']); ?></td>
                <td><?= htmlspecialchars($row['start_time']); ?></td>
                <td><?= htmlspecialchars($row['end_time']); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
