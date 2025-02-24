<?php
session_start();
include '../config/db.php';
include '../includes/header.php'; 

if (!isset($_SESSION['faculty_id']) || $_SESSION['role'] !== 'faculty') {
    die("Access Denied. Faculty only.");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch schedules assigned to the logged-in faculty, including course_id
$stmt = $pdo->prepare("SELECT s.id, s.day, s.start_time, s.end_time, 
                              s.course_id, sub.subject_name, c.course_name, 
                              y.year_name, ses.session_name 
                       FROM schedule s
                       JOIN subjects sub ON s.subject_id = sub.id
                       JOIN courses c ON s.course_id = c.id
                       JOIN years y ON s.year_id = y.id
                       JOIN sessions ses ON s.session_id = ses.id
                       WHERE s.faculty_id = ?");
$stmt->execute([$faculty_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script> <!-- QR Library -->
</head>
<body>
    <div class="container mt-4">
        <h2>📌 Generate QR & Mark Attendance</h2>

        <!-- Step 1: Select Schedule & Date -->
        <div class="mb-3">
            <label class="form-label">Select Class:</label>
            <select name="schedule_id" id="schedule" class="form-control" required>
                <option value="">-- Select Class --</option>
                <?php foreach ($schedules as $schedule) { ?>
                    <option value="<?= $schedule['id']; ?>" data-course-id="<?= $schedule['course_id']; ?>">
                        <?= htmlspecialchars($schedule['subject_name']); ?> 
                        (<?= htmlspecialchars($schedule['course_name']); ?> - <?= htmlspecialchars($schedule['year_name']); ?> - <?= htmlspecialchars($schedule['session_name']); ?>) 
                        [<?= htmlspecialchars($schedule['day']); ?> - <?= htmlspecialchars($schedule['start_time']); ?> to <?= htmlspecialchars($schedule['end_time']); ?>]
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Date:</label>
            <input type="date" id="date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
        </div>

        <!-- Step 2: Generate QR Code Button -->
        <button id="generateQR" class="btn btn-primary mb-3">📸 Generate QR Code</button>
        <div id="qrcode"></div><br>

        <!-- Step 3: Fetch Students for Manual Attendance -->
        <button id="fetchStudents" class="btn btn-success mb-3" disabled>📋 Fetch Students</button>
        <div id="students-container"></div>
    </div>

    <script>
        document.getElementById('generateQR').addEventListener('click', function () {
            let scheduleSelect = document.getElementById('schedule');
            let scheduleId = scheduleSelect.value;
            let courseId = scheduleSelect.options[scheduleSelect.selectedIndex].getAttribute('data-course-id');
            let date = document.getElementById('date').value;
            let qrContainer = document.getElementById('qrcode');

            if (!scheduleId || !date) {
                alert("⚠️ Please select a class and date first!");
                return;
            }

            // Clear previous QR code
            qrContainer.innerHTML = "";

            // Generate QR Code data with course_id
            let qrData = JSON.stringify({
                faculty_id: <?= $faculty_id; ?>,
                schedule_id: scheduleId,
                course_id: courseId,
                date: date
            });

            new QRCode(qrContainer, {
                text: qrData,
                width: 256,
                height: 256
            });

            // Enable Fetch Students button
            document.getElementById('fetchStudents').disabled = false;
        });

        document.getElementById('fetchStudents').addEventListener('click', function () {
            let scheduleId = document.getElementById('schedule').value;
            let studentsContainer = document.getElementById('students-container');

            if (!scheduleId) {
                alert("⚠️ Please select a class first!");
                return;
            }

            fetch('fetch_students.php?schedule_id=' + scheduleId) 
            .then(response => response.text())
            .then(data => {
                studentsContainer.innerHTML = data;
            });
        });
    </script>
</body>
</html>
