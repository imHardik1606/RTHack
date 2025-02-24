<?php
session_start();
include('../config/db.php'); // Include database connection

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../student/dashboard.php");
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $course_id = trim($_POST['course']);
    $session_id = trim($_POST['session']);
    $year_id = trim($_POST['year']);

    if (!empty($name) && !empty($email) && !empty($password) && !empty($course_id) && !empty($session_id) && !empty($year_id)) {
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered. Please login.";
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO students (name, email, password, role, course_id, session_id, year_id) VALUES (?, ?, ?, 'student', ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $hashedPassword, $course_id, $session_id, $year_id])) {
                // Auto login after registration
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['name'] = $name;
                $_SESSION['role'] = 'student';

                // Redirect to dashboard
                header("Location: ../student/dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch courses, sessions, and years for dropdowns
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
$sessions = $pdo->query("SELECT * FROM sessions")->fetchAll();
$years = $pdo->query("SELECT * FROM years")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Attendance System</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            width: 400px;
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-weight: bold;
        }

        .input-group input,
        .input-group select {
            width: 90%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .register-btn {
            width: 100%;
            padding: 10px;
            background: #0056b3;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .register-btn:hover {
            background: #003d80;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Register</h2>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= $error; ?></p>
    <?php } ?>

    <?php if (!empty($success)) { ?>
        <p class="success"><?= $success; ?></p>
    <?php } ?>

    <form method="POST" onsubmit="return validateForm()">
        <div class="input-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" required>
        </div>

        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="input-group">
            <label for="course">Course</label>
            <select name="course" id="course" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course) { ?>
                    <option value="<?= $course['id']; ?>"><?= $course['course_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="input-group">
            <label for="session">Session</label>
            <select name="session" id="session" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $session) { ?>
                    <option value="<?= $session['id']; ?>"><?= $session['session_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="input-group">
            <label for="year">Year</label>
            <select name="year" id="year" required>
                <option value="">Select Year</option>
                <?php foreach ($years as $year) { ?>
                    <option value="<?= $year['id']; ?>"><?= $year['year']; ?></option>
                <?php } ?>
            </select>
        </div><br>
        <div class="g-recaptcha" data-sitekey="6LcKQd8qAAAAAMKu2MIUQD22IxoIVVwZ1npIMnxQ"></div><br>

        <button type="submit" class="register-btn">Register</button>
    </form>
</div>

<script>
    function validateForm() {
        let name = document.getElementById("name").value;
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;
        let course = document.getElementById("course").value;
        let session = document.getElementById("session").value;
        let year = document.getElementById("year").value;

        if (name.trim() === "" || email.trim() === "" || password.trim() === "" || 
            course === "" || session === "" || year === "") {
            alert("Please fill in all fields.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>
