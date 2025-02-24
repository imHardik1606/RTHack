<?php
session_start();
include('../config/db.php'); // Database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if schedule ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $schedule_id = $_GET['id'];
    
    // Prepare delete query
    $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = ?");
    
    if ($stmt->execute([$schedule_id])) {
        $_SESSION['success_message'] = "Schedule deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete schedule.";
    }
} else {
    $_SESSION['error_message'] = "Invalid schedule ID.";
}

// Redirect back to schedule page
header("Location: show_schedule.php");
exit();
?>
