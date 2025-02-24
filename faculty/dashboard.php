<?php
session_start();
include('../config/db.php');
include('../includes/header.php'); // Database connection

// Check if faculty is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}
$today = date('l');
$faculty_id = $_SESSION['user_id']; // Faculty ID from session

//---------------------------------------------------------------------------------------------------------------
$today = date('l'); // Get current day (Monday, Tuesday, etc.)

// Fetch today's schedule for faculty, sorted by course and subject
$stmt = $pdo->prepare("SELECT s.id, sub.subject_name AS subject, c.course_name AS course, 
                               se.session_name AS session, y.year_name AS year, 
                               s.start_time, s.end_time 
    FROM schedule s
    JOIN subjects sub ON s.subject_id = sub.id
    JOIN courses c ON s.course_id = c.id
    JOIN sessions se ON s.session_id = se.id
    JOIN years y ON s.year_id = y.id
    WHERE s.faculty_id = ? AND s.day = ?
    ORDER BY c.course_name, sub.subject_name");
$stmt->execute([$faculty_id, $today]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get attendance percentage per subject, ensuring no division by zero
$stmt = $pdo->prepare("SELECT sub.subject_name AS subject, 
           COUNT(a.id) AS total_classes, 
           SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS attended_classes,
           (CASE WHEN COUNT(a.id) > 0 THEN (SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100 ELSE 0 END) AS percentage
    FROM attendance a
    JOIN schedule sch ON a.schedule_id = sch.id
    JOIN subjects sub ON sch.subject_id = sub.id
    WHERE sch.faculty_id = ?
    GROUP BY sub.subject_name
    ORDER BY sub.subject_name");
$stmt->execute([$faculty_id]);
$attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

//---------------------------------------------------------------------------------------------------------------------------------

// Fetch faculty details
$stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
$stmt->execute([$faculty_id]);
$faculty = $stmt->fetch();

// Fetch available courses for filter
$courseQuery = "SELECT DISTINCT c.id, c.course_name FROM courses c 
                JOIN attendance a ON a.course_id = c.id 
                WHERE a.faculty_id = ?";
$courseStmt = $pdo->prepare($courseQuery);
$courseStmt->execute([$faculty_id]);
$courses = $courseStmt->fetchAll();

// Fetch available subjects for filter
$subjectQuery = "SELECT DISTINCT sb.id, sb.subject_name FROM subjects sb 
                 JOIN attendance a ON a.subjects_id = sb.id 
                 WHERE a.faculty_id = ?";
$subjectStmt = $pdo->prepare($subjectQuery);
$subjectStmt->execute([$faculty_id]);
$subjects = $subjectStmt->fetchAll();

// Get selected filters
$selectedCourse = $_GET['course'] ?? '';
$selectedSubject = $_GET['subject'] ?? '';

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

// Apply course filter
if (!empty($selectedCourse)) {
    $query .= " AND a.course_id = ?";
    $params[] = $selectedCourse;
}

// Apply subject filter
if (!empty($selectedSubject)) {
    $query .= " AND a.subjects_id = ?";
    $params[] = $selectedSubject;
}

// Order by course and subject
$query .= " ORDER BY c.course_name, sb.subject_name, a.date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$attendance_records = $stmt->fetchAll();
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
            let subject = document.getElementById('subjectFilter').value;
            window.location.href = "?course=" + course + "&subject=" + subject;
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($faculty['name']); ?> (Faculty)</h2>
    <h2>Today's Schedule</h2>
    <table border="1">
        <!-- <tr>
            <th>Subject</th>
            <th>Course</th>
            <th>Session</th>
            <th>Year</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Action</th>
        </tr> -->
        <?php foreach ($schedule as $row) { ?>
            <tr>
                <td><?= htmlspecialchars($row['subject']); ?></td>
                <td><?= htmlspecialchars($row['course']); ?></td>
                <td><?= htmlspecialchars($row['session']); ?></td>
                <td><?= htmlspecialchars($row['year']); ?></td>
                <td><?= htmlspecialchars($row['start_time']); ?></td>
                <td><?= htmlspecialchars($row['end_time']); ?></td>
                <td><a href="mark_attendance.php?schedule_id=<?= $row['id']; ?>">Mark Attendance</a></td>
            </tr>
        <?php } ?>
    </table>

    <h2>Attendance Percentage</h2>
    <table border="1">
        <tr>
            <th>Subject</th>
            <th>Total Classes</th>
            <th>Attended Classes</th>
            <th>Attendance %</th>
        </tr>
        <?php foreach ($attendanceData as $data) { ?>
            <tr>
                <td><?= htmlspecialchars($data['subject']); ?></td>
                <td><?= $data['total_classes']; ?></td>
                <td><?= $data['attended_classes']; ?></td>
                <td><?= round($data['percentage'], 2); ?>%</td>
            </tr>
        <?php } ?>
    </table>
    
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

        <label for="subjectFilter">Subject:</label>
        <select id="subjectFilter" onchange="applyFilters()">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id']; ?>" <?= $selectedSubject == $subject['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($subject['subject_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Attendance Table -->
    <table>
        <tr>
            <th>Student</th>
            <th>Subject</th>
            <th>Course</th>
            <th>Year</th>
            <th>Session</th>
            <th>Date</th>
            <th>Status</th>
            <!-- <th>Total Classes</th>
            <th>Attended Classes</th>
            <th>Attendance %</th> -->
        </tr>
        
        <?php foreach ($attendance_records as $row): 
            $total_classes = (int)$row['total_classes'];
            $attended_classes = (int)$row['attended_classes'];
            $attendance_percentage = ($total_classes > 0) ? round(($attended_classes / $total_classes) * 100, 2) : 0;
        ?>
        <tr>
            <td><?= htmlspecialchars($row['student_name']); ?></td>
            <td><?= htmlspecialchars($row['subject_name']); ?></td>
            <td><?= htmlspecialchars($row['course_name']); ?></td>
            <td><?= htmlspecialchars($row['year_name']); ?></td>
            <td><?= htmlspecialchars($row['session_name']); ?></td>
            <td><?= htmlspecialchars($row['date']); ?></td>
            <td class="status-<?= strtolower($row['status']); ?>"><?= htmlspecialchars($row['status']); ?></td>
            <!-- <td><?= $total_classes; ?></td>
            <td><?= $attended_classes; ?></td>
            <td><?= $attendance_percentage; ?>%</td> -->
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if (empty($attendance_records)): ?>
        <p style="text-align:center; color: red;">No attendance records found.</p>
    <?php endif; ?>

</div>

</body>
</html>
