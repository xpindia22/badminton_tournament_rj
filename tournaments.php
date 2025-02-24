<?php
//tournaments.php
include 'header.php';
require 'fetch_tournaments.php'; // Include the reusable fetch logic

require_once 'conn.php';
////require_once 'permissions.php';

require 'auth.php';
redirect_if_not_logged_in();

$is_admin = is_admin();

// Fetch tournaments
$query = $is_admin ? "SELECT * FROM tournaments" : "SELECT * FROM tournaments WHERE created_by = ?";
$stmt = $conn->prepare($query);
if (!$is_admin) {
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$result = $stmt->get_result();

$message = "";

// Handle form submissions for edit and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'delete') {
        $tournament_id = intval($_POST['tournament_id']);
        $stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ?");
        $stmt->bind_param("i", $tournament_id);
        if ($stmt->execute()) {
            $message = "Tournament deleted successfully.";
        } else {
            $message = "Error deleting tournament: {$stmt->error}";
        }
    }

    if ($_POST['action'] === 'edit') {
        $tournament_id = intval($_POST['tournament_id']);
        $name = $_POST['name'];
        $year = $_POST['year'];
        $stmt = $conn->prepare("UPDATE tournaments SET name = ?, year = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $year, $tournament_id);
        if ($stmt->execute()) {
            $message = "Tournament updated successfully.";
        } else {
            $message = "Error updating tournament: {$stmt->error}";
        }
    }

    header("Location: tournaments.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_admin ? "Manage All Tournaments" : "View Tournaments" ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add additional styles for better layout */
        .container {
            margin: 20px auto;
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .form-inline {
            display: inline;
        }
    </style>
</head>
<body>
 
    <div class="container">
        <h1><?= $is_admin ? "Manage All Tournaments" : "View Tournaments" ?></h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Year</th>
                    <?php if ($is_admin): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td>
                        <?php if ($is_admin): ?>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="tournament_id" value="<?= $row['id'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                        <?php else: ?>
                            <?= htmlspecialchars($row['name']) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($is_admin): ?>
                                <input type="number" name="year" value="<?= htmlspecialchars($row['year']) ?>" required>
                        <?php else: ?>
                            <?= htmlspecialchars($row['year']) ?>
                        <?php endif; ?>
                    </td>
                    <?php if ($is_admin): ?>
                    <td>
                        <button type="submit" name="action" value="edit" class="btn-small">Save</button>
                        </form>
                        <form method="post" class="form-inline">
                            <input type="hidden" name="tournament_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="delete" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this tournament?')">Delete</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No tournaments found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
