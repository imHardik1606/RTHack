<?php
include "config.php";
// Database Configuration
define('DB_HOST', 'localhost'); // Change if using a remote DB
define('DB_NAME', 'attendance'); // Change to your database name
define('DB_USER', 'root'); // Change to your database user
define('DB_PASS', ''); // Change to your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enables error reporting
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch data as an associative array
        PDO::ATTR_EMULATE_PREPARES => false // Use real prepared statements
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
