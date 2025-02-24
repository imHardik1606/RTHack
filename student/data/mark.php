<?php
session_start();
include '../config/db.php';

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

    // Fetch student details
    $stmt = $pdo->prepare("SELECT  course_id, year_id, session_id FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(["status" => "error", "message" => "Student not found."]);
        exit();
    }

    // Check if attendance is already marked
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND schedule_id = ? AND date = ?");
    $stmt->execute([$student_id, $schedule_id, $date]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo json_encode(["status" => "error", "message" => "Attendance already marked."]);
        exit();
    }

    // Insert attendance record
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, course_id, year_id, session_id, faculty_id, schedule_id, date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$student_id,  $student['course_id'], $student['year_id'], $student['session_id'], $faculty_id, $schedule_id, $date, 'Present'])) {
        echo json_encode(["status" => "success", "message" => "Attendance marked successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to mark attendance."]);
    }
}
?>
