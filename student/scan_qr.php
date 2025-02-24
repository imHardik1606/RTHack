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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        #qr-reader {
            width: 100%;
            border: 2px dashed #007bff;
            padding: 10px;
            text-align: center;
        }
        #qr-data {
            margin-top: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">📸 Scan QR Code</h2>
        <div id="qr-reader"></div>
        <div id="qr-data" class="mt-3 text-center">No QR data detected.</div>
        <button id="mark-attendance-btn" class="btn btn-success w-100 mt-3" style="display: none;">Mark Attendance</button>
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
                document.getElementById('qr-data').innerHTML = `<div class='alert alert-danger'>Invalid QR Code: ${error.message}</div>`;
                scannedData = null;
                return;
            }

            document.getElementById('qr-data').innerHTML = `
                <div class='alert alert-success'>
                    <strong>QR Code Extracted Successfully!</strong><br>
                    Faculty ID: ${scannedData.faculty_id}<br>
                    Schedule ID: ${scannedData.schedule_id}<br>
                    Subject ID: ${scannedData.subject_id}<br>
                    Date: ${scannedData.date}
                </div>`;
            
            document.getElementById('mark-attendance-btn').style.display = "block";
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
                let alertClass = data.status === 'success' ? 'alert-success' : 'alert-danger';
                document.getElementById('qr-data').innerHTML += `<div class='alert ${alertClass} mt-2'>${data.message}</div>`;
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
