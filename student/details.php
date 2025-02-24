<?php
session_start();
include('../config/db.php'); // Database connection
include('../includes/header.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* margin: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h3 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        .info, .address, .parents {
            margin-bottom: 20px;
        }
        .profile-pic {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 2px solid #007bff;
        }
        strong {
            color: #333;
        }
    </style>
</head>
<body><br><br>
    <div class="container">
        <h3>Personal Information</h3><br>
        <div class="info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
            <p><strong>Date Of Birth:</strong> <?= htmlspecialchars($user['dob']); ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($user['gender']); ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="profile-pic">
            <img src="../uploads/<?= htmlspecialchars($user['profile_pic']); ?>" alt="<?= htmlspecialchars($user['name']); ?>">
            <h4><?= strtoupper(htmlspecialchars($user['name'])); ?></h4>
        </div>

        <h3>Address Information</h3><br>
        <div class="address">
            <p><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
            <p><strong>District:</strong> <?= htmlspecialchars($user['district']); ?></p>
            <p><strong>City/Taluk:</strong> <?= htmlspecialchars($user['city']); ?></p>
            <p><strong>State:</strong> <?= htmlspecialchars($user['state']); ?></p>
            <p><strong>Pin Code:</strong> <?= htmlspecialchars($user['pincode']); ?></p>
            <p><strong>Country:</strong> <?= htmlspecialchars($user['country']); ?></p>
        </div>

        <h3>Parents Information</h3><br>
        <div class="parents">
            <p><strong>Father's Name:</strong> <?= htmlspecialchars($user['father_name']); ?></p>
            <p><strong>Mother's Name:</strong> <?= htmlspecialchars($user['mother_name']); ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['parent_mobile']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['parent_email']); ?></p>
        </div>
    </div>
</body>
</html>
