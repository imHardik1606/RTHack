<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied. Admins only.");
}

// Handle Add Year
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_year'])) {
    $year_name = trim($_POST['year_name']);
    
    if (!empty($year_name)) {
        $stmt = $pdo->prepare("INSERT INTO years (year_name) VALUES (?)");
        $stmt->execute([$year_name]);
        echo "<div class='alert alert-success'>✅ Year added successfully!</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ Year name cannot be empty!</div>";
    }
}

// Handle Edit Year
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_year'])) {
    $year_id = $_POST['year_id'];
    $year_name = trim($_POST['year_name']);

    if (!empty($year_name)) {
        $stmt = $pdo->prepare("UPDATE years SET year_name = ? WHERE id = ?");
        $stmt->execute([$year_name, $year_id]);
        echo "<div class='alert alert-info'>✏️ Year updated successfully!</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ Year name cannot be empty!</div>";
    }
}

// Handle Delete Year
if (isset($_GET['delete'])) {
    $year_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM years WHERE id = ?");
    $stmt->execute([$year_id]);
    echo "<div class='alert alert-danger'>🗑️ Year deleted successfully!</div>";
}

// Fetch all years
$stmt = $pdo->prepare("SELECT * FROM years ORDER BY id ASC");
$stmt->execute();
$years = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Year</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>📌 Manage Year</h2>

        <!-- Add Year Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">➕ Add New Year</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Year Name:</label>
                        <input type="text" name="year_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_year" class="btn btn-success">✅ Add Year</button>
                </form>
            </div>
        </div>

        <!-- Year List -->
        <div class="card">
            <div class="card-header bg-dark text-white">📋 Year List</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Year Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $year) { ?>
                            <tr>
                                <td><?= $year['id']; ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="year_id" value="<?= $year['id']; ?>">
                                        <input type="text" name="year_name" value="<?= htmlspecialchars($year['year_name']); ?>" class="form-control d-inline w-50" required>
                                        <button type="submit" name="edit_year" class="btn btn-sm btn-warning">✏️ Edit</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="manage_year.php?delete=<?= $year['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this year?')">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>
