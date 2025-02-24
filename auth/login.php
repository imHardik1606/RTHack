<?php
session_start();
include('../config/db.php'); // Include database connection

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'faculty') {
        header("Location: ../faculty/dashboard.php");
        exit();
    } else {
        header("Location: ../student/dashboard.php");
        exit();
    }
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // If the user is faculty, store faculty_id separately
            if ($user['role'] == 'faculty') {
                $_SESSION['faculty_id'] = $user['id']; // Assign faculty_id same as user_id
                header("Location: ../faculty/dashboard.php"); 
                exit();
            } elseif ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } elseif ($user['role'] == 'student') {
                header("Location: ../student/dashboard.php");
                exit();
            }else {
                header("Location: ../auth/login.php");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
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

        .login-container {
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            width: 350px;
            text-align: center;
        }

        .login-container h2 {
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

        .input-group input {
            width: 90%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            background: #0056b3;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-btn:hover {
            background: #003d80;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= $error; ?></p>
    <?php } ?>

    <form method="POST" onsubmit="return validateForm()">
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="g-recaptcha" data-sitekey="6LcKQd8qAAAAAMKu2MIUQD22IxoIVVwZ1npIMnxQ"></div><br>
        <button type="submit" class="login-btn">Login</button>
    </form>
</div>

<script>
    function validateForm() {
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;
        if (email.trim() === "" || password.trim() === "") {
            alert("Please fill in all fields.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>
