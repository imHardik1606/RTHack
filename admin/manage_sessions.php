<?php
session_start();
require '..\config/db.php'; // Ensure this file establishes $pdo connection

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_session'])) {
        $session_name = trim($_POST['session_name']);

        // Prevent duplicate session names
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE session_name = ?");
        $stmt->execute([$session_name]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO sessions (session_name) VALUES (?)")->execute([$session_name]);
        }
    } elseif (isset($_POST['delete_session'])) {
        $session_id = $_POST['session_id'];
        $pdo->prepare("DELETE FROM sessions WHERE id = ?")->execute([$session_id]);
    }
    header("Location: manage_sessions.php");
    exit;
}

// Fetch existing sessions
$sessions = $pdo->query("SELECT * FROM sessions ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Sessions - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include('..\includes/header.php');  ?>
    <div class="container mt-4">
        <h2>📌 Manage Sessions</h2>
        
        <h4>➕ Add Session</h4>
        <form method="POST" class="mb-3">
            <input type="text" name="session_name" placeholder="Session (e.g., Batch_2022_2025)" required class="form-control w-50 d-inline">
            <button type="submit" name="add_session" class="btn btn-primary">Add</button>
        </form>

        <h4>📋 Existing Sessions</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Session Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?= htmlspecialchars($session['id']) ?></td>
                        <td><?= htmlspecialchars($session['session_name']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                <button type="submit" name="delete_session" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
</body>
</html>
