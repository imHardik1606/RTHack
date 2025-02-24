<?php
session_start();
include('../config/db.php');
include('../includes/header.php');

// Ensure the user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied. Students only.");
}

$student_id = $_SESSION['user_id'];

// Fetch available courses, semesters (years), and subjects for filters
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$years = $pdo->query("SELECT * FROM years ORDER BY year_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$course_id = $_GET['course_id'] ?? '';
$year_id = $_GET['year_id'] ?? '';

// Fetch subjects based on course & year
$subjects = [];
if ($course_id && $year_id) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE course_id = ? AND year_id = ? ORDER BY subject_name ASC");
    $stmt->execute([$course_id, $year_id]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch attendance details subject-wise
$query = "SELECT sub.id AS subject_id, sub.subject_name, 
                 COUNT(a.id) AS total_lectures, 
                 SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS attended_lectures
          FROM subjects sub
          LEFT JOIN schedule s ON sub.id = s.subject_id
          LEFT JOIN attendance a ON s.id = a.schedule_id AND a.student_id = ?
          WHERE sub.course_id = ? AND sub.year_id = ?
          GROUP BY sub.id, sub.subject_name";

$stmt = $pdo->prepare($query);
$stmt->execute([$student_id, $course_id, $year_id]);
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall attendance percentage
$total_lectures = array_sum(array_column($attendance_records, 'total_lectures'));
$total_attended = array_sum(array_column($attendance_records, 'attended_lectures'));
$overall_attendance_percentage = $total_lectures > 0 ? round(($total_attended / $total_lectures) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-8 p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-gray-800">🎓 Student Dashboard</h2>

        <div class="mt-4 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 rounded">
            <strong>Overall Attendance Percentage:</strong> 
            <span class="font-bold"><?= $overall_attendance_percentage; ?>%</span>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Course:</label>
                <select name="course_id" id="course_id" class="w-full mt-1 p-2 border rounded">
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course) { ?>
                        <option value="<?= $course['id']; ?>" <?= ($course_id == $course['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Semester:</label>
                <select name="year_id" id="year_id" class="w-full mt-1 p-2 border rounded">
                    <option value="">-- Select Semester --</option>
                    <?php foreach ($years as $year) { ?>
                        <option value="<?= $year['id']; ?>" <?= ($year_id == $year['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($year['year_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                    🔍 Filter Attendance
                </button>
            </div>
        </form>

        <!-- Attendance Table -->
        <h3 class="mt-6 text-xl font-semibold">📅 Attendance Records</h3>
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="px-4 py-2">Subject</th>
                        <th class="px-4 py-2">Total Lectures</th>
                        <th class="px-4 py-2">Total Attended</th>
                        <th class="px-4 py-2">Attendance Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($attendance_records) > 0) { ?>
                        <?php foreach ($attendance_records as $record) { 
                            $percentage = $record['total_lectures'] > 0
                                ? round(($record['attended_lectures'] / $record['total_lectures']) * 100, 2)
                                : 0;
                        ?>
                        <tr class="border-b hover:bg-gray-100">
                            <td class="px-4 py-2"><?= htmlspecialchars($record['subject_name']); ?></td>
                            <td class="px-4 py-2"><?= $record['total_lectures']; ?></td>
                            <td class="px-4 py-2"><?= $record['attended_lectures']; ?></td>
                            <td class="px-4 py-2">
                                <span class="px-3 py-1 rounded text-white <?= $percentage >= 75 ? 'bg-green-500' : 'bg-red-500'; ?>">
                                    <?= $percentage; ?>%
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-center text-gray-500">No attendance records found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('course_id').addEventListener('change', fetchSubjects);
        document.getElementById('year_id').addEventListener('change', fetchSubjects);

        function fetchSubjects() {
            let course_id = document.getElementById('course_id').value;
            let year_id = document.getElementById('year_id').value;

            if (course_id && year_id) {
                fetch(`fetch_subjects.php?course_id=${course_id}&year_id=${year_id}`)
                    .then(response => response.json())
                    .then(data => console.log("Subjects Loaded")) 
                    .catch(error => console.error('Error fetching subjects:', error));
            }
        }
    </script>
</body>
</html>
