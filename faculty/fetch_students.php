<?php
include '../config/db.php';

if (isset($_GET['schedule_id'])) {
    $schedule_id = $_GET['schedule_id'];

    // Fetch schedule details to get course, year, and session
    $stmt = $pdo->prepare("SELECT course_id, year_id, session_id FROM schedule WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        echo "<p class='text-red-500 text-center font-semibold'>Invalid schedule selected.</p>";
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
        echo '<div class="overflow-x-auto mt-6">
                <table class="w-full border-collapse border border-gray-300 shadow-md rounded-lg">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-3 px-4 border-b">Student Name</th>
                            <th class="py-3 px-4 border-b">Attendance</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach ($students as $student) {
            echo "<tr class='hover:bg-gray-50'>
                    <td class='py-2 px-4 border-b text-center'>{$student['name']}</td>
                    <td class='py-2 px-4 border-b text-center'>
                        <label class='inline-flex items-center mx-2'>
                            <input type='radio' name='attendance[{$student['id']}]' value='Present' required class='form-radio text-green-500'>
                            <span class='ml-1 text-green-600'>Present</span>
                        </label>
                        <label class='inline-flex items-center mx-2'>
                            <input type='radio' name='attendance[{$student['id']}]' value='Absent' class='form-radio text-red-500'>
                            <span class='ml-1 text-red-600'>Absent</span>
                        </label>
                        <label class='inline-flex items-center mx-2'>
                            <input type='radio' name='attendance[{$student['id']}]' value='Late' class='form-radio text-yellow-500'>
                            <span class='ml-1 text-yellow-600'>Late</span>
                        </label>
                    </td>
                  </tr>";
        }
        echo '</tbody></table></div>';
    } else {
        echo "<p class='text-red-500 text-center font-semibold mt-4'>No students found for this schedule.</p>";
    }
}
?>
