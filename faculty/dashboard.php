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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function applyFilters() {
            let course = document.getElementById('courseFilter').value;
            let subject = document.getElementById('subjectFilter').value;
            window.location.href = "?course=" + course + "&subject=" + subject;
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="max-w-6xl mx-auto p-6 bg-white shadow-lg rounded-lg mt-6">
        <h2 class="text-2xl font-semibold text-center mb-4">Welcome, <?= htmlspecialchars($faculty['name']); ?> (Faculty)</h2>
        
        <h2 class="text-xl font-semibold text-center mb-4">Today's Schedule</h2>
        <table class="w-full border-collapse border border-gray-300 text-center">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="p-2">Subject</th>
                    <th class="p-2">Course</th>
                    <th class="p-2">Session</th>
                    <th class="p-2">Year</th>
                    <th class="p-2">Start Time</th>
                    <th class="p-2">End Time</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedule as $row) { ?>
                    <tr class="border border-gray-300">
                        <td class="p-2"> <?= htmlspecialchars($row['subject']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['course']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['session']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['year']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['start_time']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['end_time']); ?> </td>
                        <td class="p-2"> <a href="mark_attendance.php?schedule_id=<?= $row['id']; ?>" class="text-blue-600 hover:underline">Mark Attendance</a> </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2 class="text-xl font-semibold text-center my-4">Attendance Percentage</h2>
        <table class="w-full border-collapse border border-gray-300 text-center">
            <thead>
                <tr class="bg-green-600 text-white">
                    <th class="p-2">Subject</th>
                    <th class="p-2">Total Classes</th>
                    <th class="p-2">Attended Classes</th>
                    <th class="p-2">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendanceData as $data) { ?>
                    <tr class="border border-gray-300">
                        <td class="p-2"> <?= htmlspecialchars($data['subject']); ?> </td>
                        <td class="p-2"> <?= $data['total_classes']; ?> </td>
                        <td class="p-2"> <?= $data['attended_classes']; ?> </td>
                        <td class="p-2"> <?= round($data['percentage'], 2); ?>% </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="text-center my-4">
            <label for="courseFilter" class="font-semibold">Course:</label>
            <select id="courseFilter" class="p-2 border border-gray-300 rounded" onchange="applyFilters()">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id']; ?>" <?= $selectedCourse == $course['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($course['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="subjectFilter" class="font-semibold">Subject:</label>
            <select id="subjectFilter" class="p-2 border border-gray-300 rounded" onchange="applyFilters()">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= $subject['id']; ?>" <?= $selectedSubject == $subject['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($subject['subject_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <h2 class="text-xl font-semibold text-center my-4">Attendance Records</h2>
        <table class="w-full border-collapse border border-gray-300 text-center">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="p-2">Student</th>
                    <th class="p-2">Subject</th>
                    <th class="p-2">Course</th>
                    <th class="p-2">Year</th>
                    <th class="p-2">Session</th>
                    <th class="p-2">Date</th>
                    <th class="p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $row): ?>
                    <tr class="border border-gray-300">
                        <td class="p-2"> <?= htmlspecialchars($row['student_name']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['subject_name']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['course_name']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['year_name']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['session_name']); ?> </td>
                        <td class="p-2"> <?= htmlspecialchars($row['date']); ?> </td>
                        <td class="p-2 font-bold <?= $row['status'] == 'Present' ? 'text-green-600' : ($row['status'] == 'Absent' ? 'text-red-600' : 'text-orange-600'); ?>">
                            <?= htmlspecialchars($row['status']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

