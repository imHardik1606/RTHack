<?php
session_start();
include '../config/db.php';
include '../includes/header.php'; // Include header if required
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    die("Access Denied. Faculty only.");
}

$faculty_id = $_SESSION['user_id'];

// Fetch schedules assigned to the faculty
$stmt = $pdo->prepare("SELECT s.*, sub.subject_name, c.course_name, y.year_name, ses.session_name 
                       FROM schedule s
                       JOIN subjects sub ON s.subject_id = sub.id
                       JOIN courses c ON s.course_id = c.id
                       JOIN years y ON s.year_id = y.id
                       JOIN sessions ses ON s.session_id = ses.id
                       WHERE s.faculty_id = ?");
$stmt->execute([$faculty_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $schedule_id = $_POST['schedule_id'];
    $date = $_POST['date'];

    // Fetch schedule details
    $stmt = $pdo->prepare("SELECT faculty_id, subject_id, course_id, year_id, session_id 
                           FROM schedule WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        echo "<p style='color:red;'>Invalid schedule selected.</p>";
        exit();
    }

    $faculty_id = $schedule['faculty_id'];
    $subject_id = $schedule['subject_id'];
    $course_id = $schedule['course_id'];
    $year_id = $schedule['year_id'];
    $session_id = $schedule['session_id'];

    if (empty($schedule_id) || empty($date)) {
        echo "<p style='color:red;'>Please select a schedule and date.</p>";
    } else {
        foreach ($_POST['attendance'] as $student_id => $status) {
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, schedule_id, faculty_id, subjects_id, course_id, year_id, session_id, date, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE status = VALUES(status)");
            $stmt->execute([$student_id, $schedule_id, $faculty_id, $subject_id, $course_id, $year_id, $session_id, $date, $status]);

            // Fetch student email
            $stmt = $pdo->prepare("SELECT email FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student && !empty($student['email'])) {
                $email = $student['email'];
                $subject = "Attendance Marked Notification";
                $message = "Dear Student,\n\nYour attendance for the subject " . htmlspecialchars($schedule['subject_id']) . " on " . $date . " has been marked as: " . $status . ".\n\nBest Regards,\nFaculty";
                
                // Send email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Update SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'yashdoifode1439@gmail.com'; // Update sender email
                    $mail->Password = 'mvub juzg shso fhpa'; // Update password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('your-email@example.com', 'Faculty');
                    $mail->addAddress($email);
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mail could not be sent to $email: {$mail->ErrorInfo}");
                }
            }
        }
        echo "<p style='color:green;'>✅ Attendance marked successfully! Emails sent.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>📌 Mark Attendance</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Select Class:</label>
                <select name="schedule_id" id="schedule" class="form-control" required>
                    <option value="">-- Select Class --</option>
                    <?php foreach ($schedules as $schedule) { ?>
                        <option value="<?= $schedule['id']; ?>">
                            <?= htmlspecialchars($schedule['subject_name']); ?> (<?= htmlspecialchars($schedule['course_name']); ?> - <?= htmlspecialchars($schedule['year_name']); ?> - <?= htmlspecialchars($schedule['session_name']); ?>) 
                            [<?= htmlspecialchars($schedule['day']); ?> - <?= htmlspecialchars($schedule['start_time']); ?> to <?= htmlspecialchars($schedule['end_time']); ?>]
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Date:</label>
                <input type="date" name="date" class="form-control" required>
            </div>

            <div id="students-container">
                <!-- Students will be loaded here via AJAX -->
            </div>

            <button type="submit" class="btn btn-success">✅ Submit Attendance</button>
        </form>
    </div>

    <script>
        document.getElementById('schedule').addEventListener('change', function () {
            let scheduleId = this.value;
            let studentsContainer = document.getElementById('students-container');

            if (scheduleId) {
                fetch('fetch_students.php?schedule_id=' + scheduleId) // Fixed the file name
                .then(response => response.text())
                .then(data => {
                    studentsContainer.innerHTML = data;
                });
            } else {
                studentsContainer.innerHTML = '';
            }
        });
    </script>
</body>
</html>
