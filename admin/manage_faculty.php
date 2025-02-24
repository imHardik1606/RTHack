<?php
session_start();
include('../config/db.php');
include('../includes/header.php');  // Database connection

// Redirect non-admin users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle adding a new faculty member
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_faculty'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    
    if (!empty($name) && !empty($email) && !empty($_POST['password'])) {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, password, role) VALUES (?, ?, ?, 'faculty')");
        if ($stmt->execute([$name, $email, $password])) {
            $success = "Faculty member added successfully!";
        } else {
            $error = "Error adding faculty member.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle faculty deletion
if (isset($_GET['delete'])) {
    $faculty_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ? AND role = 'faculty'");
    if ($stmt->execute([$faculty_id])) {
        $success = "Faculty member deleted.";
    } else {
        $error = "Error deleting faculty.";
    }
}

// Fetch all faculty members
$stmt = $pdo->prepare("SELECT id, name, email FROM students WHERE role = 'faculty'");
$stmt->execute();
$faculty_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            /* padding: 20px; */
        }
        
        .container {
            width: 90%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .success, .error {
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .faculty-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .faculty-table th, .faculty-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .faculty-table th {
            background: #0056b3;
            color: white;
        }

        .action-links a {
            margin-right: 10px;
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .add-form {
            background: #f9f9f9;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .add-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .add-btn {
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-btn:hover {
            background: #218838;
        }

        .back-btn {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #0056b3;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Faculty</h2>

    <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <table class="faculty-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Faculty Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faculty_members as $faculty) { ?>
                <tr>
                    <td><?= htmlspecialchars($faculty['id']); ?></td>
                    <td><?= htmlspecialchars($faculty['name']); ?></td>
                    <td><?= htmlspecialchars($faculty['email']); ?></td>
                    <td class="action-links">
                        <a href="manage_faculty.php?delete=<?= $faculty['id']; ?>" onclick="return confirm('Are you sure?')">❌ Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="add-form">
        <h3>Add Faculty Member</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_faculty" class="add-btn">Add Faculty</button>
        </form>
    </div>

    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>

</body>
</html>
