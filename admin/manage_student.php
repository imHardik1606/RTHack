<?php
session_start();
require '../config/db.php'; // Ensure database connection

$message = ""; // Variable for messages

// Fetch Courses and Years
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$years = $pdo->query("SELECT * FROM years")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Students (Initially Empty)
$students = [];

// Handle Course & Year Selection
$selected_course_id = isset($_POST['course_id']) ? $_POST['course_id'] : "";
$selected_year_id = isset($_POST['year_id']) ? $_POST['year_id'] : "";

if (!empty($selected_course_id) && !empty($selected_year_id)) {
    $stmt = $pdo->prepare("SELECT students.*, courses.course_name, years.year_name, sessions.session_name 
                           FROM students 
                           JOIN courses ON students.course_id = courses.id
                           JOIN years ON students.year_id = years.id
                           JOIN sessions ON students.session_id = sessions.id
                           WHERE students.course_id = ? AND students.year_id = ?
                           ORDER BY students.id DESC");
    $stmt->execute([$selected_course_id, $selected_year_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle Student Deletion
if (isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt->execute([$student_id])) {
        $message = "<div class='alert alert-success'>✅ Student deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>⚠️ Error deleting student.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students - Attendance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function selectFilters() {
            document.getElementById('filterForm').submit();
        }
    </script>
</head>
<body>
    <?php include('..\includes/header.php');  ?>

    <div class="container mt-4">
        <h2>📌 Manage Students</h2>

        <?= $message; // Display messages if any ?>

        <!-- Select Course & Year First -->
        <h4>📚 Select Course & Year</h4>
        <form method="POST" id="filterForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <select name="course_id" class="form-select" onchange="selectFilters()" required>
                        <option value="">-- Choose Course --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id']; ?>" <?= ($selected_course_id == $course['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <select name="year_id" class="form-select" onchange="selectFilters()" required>
                        <option value="">-- Choose Year --</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year['id']; ?>" <?= ($selected_year_id == $year['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($year['year_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- Display Students (Only if Course & Year are Selected) -->
        <?php if (!empty($selected_course_id) && !empty($selected_year_id)): ?>
        <a href="add_student.php" class="btn btn-primary mb-3">➕ Add Student</a>

        <h4>📋 Student Records</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Session</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['id']); ?></td>
                            <td><?= htmlspecialchars($student['name']); ?></td>
                            <td><?= htmlspecialchars($student['email']); ?></td>
                            <td><?= htmlspecialchars($student['course_name']); ?></td>
                            <td><?= htmlspecialchars($student['year_name']); ?></td>
                            <td><?= htmlspecialchars($student['session_name']); ?></td>
                            <td>
                                <a href="edit_student.php?id=<?= $student['id']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="student_id" value="<?= $student['id']; ?>">
                                    <button type="submit" name="delete_student" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?');">❌ Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No students found for this selection.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

   
</body>
</html>
