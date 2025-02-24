<!--  -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/index.css">
    <title>Attendance System - Home</title>
</head>
<body>

<div class="container">
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.svg" alt="Logo">
            <h1>TTENDEASE</h1>
        </div>
        <ul class="menu">
            <li><a href="#">Home</a></li>
            <li><a href="#">About us</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Login/SignUp</a></li>
        </ul>
    </div>

    <!-- Hero Section -->
    <div class="hero">
        <div class="login-box">
            <h2>LOGIN PAGE</h2>
            <!-- <form action="auth/login.php" method="POST">
                <input type="email" name="email" placeholder="Enter Your College Mail Id" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="user_type">
                    <option value="student">Student</option>
                    <option value="teacher">Faculty</option>
                </select>
                <button type="submit">LOGIN</button>

                <span>New User? <a href="auth/register.php">SignUp</a></span>
            </form> -->
            <form action="auth/login.php" method="POST">
    <input type="email" name="email" placeholder="Enter Your College Mail Id" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="user_type">
        <option value="student">Studenst</option>
        <option value="teacher">Faculty</option>
    </select>
    <a href=""><button type="submit" >LOGIN</button></a>
</form>

        </div>
    </div>
</div>

</body>
</html>
