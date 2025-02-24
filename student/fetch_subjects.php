<?php
include('../config/db.php');

$course_id = $_GET['course_id'] ?? '';
$year_id = $_GET['year_id'] ?? '';

if ($course_id && $year_id) {
    $stmt = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE course_id = ? AND year_id = ? ORDER BY subject_name ASC");
    $stmt->execute([$course_id, $year_id]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($subjects);
} else {
    echo json_encode([]);
}
?>
