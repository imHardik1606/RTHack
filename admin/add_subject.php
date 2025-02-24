<?php
session_start();
include('../config/db.php');
include('../includes/header.php');

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied. Admin only.");
}

// Fetch courses, years, and sessions
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$years = $pdo->query("SELECT * FROM years ORDER BY year_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM sessions ORDER BY session_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Check if editing an existing subject
$subject_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$subject_name = "";
$selected_course_id = "";
$selected_year_id = "";
$selected_session_id = "";

if ($subject_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subject) {
        $subject_name = $subject['subject_name'];
        $selected_course_id = $subject['course_id'];
        $selected_year_id = $subject['year_id'];
        $selected_session_id = $subject['session_id'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = $_POST['subject_name'];
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $session_id = $_POST['session_id'];

    if ($subject_id > 0) {
        // Update subject
        $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, course_id = ?, year_id = ?, session_id = ? WHERE id = ?");
        $stmt->execute([$subject_name, $course_id, $year_id, $session_id, $subject_id]);
    } else {
        // Insert new subject
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, course_id, year_id, session_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$subject_name, $course_id, $year_id, $session_id]);
    }
    
    header("Location: manage_subjects.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $subject_id > 0 ? 'Edit Subject' : 'Add Subject' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2><?= $subject_id > 0 ? '✏ Edit Subject' : '➕ Add Subject' ?></h2>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Subject Name:</label>
                <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($subject_name); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Course:</label>
                <select name="course_id" class="form-control" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course) { ?>
                        <option value="<?= $course['id']; ?>" <?= ($selected_course_id == $course['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Year (Semester):</label>
                <select name="year_id" class="form-control" required>
                    <option value="">-- Select Year --</option>
                    <?php foreach ($years as $year) { ?>
                        <option value="<?= $year['id']; ?>" <?= ($selected_year_id == $year['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($year['year_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Session:</label>
                <select name="session_id" class="form-control" required>
                    <option value="">-- Select Session --</option>
                    <?php foreach ($sessions as $session) { ?>
                        <option value="<?= $session['id']; ?>" <?= ($selected_session_id == $session['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($session['session_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><?= $subject_id > 0 ? '✅ Update Subject' : '➕ Add Subject' ?></button>
            <a href="manage_subjects.php" class="btn btn-secondary">🔙 Back</a>
        </form>
    </div>
</body>
</html>
