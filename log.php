<?php
function logActivity($message) {
    $role = $_SESSION['role'] ?? 'guest';
    $logDir = __DIR__ . "/logs/$role/";
    $logFile = $logDir . 'logs.txt';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['name'] ?? 'Guest';
    $page = basename($_SERVER['PHP_SELF']);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $url = $_SERVER['REQUEST_URI'] ?? 'Unknown URL';
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'No Referrer';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User-Agent';
    
    $logEntry = "[$timestamp] IP: $ipAddress - URL: $url - Referrer: $referrer - User-Agent: $userAgent - $user accessed $page - $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';

// Log the page visit
logActivity("Visited the header page.");
?>