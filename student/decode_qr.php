<?php 
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['qr_file'])) {
    $file = $_FILES['qr_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];

        // Send file to GoQR API
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.qrserver.com/v1/read-qr-code/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => new CURLFile($fileTmpPath)]
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code !== 200) {
            echo json_encode(["status" => "error", "message" => "QR API request failed."]);
            exit();
        }

        $result = json_decode($response, true);

        if (!empty($result) && isset($result[0]['symbol'][0]['data']) && !empty($result[0]['symbol'][0]['data'])) {
            $decodedText = $result[0]['symbol'][0]['data'];

            // Try to parse as JSON
            $qrData = json_decode($decodedText, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($qrData['faculty_id'], $qrData['schedule_id'], $qrData['date'])) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "QR Code extracted successfully!", 
                    "qrData" => $qrData
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid QR Code format."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No QR code detected."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed. Error code: " . $file['error']]);
    }
    exit();
}
?>