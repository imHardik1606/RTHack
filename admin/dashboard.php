<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-6">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-4xl">
        <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">Admin Dashboard</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-500 text-white p-4 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold"><?= $totalStudents; ?></h2>
                <p class="text-lg">Total Students</p>
            </div>
            <div class="bg-green-500 text-white p-4 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold"><?= $totalFaculty; ?></h2>
                <p class="text-lg">Total Faculty</p>
            </div>
            <div class="bg-yellow-500 text-white p-4 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold"><?= $totalCourses; ?></h2>
                <p class="text-lg">Total Courses</p>
            </div>
            <div class="bg-purple-500 text-white p-4 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold"><?= $totalSubjects; ?></h2>
                <p class="text-lg">Total Subjects</p>
            </div>
            <div class="bg-red-500 text-white p-4 rounded-lg shadow-md text-center">
                <h2 class="text-3xl font-bold"><?= $totalAttendance; ?></h2>
                <p class="text-lg">Total Attendance Records</p>
            </div>
        </div>
        <form action="../auth/logout.php" method="POST" class="mt-6 flex justify-center">
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">Logout</button>
        </form>
    </div>
</body>
</html>
