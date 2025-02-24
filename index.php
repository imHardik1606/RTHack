<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Attendance System - Home</title>
</head>
<body class="bg-gray-100">

<!-- Navigation Bar -->
<nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <div class="flex items-center space-x-2">
        <img src="assets/logo.svg" alt="Logo" class="w-10 h-10">
        <h1 class="text-xl font-bold text-gray-800">TTENDEASE</h1>
    </div>
    <ul class="flex space-x-6 text-gray-700 font-medium">
        <li><a href="#" class="hover:text-blue-500">Home</a></li>
        <li><a href="#" class="hover:text-blue-500">About us</a></li>
        <li><a href="#" class="hover:text-blue-500">Contact</a></li>
        <li><a href="#" class="hover:text-blue-500">Login/SignUp</a></li>
    </ul>
</nav>

<!-- Hero Section -->
<div class="flex items-center justify-center h-screen">
    <div class="bg-white p-8 shadow-lg rounded-lg w-96">
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-4">LOGIN PAGE</h2>
        <form action="auth/login.php" method="POST" class="space-y-4">
            <input type="email" name="email" placeholder="Enter Your College Mail Id" required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="password" name="password" placeholder="Password" required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            <!-- <select name="user_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="student">Student</option>
                <option value="teacher">Faculty</option>
            </select> -->
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">LOGIN</button>
        </form>
        <p class="text-center mt-4 text-gray-600">New User? <a href="auth/register.php" class="text-blue-500 hover:underline">SignUp</a></p>
    </div>
</div>

</body>
</html>
