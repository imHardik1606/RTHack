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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

<div class="w-full max-w-md bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-center text-gray-700 mb-4">Register</h2>

    <?php if (!empty($error)) { ?>
        <p class="text-red-500 text-sm mb-4"> <?= $error; ?> </p>
    <?php } ?>
    
    <?php if (!empty($success)) { ?>
        <p class="text-green-500 text-sm mb-4"> <?= $success; ?> </p>
    <?php } ?>

    <form method="POST" onsubmit="return validateForm()">
        <div class="mb-4">
            <label class="block text-gray-600 font-medium" for="name">Full Name</label>
            <input type="text" name="name" id="name" class="w-full mt-1 p-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-600 font-medium" for="email">Email</label>
            <input type="email" name="email" id="email" class="w-full mt-1 p-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-600 font-medium" for="password">Password</label>
            <input type="password" name="password" id="password" class="w-full mt-1 p-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-600 font-medium" for="course">Course</label>
            <select name="course" id="course" class="w-full mt-1 p-2 border rounded-md" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course) { ?>
                    <option value="<?= $course['id']; ?>"> <?= $course['course_name']; ?> </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-600 font-medium" for="session">Session</label>
            <select name="session" id="session" class="w-full mt-1 p-2 border rounded-md" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $session) { ?>
                    <option value="<?= $session['id']; ?>"> <?= $session['session_name']; ?> </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-600 font-medium" for="year">Year</label>
            <select name="year" id="year" class="w-full mt-1 p-2 border rounded-md" required>
                <option value="">Select Year</option>
                <?php foreach ($years as $year) { ?>
                    <option value="<?= $year['id']; ?>"> <?= $year['year']; ?> </option>
                <?php } ?>
            </select>
        </div>
        
        <div class="g-recaptcha mb-4" data-sitekey="6LcKQd8qAAAAAMKu2MIUQD22IxoIVVwZ1npIMnxQ"></div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Register</button>
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

