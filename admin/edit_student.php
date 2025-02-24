<?php
session_start();
include('../config/db.php');
include('../includes/header.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if student ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_students.php");
    exit();
}

$student_id = $_GET['id'];
$student = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student->execute([$student_id]);
$student = $student->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: manage_students.php");
    exit();
}

// Fetch courses, years (semesters), and sessions
$courses = $pdo->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$years = $pdo->query("SELECT id, year_name FROM years")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT id, session_name FROM sessions")->fetchAll(PDO::FETCH_ASSOC);

// Handle student update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $session_id = $_POST['session_id'];
    
    $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, course_id = ?, year_id = ?, session_id = ? WHERE id = ?");
    if ($stmt->execute([$name, $email, $course_id, $year_id, $session_id, $student_id])) {
        $message = "Student updated successfully!";
    } else {
        $message = "Error updating student.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
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
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        p {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body><br><br><br><br>
    <center><div class="container">
        <h2><i class="fas fa-edit"></i> Edit Student</h2>
        <?php if (isset($message)) echo "<p>$message</p>"; ?>
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo $student['name']; ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
            
            <label>Course:</label>
            <select name="course_id" required>
                <?php foreach ($courses as $course) {
                    $selected = ($course['id'] == $student['course_id']) ? 'selected' : '';
                    echo "<option value='{$course['id']}' $selected>{$course['course_name']}</option>";
                } ?>
            </select>
            
            <label>Year (Semester):</label>
            <select name="year_id" required>
                <?php foreach ($years as $year) {
                    $selected = ($year['id'] == $student['year_id']) ? 'selected' : '';
                    echo "<option value='{$year['id']}' $selected>{$year['year_name']}</option>";
                } ?>
            </select>
            
            <label>Session:</label>
            <select name="session_id" required>
                <?php foreach ($sessions as $session) {
                    $selected = ($session['id'] == $student['session_id']) ? 'selected' : '';
                    echo "<option value='{$session['id']}' $selected>{$session['session_name']}</option>";
                } ?>
            </select>
            
            <button type="submit"><i class="fas fa-save"></i> Update Student</button>
        </form>
    </div></center>
</body>
</html>
