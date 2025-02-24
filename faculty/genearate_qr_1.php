<?php
session_start();
include '../config/db.php';
include '../includes/header.php'; 

if (!isset($_SESSION['faculty_id']) || $_SESSION['role'] !== 'faculty') {
    die("<p class='text-red-500 text-center font-semibold'>Access Denied. Faculty only.</p>");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch schedules assigned to the logged-in faculty
$stmt = $pdo->prepare("SELECT s.id, s.day, s.start_time, s.end_time, 
                              sub.subject_name, c.course_name, y.year_name, ses.session_name 
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
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script> <!-- QR Library -->
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CSS -->
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-700 mb-4">📌 Generate QR & Mark Attendance</h2>

        <!-- Step 1: Select Schedule & Date -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-1">Select Class:</label>
            <select name="schedule_id" id="schedule" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                <option value="">-- Select Class --</option>
                <?php foreach ($schedules as $schedule) { ?>
                    <option value="<?= $schedule['id']; ?>">
                        <?= htmlspecialchars($schedule['subject_name']); ?> 
                        (<?= htmlspecialchars($schedule['course_name']); ?> - <?= htmlspecialchars($schedule['year_name']); ?> - <?= htmlspecialchars($schedule['session_name']); ?>) 
                        [<?= htmlspecialchars($schedule['day']); ?> - <?= htmlspecialchars($schedule['start_time']); ?> to <?= htmlspecialchars($schedule['end_time']); ?>]
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-1">Select Date:</label>
            <input type="date" id="date" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" value="<?= date('Y-m-d'); ?>" required>
        </div>

        <!-- Step 2: Generate QR Code Button -->
        <button id="generateQR" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700 transition duration-200">📸 Generate QR Code</button>
        <div id="qrcode" class="flex justify-center my-4"></div>

        <!-- Step 3: Fetch Students for Manual Attendance -->
        <button id="fetchStudents" class="w-full bg-green-600 text-white font-semibold py-2 rounded-md hover:bg-green-700 transition duration-200" disabled>📋 Fetch Students</button>
        <div id="students-container" class="mt-4"></div>
    </div>

    <script>
        document.getElementById('generateQR').addEventListener('click', function () {
            let scheduleId = document.getElementById('schedule').value;
            let date = document.getElementById('date').value;
            let qrContainer = document.getElementById('qrcode');

            if (!scheduleId || !date) {
                alert("⚠️ Please select a class and date first!");
                return;
            }

            // Clear previous QR code
            qrContainer.innerHTML = "";

            // Generate QR Code data
            let qrData = JSON.stringify({
                faculty_id: <?= $faculty_id; ?>,
                schedule_id: scheduleId,
                date: date
            });

            new QRCode(qrContainer, {
                text: qrData,
                width: 200,
                height: 200
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
