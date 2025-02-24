<?php
session_start();
include('../config/db.php'); // Database connection
include('../includes/header.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch faculty members
$stmt = $pdo->query("SELECT id, name FROM students WHERE role = 'faculty'");
$faculties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses
$stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects
$stmt = $pdo->query("SELECT id, subject_name, course_id FROM subjects ORDER BY course_id, subject_name ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sessions
$stmt = $pdo->query("SELECT id, session_name FROM sessions");
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch years
$stmt = $pdo->query("SELECT id, year_name FROM years");
$years = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules based on filters
$schedules = [];
if (!empty($_GET['faculty_id']) || !empty($_GET['course_id']) || !empty($_GET['subject_id']) || !empty($_GET['year_id']) || !empty($_GET['session_id']) || !empty($_GET['day'])) {
    $query = "SELECT s.*, f.name AS faculty_name, c.course_name, sub.subject_name, ses.session_name, y.year_name 
              FROM schedule s
              JOIN students f ON s.faculty_id = f.id
              JOIN courses c ON s.course_id = c.id
              JOIN subjects sub ON s.subject_id = sub.id
              JOIN sessions ses ON s.session_id = ses.id
              JOIN years y ON s.year_id = y.id
              WHERE 1=1";
    
    $params = [];
    if (!empty($_GET['faculty_id'])) {
        $query .= " AND s.faculty_id = ?";
        $params[] = $_GET['faculty_id'];
    }
    if (!empty($_GET['course_id'])) {
        $query .= " AND s.course_id = ?";
        $params[] = $_GET['course_id'];
    }
    if (!empty($_GET['subject_id'])) {
        $query .= " AND s.subject_id = ?";
        $params[] = $_GET['subject_id'];
    }
    if (!empty($_GET['year_id'])) {
        $query .= " AND s.year_id = ?";
        $params[] = $_GET['year_id'];
    }
    if (!empty($_GET['session_id'])) {
        $query .= " AND s.session_id = ?";
        $params[] = $_GET['session_id'];
    }
    if (!empty($_GET['day'])) {
        $query .= " AND s.day = ?";
        $params[] = $_GET['day'];
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Show Schedule</title>
    <style>
  
form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    justify-content: center;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

form select,
form button {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    min-width: 150px;
}

form button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

form button:hover {
    background-color: #0056b3;
}

/* Ensure responsiveness */
@media (max-width: 768px) {
    form {
        flex-direction: column;
    }

    form select, 
    form button {
        width: 100%;
    }
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
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
    background-color: #007bff;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}
    </style>
</head>
<body>
    <form method="GET">
        <label>Faculty:</label>
        <select name="faculty_id">
            <option value="">Select Faculty</option>
            <?php foreach ($faculties as $faculty) { ?>
                <option value="<?= $faculty['id']; ?>"><?= htmlspecialchars($faculty['name']); ?></option>
            <?php } ?>
        </select>
        <label>Course:</label>
        <select name="course_id">
            <option value="">Select Course</option>
            <?php foreach ($courses as $course) { ?>
                <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
            <?php } ?>
        </select>
        <label>Year:</label>
        <select name="year_id">
            <option value="">Select Year</option>
            <?php foreach ($years as $year) { ?>
                <option value="<?= $year['id']; ?>"><?= htmlspecialchars($year['year_name']); ?></option>
            <?php } ?>
        </select>
        <label>Session:</label>
        <select name="session_id">
            <option value="">Select Session</option>
            <?php foreach ($sessions as $session) { ?>
                <option value="<?= $session['id']; ?>"><?= htmlspecialchars($session['session_name']); ?></option>
            <?php } ?>
        </select>
        <label>Day:</label>
        <select name="day">
            <option value="">Select Day</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
        </select>
        <button type="submit">Filter</button>
    </form>
    <table>
        <tr>
            <th>Faculty</th>
            <th>Course</th>
            <th>Subject</th>
            <th>Session</th>
            <th>Year</th>
            <th>Day</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Action</th>
        </tr>
        <?php foreach ($schedules as $schedule) { ?>
            <tr>
                <td><?= htmlspecialchars($schedule['faculty_name']); ?></td>
                <td><?= htmlspecialchars($schedule['course_name']); ?></td>
                <td><?= htmlspecialchars($schedule['subject_name']); ?></td>
                <td><?= htmlspecialchars($schedule['session_name']); ?></td>
                <td><?= htmlspecialchars($schedule['year_name']); ?></td>
                <td><?= htmlspecialchars($schedule['day']); ?></td>
                <td><?= htmlspecialchars($schedule['start_time']); ?></td>
                <td><?= htmlspecialchars($schedule['end_time']); ?></td>
                <td>
                    <a href="delete_schedule.php?id=<?= $schedule['id']; ?>" onclick="return confirm('Are you sure you want to delete this schedule?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>