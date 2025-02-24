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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>🎓 Student Dashboard</h2>

        <div class="alert alert-info">
            <strong>Overall Attendance Percentage:</strong> <?= $overall_attendance_percentage; ?>%
        </div>

        <!-- Filter Form -->
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Course:</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course) { ?>
                        <option value="<?= $course['id']; ?>" <?= ($course_id == $course['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Semester:</label>
                <select name="year_id" id="year_id" class="form-control" required>
                    <option value="">-- Select Semester --</option>
                    <?php foreach ($years as $year) { ?>
                        <option value="<?= $year['id']; ?>" <?= ($year_id == $year['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($year['year_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">🔍 Filter</button>
            </div>
        </form>

        <!-- Attendance Table -->
        <h3 class="mt-4">📅 Attendance Records</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Total Lectures</th>
                    <th>Total Attended</th>
                    <th>Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($attendance_records) > 0) { ?>
                    <?php foreach ($attendance_records as $record) { ?>
                        <tr>
                            <td><?= htmlspecialchars($record['subject_name']); ?></td>
                            <td><?= $record['total_lectures']; ?></td>
                            <td><?= $record['attended_lectures']; ?></td>
                            <td>
                                <?php
                                $percentage = $record['total_lectures'] > 0
                                    ? round(($record['attended_lectures'] / $record['total_lectures']) * 100, 2)
                                    : 0;
                                ?>
                                <span class="badge <?= $percentage >= 75 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?= $percentage; ?>%
                                </span>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="text-center">No attendance records found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        // Dynamically fetch subjects when course and semester change
        document.getElementById('course_id').addEventListener('change', fetchSubjects);
        document.getElementById('year_id').addEventListener('change', fetchSubjects);

        function fetchSubjects() {
            let course_id = document.getElementById('course_id').value;
            let year_id = document.getElementById('year_id').value;
            let subjectDropdown = document.getElementById('subject_id');

            subjectDropdown.innerHTML = '<option value="">-- Select Subject --</option>';

            if (course_id && year_id) {
                fetch(`fetch_subjects.php?course_id=${course_id}&year_id=${year_id}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(subject => {
                            let option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.subject_name;
                            subjectDropdown.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching subjects:', error));
            }
        }
    </script>
</body>
</html>
