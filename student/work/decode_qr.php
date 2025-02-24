<?php
session_start();
include '../config/db.php';
include '../includes/header.php'; 

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
        curl_close($curl);

        $result = json_decode($response, true);

        if (!empty($result) && isset($result[0]['symbol'][0]['data'])) {
            $decodedText = $result[0]['symbol'][0]['data'];

            echo json_encode(["status" => "success", "qrData" => $decodedText]);
        } else {
            echo json_encode(["status" => "error", "message" => "No QR code detected."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed."]);
    }
    exit();
}
?>
