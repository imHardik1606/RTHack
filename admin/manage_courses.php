<?php
session_start();
require '../config/db.php'; // Ensure this file establishes $pdo connection

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_course'])) {
        $course_name = trim($_POST['course_name']);

        // Prevent duplicate course names
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_name = ?");
        $stmt->execute([$course_name]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO courses (course_name) VALUES (?)")->execute([$course_name]);
        }
    } elseif (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];
        $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$course_id]);
    }
    header("Location: manage_courses.php");
    exit;
}

// Fetch existing courses
$courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Courses - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include('..\includes/header.php');  ?>
    <div class="container mt-4">
        <h2>📌 Manage Courses</h2>
        
        <h4>➕ Add Course</h4>
        <form method="POST" class="mb-3">
            <input type="text" name="course_name" placeholder="Course Name" required class="form-control w-50 d-inline">
            <button type="submit" name="add_course" class="btn btn-primary">Add</button>
        </form>

        <h4>📋 Existing Courses</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= htmlspecialchars($course['id']) ?></td>
                        <td><?= htmlspecialchars($course['course_name']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <button type="submit" name="delete_course" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
</body>
</html>
