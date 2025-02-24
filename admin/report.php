<?php
session_start();
include('../config/db.php');
include('../includes/header.php'); // Database connection

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch courses, years, and sessions for filtering
$courseStmt = $pdo->query("SELECT id, course_name FROM courses");
$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

$yearStmt = $pdo->query("SELECT id, year_name FROM years");
$years = $yearStmt->fetchAll(PDO::FETCH_ASSOC);

$sessionStmt = $pdo->query("SELECT id, session_name FROM sessions");
$sessions = $sessionStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected filters
$selectedCourse = $_GET['course'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$selectedSession = $_GET['session'] ?? '';

// Fetch attendance summary if filters are applied
$attendanceData = [];
if ($selectedCourse && $selectedYear && $selectedSession) {
    $query = "SELECT s.id AS student_id, s.name AS student_name, 
                     COUNT(a.id) AS total_lectures, 
                     SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS attended_lectures,
                     ROUND((SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) AS avg_attendance
              FROM attendance a
              JOIN students s ON s.id = a.student_id
              WHERE s.course_id = ? AND s.year_id = ? AND s.session_id = ?
              GROUP BY s.id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$selectedCourse, $selectedYear, $selectedSession]);
    $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
 <style>body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    /* padding: 20px; */
}

.container {
    /* max-width: 900px; */
    max-width: 80%;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2, h3 {
    text-align: center;
    color: #333;
}

form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    justify-content: center;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

form select,
form button {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    min-width: 150px;
}

form button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

form button:hover {
    background-color: #0056b3;
}

/* Ensure responsiveness */
@media (max-width: 768px) {
    form {
        flex-direction: column;
    }

    form select, 
    form button {
        width: 100%;
    }
}


table {
    width: 100%;
    border-collapse: collapse;
    background: white;
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
    background-color: #007bff;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}
</style></style>
</head>
<body>
<div class="container">
    <h2>Attendance Report</h2>
    <form method="GET">
        <label for="course">Course:</label>
        <select name="course" id="course" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $course) { ?>
                <option value="<?= $course['id']; ?>" <?= $selectedCourse == $course['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($course['course_name']); ?>
                </option>
            <?php } ?>
        </select>
        
        <label for="year">Year:</label>
        <select name="year" id="year" required>
            <option value="">Select Year</option>
            <?php foreach ($years as $year) { ?>
                <option value="<?= $year['id']; ?>" <?= $selectedYear == $year['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($year['year_name']); ?>
                </option>
            <?php } ?>
        </select>
        
        <label for="session">Session:</label>
        <select name="session" id="session" required>
            <option value="">Select Session</option>
            <?php foreach ($sessions as $session) { ?>
                <option value="<?= $session['id']; ?>" <?= $selectedSession == $session['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($session['session_name']); ?>
                </option>
            <?php } ?>
        </select>
        
        <button type="submit">Generate Report</button>
    </form>
    
    <?php if (!empty($attendanceData)) { ?>
        <h3>Attendance Data</h3>
        <table border="1">
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Total Lectures</th>
                <th>Attended Lectures</th>
                <th>Average Attendance (%)</th>
            </tr>
            <?php foreach ($attendanceData as $row) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_id']); ?></td>
                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                    <td><?= $row['total_lectures']; ?></td>
                    <td><?= $row['attended_lectures']; ?></td>
                    <td><?= $row['avg_attendance']; ?>%</td>
                </tr>
            <?php } ?>
        </table>
        
        <form method="GET" action="generate_report.php">
            <input type="hidden" name="course" value="<?= $selectedCourse ?>">
            <input type="hidden" name="year" value="<?= $selectedYear ?>">
            <input type="hidden" name="session" value="<?= $selectedSession ?>">
            <button type="submit">Download PDF</button>
        </form>
    <?php } ?>
</div>
</body>
</html>
