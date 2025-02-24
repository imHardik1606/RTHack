<?php
session_start();
include '../config/db.php';

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

// Get JSON input from request
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['qr_data']) || empty($data['qr_data'])) {
    echo json_encode(["status" => "error", "message" => "QR Code data is missing"]);
    exit();
}

// Extract data from QR Code (format: schedule_id|faculty_id|date)
$qr_data_parts = explode('|', $data['qr_data']);
if (count($qr_data_parts) !== 3) {
    echo json_encode(["status" => "error", "message" => "Invalid QR Code format"]);
    exit();
}

$schedule_id = $qr_data_parts[0];
$faculty_id = $qr_data_parts[1];
$date = $qr_data_parts[2];

// Ensure student is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$student_id = $_SESSION['student_id'];

// Check if QR code is for today's date
$today = date('Y-m-d');
if ($date !== $today) {
    echo json_encode(["status" => "error", "message" => "QR Code is not for today"]);
    exit();
}

// Validate the schedule from database
$stmt = $pdo->prepare("SELECT * FROM schedule WHERE id = ? AND faculty_id = ?");
$stmt->execute([$schedule_id, $faculty_id]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    echo json_encode(["status" => "error", "message" => "Invalid schedule"]);
    exit();
}

// Extract subject, course, year, session from schedule
$subject_id = $schedule['subject_id'];
$course_id = $schedule['course_id'];
$year_id = $schedule['year_id'];
$session_id = $schedule['session_id'];

// Check if student already marked attendance
$checkStmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND schedule_id = ? AND date = ?");
$checkStmt->execute([$student_id, $schedule_id, $today]);

if ($checkStmt->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "Attendance already marked"]);
    exit();
}

// Insert attendance into the database
$insertStmt = $pdo->prepare("INSERT INTO attendance (student_id, schedule_id, faculty_id, subjects_id, course_id, year_id, session_id, date, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Present')");
$insertStmt->execute([$student_id, $schedule_id, $faculty_id, $subject_id, $course_id, $year_id, $session_id, $today]);

echo json_encode(["status" => "success", "message" => "Attendance marked successfully"]);
?>
