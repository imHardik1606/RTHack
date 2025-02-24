<?php
// define('APP_URL', 'http://localhost/attendance'); // Ensure APP_URL is set
?>

<footer>
    <div class="footer-container">
        <p>&copy; <?= date('Y'); ?> Attendance System. All rights reserved.</p>
        <div class="footer-links">
            <a href="<?= APP_URL; ?>/index.php">Home</a> |
            <a href="<?= APP_URL; ?>/about.php">About</a> |
            <a href="<?= APP_URL; ?>/contact.php">Contact</a>
        </div>
    </div>
</footer>

<style>
    footer {
        background: #0056b3;
        color: white;
        text-align: center;
        padding: 15px 0;
        margin-top: 20px;
        position: relative;
        bottom: 0;
        width: 100%;
    }

    .footer-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .footer-links a {
        color: white;
        text-decoration: none;
        margin: 0 8px;
        font-weight: bold;
    }

    .footer-links a:hover {
        text-decoration: underline;
    }
</style>
