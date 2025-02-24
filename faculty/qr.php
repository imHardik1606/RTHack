<?php
session_start();
include '../config/db.php';
include '../includes/header.php'; 
include 'phpqrcode/qrlib.php'; // QR Code Library

if (!isset($_SESSION['faculty_id']) || $_SESSION['role'] !== 'faculty') {
    die("Access Denied. Faculty only.");
}

$faculty_id = $_SESSION['faculty_id'];
$today_date = date("Y-m-d"); // Get today's date

// Fetch schedules assigned to the logged-in faculty
$stmt = $pdo->prepare("SELECT s.id, s.day, s.start_time, s.end_time, 
                              s.course_id, s.subject_id, sub.subject_name, 
                              c.course_name, y.year_name, ses.session_name 
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
    <title>Generate QR Code</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>📌 Generate QR for Attendance</h2>
        <form method="POST">
            <label class="form-label">Select Class:</label>
            <select name="schedule_id" class="form-control" required>
                <option value="">-- Select Class --</option>
                <?php foreach ($schedules as $schedule) { ?>
                    <option value="<?= $schedule['id']; ?>" 
                            data-course-id="<?= $schedule['course_id']; ?>" 
                            data-subject-id="<?= $schedule['subject_id']; ?>">
                        <?= htmlspecialchars($schedule['subject_name']); ?> 
                        (<?= htmlspecialchars($schedule['course_name']); ?> - <?= htmlspecialchars($schedule['year_name']); ?> - <?= htmlspecialchars($schedule['session_name']); ?>) 
                        [<?= htmlspecialchars($schedule['day']); ?> - <?= htmlspecialchars($schedule['start_time']); ?> to <?= htmlspecialchars($schedule['end_time']); ?>]
                    </option>
                <?php } ?>
            </select>
            <button type="submit" name="generate_qr" class="btn btn-primary mt-3">📸 Generate QR Code</button>
        </form>

        <?php
        if (isset($_POST['generate_qr'])) {
            $schedule_id = $_POST['schedule_id'];

            // Fetch schedule details
            $stmt = $pdo->prepare("SELECT s.course_id, s.subject_id, sub.subject_name, s.start_time 
                                   FROM schedule s
                                   JOIN subjects sub ON s.subject_id = sub.id
                                   WHERE s.id = ? AND s.faculty_id = ?");
            $stmt->execute([$schedule_id, $faculty_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($schedule) {
                $course_id = $schedule['course_id'];
                $subject_id = $schedule['subject_id'];
                $subject_name = $schedule['subject_name'];
                $time = $schedule['start_time'];

                // QR Code Data (Includes subject_id)
                $qr_data = json_encode([
                    'faculty_id' => $faculty_id,
                    'schedule_id' => $schedule_id,
                    'course_id' => $course_id,
                    'subject_id' => $subject_id,
                    'date' => $today_date,
                    'time' => $time
                ]);

                // QR Code File Path
                $qr_file = 'qrcodes/attendance_' . $faculty_id . '_' . $schedule_id . '.png';

                // Generate QR Code
                QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 6);

                // Display QR Code & Download Option
                echo "<h3>QR Code for $subject_name ($today_date - $time)</h3>";
                echo "<img src='$qr_file' style='width: 200px; height: 200px;'><br><br>";
                echo "<a href='$qr_file' download='attendance_qr.png' class='btn btn-success'>Download QR Code</a>";
            } else {
                echo "<p class='text-danger'>Invalid Schedule Selection</p>";
            }
        }
        ?>
    </div>
</body>
</html>
