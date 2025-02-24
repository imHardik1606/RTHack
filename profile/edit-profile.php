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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $dob = trim($_POST['dob']);
    $gender = trim($_POST['gender']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    $country = trim($_POST['country']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
    $parent_mobile = trim($_POST['parent_mobile']);
    $parent_email = trim($_POST['parent_email']);
    
    $update_stmt = $pdo->prepare("UPDATE students SET name = ?, dob = ?, gender = ?, mobile = ?, email = ?, address = ?, district = ?, city = ?, state = ?, pincode = ?, country = ?, father_name = ?, mother_name = ?, parent_mobile = ?, parent_email = ? WHERE id = ?");
    
    if ($update_stmt->execute([$name, $dob, $gender, $mobile, $email, $address, $district, $city, $state, $pincode, $country, $father_name, $mother_name, $parent_mobile, $parent_email, $user_id])) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: student_details.php");
        exit();
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success, .error {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success {
            background: #28a745;
            color: white;
        }
        .error {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Edit Profile</h3>
        <?php if (isset($_SESSION['success_message'])) { ?>
            <div class="success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php } ?>
        <?php if (isset($error_message)) { ?>
            <div class="error"><?= $error_message; ?></div>
        <?php } ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($user['dob']); ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <input type="text" name="gender" id="gender" value="<?= htmlspecialchars($user['gender']); ?>" required>
            </div>
            <div class="form-group">
                <label for="mobile">Mobile:</label>
                <input type="text" name="mobile" id="mobile" value="<?= htmlspecialchars($user['mobile']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
        <br>
        <a href="profile.php" class="btn">Back to Profile</a>
    </div>
</body>
</html>
