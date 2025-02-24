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

// Handle attendance update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    foreach ($_POST['attendance'] as $attendance_id => $status) {
        $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE id = ? AND faculty_id = ?");
        $stmt->execute([$status, $attendance_id, $faculty_id]);
    }
    echo "<script>alert('Attendance updated successfully!'); window.location.href='update.php';</script>";
}

// Fetch courses
$stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects assigned to faculty
$stmt = $pdo->prepare("SELECT DISTINCT s.id, s.subject_name FROM subjects s JOIN schedule sch ON s.id = sch.subject_id WHERE sch.faculty_id = ? ORDER BY s.subject_name ASC");
$stmt->execute([$faculty_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch years
$stmt = $pdo->query("SELECT id, year_name FROM years ORDER BY year_name ASC");
$queryyears = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filters
$selectedCourse = $_GET['course_id'] ?? '';
$selectedSubject = $_GET['subject_id'] ?? '';
$selectedYear = $_GET['year_id'] ?? '';
$selectedDate = $_GET['date'] ?? '';

// Build query with filters
$query = "SELECT a.id AS attendance_id, a.student_id, s.name AS student_name, a.schedule_id, a.date, a.status, 
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
    <title>View & Update Attendance</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
    }
    .container {
        width: 60%;
        margin: auto;
        padding: 20px;
        background: white;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        margin-top: 20px;
    }
    .scrollable-table {
        max-height: 500px;
        overflow-y: auto;
    }
    h2 {
        text-align: center;
    }
    .filters {
        margin-bottom: 20px;
        text-align: center;
    }
    select, input[type="date"], button {
        padding: 10px;
        margin: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    button {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        padding: 10px 20px;
        font-size: 16px;
    }
    button:hover {
        background-color: #0056b3;
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
    .update-btn-container {
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>
<body>
    <div class="container">
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
            <label>Date:</label>
            <input type="date" name="date" value="<?= htmlspecialchars($selectedDate); ?>">
            <button type="submit">Filter</button>
        </form>
        <form method="POST">
            <div class="scrollable-table">
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
                            <td>
                                <select name="attendance[<?= $record['attendance_id']; ?>]">
                                    <option value="Present" <?= ($record['status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                    <option value="Late" <?= ($record['status'] == 'Late') ? 'selected' : ''; ?>>Late</option>
                                    <option value="Absent" <?= ($record['status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="update-btn-container">
                <button type="submit" name="update_attendance">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>
