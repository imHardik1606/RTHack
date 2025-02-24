<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Access Denied. Students only.", "data_received" => $data]);
        exit();
    }

    $student_id = $_SESSION['user_id'];
    $faculty_id = $data['faculty_id'] ?? null;
    $schedule_id = $data['schedule_id'] ?? null;
    $date = $data['date'] ?? date('Y-m-d');

    if (!$faculty_id || !$schedule_id) {
        echo json_encode(["status" => "error", "message" => "Invalid QR Code Data.", "data_received" => $data]);
        exit();
    }

    // Fetch student details
    $stmt = $pdo->prepare("SELECT course_id, year_id, session_id FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(["status" => "error", "message" => "Student not found.", "data_received" => $data]);
        exit();
    }

    // Fetch subject ID from schedule
    $stmt = $pdo->prepare("SELECT subjects_id FROM schedule WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        echo json_encode(["status" => "error", "message" => "Schedule not found.", "data_received" => $data]);
        exit();
    }

    $subjects_id = $schedule['subjects_id'];

    // Check if attendance is already marked
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND schedule_id = ? AND date = ?");
    $stmt->execute([$student_id, $schedule_id, $date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(["status" => "error", "message" => "Attendance already marked.", "details" => $data, "data_received" => $data]);
        exit();
    }

    // Insert attendance record
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, course_id, year_id, session_id, faculty_id, schedule_id, subjects_id, date, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([
        $student_id,  
        $student['course_id'], 
        $student['year_id'], 
        $student['session_id'], 
        $faculty_id, 
        $schedule_id, 
        $subjects_id, 
        $date, 
        'Present'
    ]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "Attendance marked successfully.", "details" => [
            "student_id" => $student_id,
            "faculty_id" => $faculty_id,
            "schedule_id" => $schedule_id,
            "subjects_id" => $subjects_id,
            "date" => $date,
            "status" => "Present"
        ], "data_received" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to mark attendance.", "details" => $data, "data_received" => $data]);
    }
}