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

// Initialize filters
$selected_course_id = $_POST['course_id'] ?? "";
$selected_year_id = $_POST['year_id'] ?? "";
$selected_session_id = $_POST['session_id'] ?? "";

// Pagination settings
$limit = 10; // Number of subjects per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Fetch filtered subjects with pagination
$query = "SELECT s.*, c.course_name, y.year_name, ses.session_name 
          FROM subjects s
          JOIN courses c ON s.course_id = c.id
          JOIN years y ON s.year_id = y.id
          JOIN sessions ses ON s.session_id = ses.id
          WHERE 1"; // Base query

$params = [];
if (!empty($selected_course_id)) {
    $query .= " AND s.course_id = ?";
    $params[] = $selected_course_id;
}
if (!empty($selected_year_id)) {
    $query .= " AND s.year_id = ?";
    $params[] = $selected_year_id;
}
if (!empty($selected_session_id)) {
    $query .= " AND s.session_id = ?";
    $params[] = $selected_session_id;
}

// Get total records count
$count_query = str_replace("s.*, c.course_name, y.year_name, ses.session_name", "COUNT(*) AS total", $query);
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Add pagination to query
$query .= " ORDER BY c.course_name, y.year_name, s.subject_name ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle subject addition or update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject_name'])) {
    $subject_name = $_POST['subject_name'];
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $session_id = $_POST['session_id'];

    if (!empty($_POST['subject_id'])) {
        // Update subject
        $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, course_id = ?, year_id = ?, session_id = ? WHERE id = ?");
        $stmt->execute([$subject_name, $course_id, $year_id, $session_id, $_POST['subject_id']]);
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
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function applyFilters() {
            document.getElementById('filterForm').submit();
        }
    </script>
</head>
<body>
    <div class="container mt-4">
        
        <h2>📘 Manage Subjects</h2>


        <!-- Filters -->
        <form method="POST" id="filterForm">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Course:</label>
                    <select name="course_id" class="form-control" onchange="applyFilters()">
                        <option value="">-- All Courses --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id']; ?>" <?= ($selected_course_id == $course['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Year (Semester):</label>
                    <select name="year_id" class="form-control" onchange="applyFilters()">
                        <option value="">-- All Years --</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year['id']; ?>" <?= ($selected_year_id == $year['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($year['year_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Session:</label>
                    <select name="session_id" class="form-control" onchange="applyFilters()">
                        <option value="">-- All Sessions --</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= $session['id']; ?>" <?= ($selected_session_id == $session['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($session['session_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- Subjects Table -->
        <h3 class="mt-4">📜 Subject List</h3>
        <table class="table table-bordered">
            <tr><th>Subject id</th>
                <th>Subject Name</th>
                <th>Course</th>
                <th>Year</th>
                <th>Session</th>
                <th>Action</th>
            </tr>
            <?php foreach ($subjects as $subject): ?>
                <tr>
                <td><?= htmlspecialchars($subject['id']); ?></td>
                    <td><?= htmlspecialchars($subject['subject_name']); ?></td>
                    <td><?= htmlspecialchars($subject['course_name']); ?></td>
                    <td><?= htmlspecialchars($subject['year_name']); ?></td>
                    <td><?= htmlspecialchars($subject['session_name']); ?></td>
                    <!-- <td>
                        <button class="btn btn-warning btn-sm edit-btn" data-id="<?= $subject['id']; ?>"
                                data-name="<?= htmlspecialchars($subject['subject_name']); ?>"
                                data-course="<?= $subject['course_id']; ?>"
                                data-year="<?= $subject['year_id']; ?>"
                                data-session="<?= $subject['session_id']; ?>">✏ Edit</button>
                                
                    </td> -->
                    <td>
                <a href="add_subject.php?id=<?= $subject['id']; ?>" class="btn btn-warning btn-sm">✏ Edit</a>
            </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>">Next</a></li>
                    <li class="page-item"><a class="page-link" href="?page=<?= $total_pages; ?>">Last</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <td>
    <a href="add_subject.php?id=<?= $subject['id']; ?>" class="btn btn-warning btn-sm">✏ ADD SUBJECT</a>
</td>

    </div>
</body>
</html>
