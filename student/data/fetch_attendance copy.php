<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Access Denied. Students only."]);
        exit();
    }

    $student_id = $_SESSION['user_id'];
    $faculty_id = $data['faculty_id'] ?? null;
    $schedule_id = $data['schedule_id'] ?? null;
    $date = $data['date'] ?? date('Y-m-d');

    if (!$faculty_id || !$schedule_id) {
        echo json_encode(["status" => "error", "message" => "Invalid QR Code Data."]);
        exit();
    }

    // ✅ Check if attendance is already marked
    $stmt = $pdo->prepare("SELECT status FROM attendance WHERE student_id = ? AND schedule_id = ? AND date = ?");
    $stmt->execute([$student_id, $schedule_id, $date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(["status" => "success", "message" => "Attendance already marked as: " . $existing['status']]);
        exit();
    }

    // ✅ Insert attendance if not marked
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, faculty_id, schedule_id, date, status) VALUES (?, ?, ?, ?, ?)");
    $success = $stmt->execute([$student_id, $faculty_id, $schedule_id, $date, 'Present']);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "Attendance marked successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to mark attendance."]);
    }
    exit();
}
?>
