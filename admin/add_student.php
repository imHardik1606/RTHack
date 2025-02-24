<?php
session_start();
include('../config/db.php');
include('../includes/header.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch courses, years (semesters), and sessions
$courses = $pdo->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$years = $pdo->query("SELECT id, year_name FROM years")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT id, session_name FROM sessions")->fetchAll(PDO::FETCH_ASSOC);

// Handle student addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $session_id = $_POST['session_id'];
    
    $stmt = $pdo->prepare("INSERT INTO students (name, email, password, course_id, year_id, session_id, role) VALUES (?, ?, ?, ?, ?, ?, 'student')");
    if ($stmt->execute([$name, $email, $password, $course_id, $year_id, $session_id])) {
        $message = "Student added successfully!";
    } else {
        $message = "Error adding student.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            /* display: flex; */
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 600px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            text-align: left;
            font-weight: 500;
            margin-top: 10px;
        }
        input, select {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            margin-top: 20px;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #218838;
        }
        p {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<center><body><br><br><br><br>
    <div class="container">
        <h2><i class="fas fa-user-plus"></i> Add Student</h2>
        <?php if (isset($message)) echo "<p>$message</p>"; ?>
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <label>Course:</label>
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course) {
                    echo "<option value='{$course['id']}'>{$course['course_name']}</option>";
                } ?>
            </select>
            
            <label>Year (Semester):</label>
            <select name="year_id" required>
                <option value="">Select Year</option>
                <?php foreach ($years as $year) {
                    echo "<option value='{$year['id']}'>{$year['year_name']}</option>";
                } ?>
            </select>
            
            <label>Session:</label>
            <select name="session_id" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $session) {
                    echo "<option value='{$session['id']}'>{$session['session_name']}</option>";
                } ?>
            </select>
            
            <button type="submit"><i class="fas fa-plus"></i> Add Student</button>
        </form>
    </div>
</body></center>
</html>