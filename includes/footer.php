<?php
// define('APP_URL', 'http://localhost/attendance'); // Ensure APP_URL is set
?>

<!-- Footer -->
<footer class="bg-blue-700 text-white text-center py-4 mt-6 w-full">
    <div class="container mx-auto flex flex-col items-center gap-2">
        <p>&copy; <?= date('Y'); ?> Attendance System. All rights reserved.</p>
        <div class="flex space-x-4">
            <a href="<?= APP_URL; ?>/index.php" class="hover:underline">Home</a>
            <span>|</span>
            <a href="<?= APP_URL; ?>/about.php" class="hover:underline">About</a>
            <span>|</span>
            <a href="<?= APP_URL; ?>/contact.php" class="hover:underline">Contact</a>
        </div>
    </div>
</footer>