<?php
include '../config/db.php';

if (isset($_GET['schedule_id'])) {
    $schedule_id = $_GET['schedule_id'];

    // Fetch schedule details to get course, year, and session
    $stmt = $pdo->prepare("SELECT course_id, year_id, session_id FROM schedule WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        echo "<p style='color:red;'>Invalid schedule selected.</p>";
        exit();
    }

    $course_id = $schedule['course_id'];
    $year_id = $schedule['year_id'];
    $session_id = $schedule['session_id'];

    // Fetch students matching the schedule's course, year, and session
    $stmt = $pdo->prepare("SELECT id, name FROM students 
                           WHERE course_id = ? AND year_id = ? AND session_id = ?");
    $stmt->execute([$course_id, $year_id, $session_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($students) > 0) {
        echo '<table class="table table-bordered">
                <tr>
                    <th>Student Name</th>
                    <th>Attendance</th>
                </tr>';
        foreach ($students as $student) {
            echo "<tr>
                    <td>{$student['name']}</td>
                    <td>
                        <label><input type='radio' name='attendance[{$student['id']}]' value='Present' required> Present</label>
                        <label><input type='radio' name='attendance[{$student['id']}]' value='Absent'> Absent</label>
                        <label><input type='radio' name='attendance[{$student['id']}]' value='Late'> Late</label>
                    </td>
                  </tr>";
        }
        echo '</table>';
    } else {
        echo "<p style='color:red;'>No students found for this schedule.</p>";
    }
}
?>
