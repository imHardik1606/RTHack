<?php
// Load configuration and database connection
// require_once __DIR__ . '/config.php';

// Start session if not already started
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// **Helper Function: Redirect**
// function redirect($url) {
//     header("Location: " . APP_URL . '/' . ltrim($url, '/'));
//     exit;
// }

// // **Helper Function: Sanitize Input**
// function sanitize($input) {
//     return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
// }

// // **Helper Function: Check Authentication**
// function isAuthenticated() {
//     return isset($_SESSION['user_id']);
// }

// // **Helper Function: Require Login**
// function requireLogin() {
//     if (!isAuthenticated()) {
//         redirect('login.php');
//     }
// }

// // **Helper Function: Flash Message**
// function setFlash($message, $type = 'success') {
//     $_SESSION['flash'] = ['message' => $message, 'type' => $type];
// }

// function getFlash() {
//     if (isset($_SESSION['flash'])) {
//         $flash = $_SESSION['flash'];
//         unset($_SESSION['flash']);
//         return "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
//     }
//     return '';
// }
?>
