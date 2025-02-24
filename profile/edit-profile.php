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

    $update_stmt = $pdo->prepare("UPDATE students SET name = ?, dob = ?, gender = ?, mobile = ?, email = ? WHERE id = ?");

    if ($update_stmt->execute([$name, $dob, $gender, $mobile, $email, $user_id])) {
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    
    <!-- Container -->
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6 mt-10">

        <h3 class="text-2xl font-semibold border-b-2 border-blue-500 pb-2 mb-4">Edit Profile</h3>

        <!-- Success or Error Message -->
        <?php if (isset($_SESSION['success_message'])) { ?>
            <div class="bg-green-100 text-green-700 p-3 mb-4 rounded-md">
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php } ?>
        <?php if (isset($error_message)) { ?>
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded-md">
                <?= $error_message; ?>
            </div>
        <?php } ?>

        <!-- Profile Form -->
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Date of Birth:</label>
                    <input type="date" name="dob" value="<?= htmlspecialchars($user['dob']); ?>" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 font-medium">Gender:</label>
                    <select name="gender" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Mobile:</label>
                    <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']); ?>" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-gray-700 font-medium">Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="profile.php" class="text-blue-600 hover:underline">Back to Profile</a>
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition">Update Profile</button>
            </div>
        </form>

    </div>

</body>
</html>
