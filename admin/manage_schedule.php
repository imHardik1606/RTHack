<?php
session_start();
include('../config/db.php');
include('../includes/header.php'); // Database connection

// Ensure user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch faculty members from students table where role is 'faculty'
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

// Handle schedule assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_id = $_POST['faculty_id'];
    $course_id = $_POST['course_id'];
    $subject_id = $_POST['subject_id'];
    $session_id = $_POST['session_id'];
    $year_id = $_POST['year_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $pdo->prepare("INSERT INTO schedule (faculty_id, course_id, subject_id, session_id, year_id, day, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$faculty_id, $course_id, $subject_id, $session_id, $year_id, $day, $start_time, $end_time]);

    echo "<p class='success'>Schedule added successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* margin: 20px; */
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        select, input[type="time"], button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #218838;
        }
        .success {
            color: green;
            text-align: center;
        }
    </style>
    <script>
        function filterSubjectsByCourse() {
            let courseSelect = document.querySelector("select[name='course_id']");
            let subjectSelect = document.querySelector("select[name='subject_id']");
            let subjects = JSON.parse('<?= json_encode($subjects) ?>');
            
            subjectSelect.innerHTML = "";
            subjects.forEach(subject => {
                if (subject.course_id == courseSelect.value) {
                    let option = document.createElement("option");
                    option.value = subject.id;
                    option.textContent = subject.subject_name;
                    subjectSelect.appendChild(option);
                }
            });
        }
    </script>
</head>
<body>
    
    <form method="POST">
        <label>Faculty:</label>
        <select name="faculty_id" required>
            <?php foreach ($faculties as $faculty) { ?>
                <option value="<?= $faculty['id']; ?>"><?= htmlspecialchars($faculty['name']); ?></option>
            <?php } ?>
        </select>

        <label>Course:</label>
        <select name="course_id" required onchange="filterSubjectsByCourse()">
            <?php foreach ($courses as $course) { ?>
                <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
            <?php } ?>
        </select>

        <label>Subject:</label>
        <select name="subject_id" required>
            <!-- Subjects will be filtered dynamically based on the selected course -->
        </select>

        <label>Session:</label>
        <select name="session_id" required>
            <?php foreach ($sessions as $session) { ?>
                <option value="<?= $session['id']; ?>"><?= htmlspecialchars($session['session_name']); ?></option>
            <?php } ?>
        </select>

        <label>Year:</label>
        <select name="year_id" required>
            <?php foreach ($years as $year) { ?>
                <option value="<?= $year['id']; ?>"><?= htmlspecialchars($year['year_name']); ?></option>
            <?php } ?>
        </select>

        <label>Day:</label>
        <select name="day" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
        </select>

        <label>Start Time:</label>
        <input type="time" name="start_time" required>
        
        <label>End Time:</label>
        <input type="time" name="end_time" required>

        <button type="submit">Assign Schedule</button>
    </form>
</body>
</html>
