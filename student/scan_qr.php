<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied. Students only.");
}

$student_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen p-4">
    <div class="max-w-lg w-full bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">📸 Scan QR Code</h2>

        <div id="qr-reader" class="border-2 border-blue-500 p-4 rounded-lg flex justify-center"></div>

        <div id="qr-data" class="mt-4 text-center text-gray-600 bg-gray-200 p-3 rounded-md">
            No QR data detected.
        </div>

        <button id="mark-attendance-btn" class="w-full bg-green-500 text-white py-2 mt-4 rounded-lg font-semibold hidden transition-opacity duration-300 hover:bg-green-600">
            Mark Attendance
        </button>
    </div>

    <script>
        let scannedData = null;

        function onScanSuccess(qrCodeMessage) {
            try {
                scannedData = JSON.parse(qrCodeMessage);
                if (!scannedData.faculty_id || !scannedData.schedule_id || !scannedData.subject_id || !scannedData.date) {
                    throw new Error("Incomplete QR Code Data");
                }
            } catch (error) {
                document.getElementById('qr-data').innerHTML = `<div class='text-red-600 font-semibold'>⚠️ Invalid QR Code: ${error.message}</div>`;
                scannedData = null;
                return;
            }

            document.getElementById('qr-data').innerHTML = `
                <div class='text-green-600 font-semibold p-2 bg-green-100 rounded-md'>
                    ✅ QR Code Extracted Successfully!<br>
                    <strong>Faculty ID:</strong> ${scannedData.faculty_id}<br>
                    <strong>Schedule ID:</strong> ${scannedData.schedule_id}<br>
                    <strong>Subject ID:</strong> ${scannedData.subject_id}<br>
                    <strong>Date:</strong> ${scannedData.date}
                </div>`;

            document.getElementById('mark-attendance-btn').classList.remove("hidden");
        }

        function onScanError(errorMessage) {
            console.warn("QR Code parse error:", errorMessage);
        }

        var html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanError);

        document.getElementById('mark-attendance-btn').addEventListener('click', function() {
            if (!scannedData) {
                alert("No valid QR data available.");
                return;
            }

            fetch('fetch_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    student_id: <?= $student_id; ?>,
                    faculty_id: scannedData.faculty_id,
                    schedule_id: scannedData.schedule_id,
                    subject_id: scannedData.subject_id,
                    date: scannedData.date
                })
            })
            .then(response => response.json())
            .then(data => {
                let alertClass = data.status === 'success' ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';
                document.getElementById('qr-data').innerHTML += `<div class='p-2 rounded-md mt-2 ${alertClass}'>${data.message}</div>`;
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>
