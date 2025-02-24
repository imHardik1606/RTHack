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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-2xl bg-white p-8 rounded-lg shadow-lg w-full">
        <h2 class="text-2xl font-semibold text-blue-600 border-b pb-2 mb-4">Personal Information</h2>
        <div class="mb-6">
            <p><strong class="text-gray-700">Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
            <p><strong class="text-gray-700">Date Of Birth:</strong> <?= htmlspecialchars($user['dob']); ?></p>
            <p><strong class="text-gray-700">Gender:</strong> <?= htmlspecialchars($user['gender']); ?></p>
            <p><strong class="text-gray-700">Mobile:</strong> <?= htmlspecialchars($user['mobile']); ?></p>
            <p><strong class="text-gray-700">Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="flex flex-col items-center mb-6">
            <img class="w-24 h-24 rounded-full border-4 border-blue-500" src="../uploads/<?= htmlspecialchars($user['profile_pic']); ?>" alt="<?= htmlspecialchars($user['name']); ?>">
            <h4 class="text-lg font-bold mt-2 uppercase text-gray-800"><?= strtoupper(htmlspecialchars($user['name'])); ?></h4>
        </div>
        <h2 class="text-2xl font-semibold text-blue-600 border-b pb-2 mb-4">Address Information</h2>
        <div class="mb-6">
            <p><strong class="text-gray-700">Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
            <p><strong class="text-gray-700">District:</strong> <?= htmlspecialchars($user['district']); ?></p>
            <p><strong class="text-gray-700">City/Taluk:</strong> <?= htmlspecialchars($user['city']); ?></p>
            <p><strong class="text-gray-700">State:</strong> <?= htmlspecialchars($user['state']); ?></p>
            <p><strong class="text-gray-700">Pin Code:</strong> <?= htmlspecialchars($user['pincode']); ?></p>
            <p><strong class="text-gray-700">Country:</strong> <?= htmlspecialchars($user['country']); ?></p>
        </div>
        <h2 class="text-2xl font-semibold text-blue-600 border-b pb-2 mb-4">Parents Information</h2>
        <div>
            <p><strong class="text-gray-700">Father's Name:</strong> <?= htmlspecialchars($user['father_name']); ?></p>
            <p><strong class="text-gray-700">Mother's Name:</strong> <?= htmlspecialchars($user['mother_name']); ?></p>
            <p><strong class="text-gray-700">Mobile:</strong> <?= htmlspecialchars($user['parent_mobile']); ?></p>
            <p><strong class="text-gray-700">Email:</strong> <?= htmlspecialchars($user['parent_email']); ?></p>
        </div>
    </div>
</body>
</html>
