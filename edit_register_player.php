<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'header.php';
require_once 'conn.php';
require 'auth.php';

// Ensure only logged-in users can access
redirect_if_not_logged_in();

// Check if the user is an admin
$is_admin = is_admin();

$message = '';
$name = '';
$dob = '';
$sex = '';
$password = '';
$uid = '';

// Function to get the next available UID dynamically
function getNextAvailableUID($conn) {
    $result = $conn->query("SELECT MAX(uid) + 1 AS next_uid FROM players");
    if ($result) {
        $row = $result->fetch_assoc();
        $next_uid = $row['next_uid'] ?? 1;
        $result->close();
        return $next_uid;
    }
    return 1;
}

// Fetch the suggested UID before showing the form
$next_uid = getNextAvailableUID($conn);

// Fetch all registered players sorted by UID (latest first)
$players = [];
$result = $conn->query("SELECT uid, name, dob, age, sex, created_at FROM players ORDER BY uid DESC");
if ($result) {
    $players = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function confirmDelete(uid) {
            if (confirm("Are you sure you want to delete this player?")) {
                window.location.href = "delete_player.php?uid=" + uid;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Players</h1>
        <h2>Registered Players</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>UID</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Registered At</th>
                    <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $id = 1; foreach ($players as $player): ?>
                    <tr>
                        <td><?= $id++ ?></td>
                        <td><?= htmlspecialchars($player['uid']) ?></td>
                        <td><?= htmlspecialchars($player['name']) ?></td>
                        <td><?= htmlspecialchars($player['dob']) ?></td>
                        <td><?= htmlspecialchars($player['age']) ?></td>
                        <td><?= htmlspecialchars($player['sex']) ?></td>
                        <td><?= date("d-m-Y h:i A", strtotime($player['created_at'])) ?></td>
                        <?php if ($is_admin): ?>
                        <td>
                            <a href="edit_player.php?uid=<?= $player['uid'] ?>">Edit</a> |
                            <a href="javascript:void(0);" onclick="confirmDelete(<?= $player['uid'] ?>)">Delete</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php ob_end_flush(); ?>
