<?php
require('../config/db.php');
require('libs/tcpdf.php'); // Include TCPDF library

// Fetch filters
$selectedCourse = $_GET['course'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$selectedSession = $_GET['session'] ?? '';

// Validate input
if (!$selectedCourse || !$selectedYear || !$selectedSession) {
    die("Invalid filters selected!");
}

// Fetch course, year, and session details
$courseName = $pdo->query("SELECT course_name FROM courses WHERE id = $selectedCourse")->fetchColumn();
$yearName = $pdo->query("SELECT year_name FROM years WHERE id = $selectedYear")->fetchColumn();
$sessionName = $pdo->query("SELECT session_name FROM sessions WHERE id = $selectedSession")->fetchColumn();

// Fetch attendance summary
$query = "SELECT s.id AS student_id, s.name AS student_name,
                 ROUND(AVG(a.attendance_percentage), 2) AS avg_attendance
          FROM (
              SELECT student_id, 
                     (SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) / COUNT(id)) * 100 AS attendance_percentage
              FROM attendance
              WHERE student_id IN (SELECT id FROM students WHERE course_id = ? AND year_id = ? AND session_id = ?)
              GROUP BY student_id, schedule_id
          ) a
          JOIN students s ON s.id = a.student_id
          GROUP BY s.id";
$stmt = $pdo->prepare($query);
$stmt->execute([$selectedCourse, $selectedYear, $selectedSession]);
$attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create new PDF document
$pdf = new TCPDF();
$pdf->SetTitle('Attendance Report');
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);

// Report Header
$pdf->Cell(0, 10, "Attendance Report - $courseName ($yearName, $sessionName)", 0, 1, 'C');

// Table Header
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(40, 10, 'Student ID', 1);
$pdf->Cell(80, 10, 'Student Name', 1);
$pdf->Cell(40, 10, 'Avg Attendance (%)', 1);
$pdf->Ln();

// Table Data
$pdf->SetFont('Helvetica', '', 10);
foreach ($attendanceData as $row) {
    $pdf->Cell(40, 10, $row['student_id'], 1);
    $pdf->Cell(80, 10, $row['student_name'], 1);
    $pdf->Cell(40, 10, $row['avg_attendance'] . '%', 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('attendance_report.pdf', 'D');
header("Location: report.php"); 
?>
