<?php
session_start();
include '../config/db.php';
include '../includes/header.php'; 
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
     

    <!-- Modal Popup -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-body-content">
                    <!-- QR data will be displayed here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function onScanSuccess(qrCodeMessage) {
            document.getElementById('modal-body-content').innerHTML = `<pre>${qrCodeMessage}</pre>`;
            new bootstrap.Modal(document.getElementById('qrModal')).show();
        }
        function onScanError(errorMessage) {
            console.warn("QR Code parse error:", errorMessage);
        }
        var html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanError);

        document.getElementById('upload-btn').addEventListener('click', function () {
            let fileInput = document.getElementById('qr-file');
            let formData = new FormData();
            formData.append('qr_file', fileInput.files[0]);

            fetch('decode_qr.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('modal-body-content').innerHTML = `<pre>${data.qrData}</pre>`;
                    new bootstrap.Modal(document.getElementById('qrModal')).show();
                } else {
                    document.getElementById('qr-data').innerHTML = `<span class='error'>Error: ${data.message}</span>`;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>