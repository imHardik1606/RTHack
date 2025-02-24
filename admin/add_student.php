<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 shadow-lg rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">
            <i class="fas fa-user-plus text-green-500"></i> Add Student
        </h2>
        <?php if (isset($message)) echo "<p class='text-green-600 font-semibold text-center'>$message</p>"; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-600 font-medium">Name:</label>
                <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-300">
            </div>
            <div>
                <label class="block text-gray-600 font-medium">Email:</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-300">
            </div>
            <div>
                <label class="block text-gray-600 font-medium">Password:</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-300">
            </div>
            <div>
                <label class="block text-gray-600 font-medium">Course:</label>
                <select name="course_id" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-300">
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course) {
                        echo "<option value='{$course['id']}'>{$course['course_name']}</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-600 font-medium">Year (Semester):</label>
                <select name="year_id" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-300">
                    <option value="">Select Year</option>
                    <?php foreach ($years as $year) {
                        echo "<option value='{$year['id']}'>{$year['year_name']}</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-600 font-medium">Session:</label>
                <select name="session_id" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-300">
                    <option value="">Select Session</option>
                    <?php foreach ($sessions as $session) {
                        echo "<option value='{$session['id']}'>{$session['session_name']}</option>";
                    } ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition">
                <i class="fas fa-plus"></i> Add Student
            </button>
        </form>
    </div>
</body>
</html>
