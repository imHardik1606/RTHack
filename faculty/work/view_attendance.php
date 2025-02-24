<?php
session_start();
include('../config/db.php'); // Database connection
include('../includes/header.php');
// Ensure faculty is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Fetch courses
$stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects
$stmt = $pdo->query("SELECT id, subject_name FROM subjects ORDER BY subject_name ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch years
$stmt = $pdo->query("SELECT id, year_name FROM years ORDER BY year_name ASC");
$years = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filters
$selectedCourse = $_GET['course_id'] ?? '';
$selectedSubject = $_GET['subject_id'] ?? '';
$selectedYear = $_GET['year_id'] ?? '';
$selectedDate = $_GET['date'] ?? '';

// Build query with filters
$query = "SELECT 
            a.student_id, s.name AS student_name, a.schedule_id, a.date, a.status, 
            a.total_classes, a.attended_classes, sb.subject_name, c.course_name, y.year_name, se.session_name
          FROM attendance a
          JOIN students s ON a.student_id = s.id
          JOIN subjects sb ON a.subjects_id = sb.id
          JOIN courses c ON a.course_id = c.id
          JOIN years y ON a.year_id = y.id
          JOIN sessions se ON a.session_id = se.id
          WHERE a.faculty_id = ?";


$params = [$faculty_id];

if (!empty($selectedCourse)) {
    $query .= " AND a.course_id = ?";
    $params[] = $selectedCourse;
}
if (!empty($selectedSubject)) {
    $query .= " AND a.subjects_id = ?";
    $params[] = $selectedSubject;
}
if (!empty($selectedYear)) {
    $query .= " AND a.year_id = ?";
    $params[] = $selectedYear;
}
if (!empty($selectedDate)) {
    $query .= " AND a.date = ?";
    $params[] = $selectedDate;
}

$query .= " ORDER BY a.date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Attendance</title>
    <style>
      
    </style>
</head>
<body>
    <br>
    <form method="GET">
        <label>Course:</label>
        <select name="course_id">
            <option value="">All Courses</option>
            <?php foreach ($courses as $course) { ?>
                <option value="<?= $course['id']; ?>" <?= ($selectedCourse == $course['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($course['course_name']); ?>
                </option>
            <?php } ?>
        </select>
        <label>Subject:</label>
        <select name="subject_id">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $subject) { ?>
                <option value="<?= $subject['id']; ?>" <?= ($selectedSubject == $subject['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($subject['subject_name']); ?>
                </option>
            <?php } ?>
        </select>
        <label>Year:</label>
        <select name="year_id">
            <option value="">All Years</option>
            <?php foreach ($years as $year) { ?>
                <option value="<?= $year['id']; ?>" <?= ($selectedYear == $year['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($year['year_name']); ?>
                </option>
            <?php } ?>
        </select>
        <label>Date:</label>
        <input type="date" name="date" value="<?= htmlspecialchars($selectedDate); ?>">
        <button type="submit">Filter</button>
    </form>
    <table>
        <tr>
            <th>Student</th>
            <th>Subject</th>
            <th>Course</th>
            <th>Year</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        <?php foreach ($attendanceRecords as $record) { ?>
            <tr>
                <td><?= htmlspecialchars($record['student_name']); ?></td>
                <td><?= htmlspecialchars($record['subject_name']); ?></td>
                <td><?= htmlspecialchars($record['course_name']); ?></td>
                <td><?= htmlspecialchars($record['year_name']); ?></td>
                <td><?= htmlspecialchars($record['date']); ?></td>
                <td><?= htmlspecialchars($record['status']); ?></td>
            </tr>
        <?php } ?>
    </table>
    <?php if (empty($attendanceRecords)) { ?>
        <p style="text-align:center; color: red;">No attendance records found.</p>
    <?php } ?>
</body>
</html>
