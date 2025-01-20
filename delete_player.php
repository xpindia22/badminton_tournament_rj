<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'header.php';
require 'auth.php';
redirect_if_not_logged_in();

// Ensure only admins or owners can delete
if (!is_admin() && !isset($_SESSION['user_id'])) {
    die("Access denied.");
}

// Validate UID
if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    die("Invalid player UID.");
}

$player_uid = intval($_GET['uid']);
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Verify ownership or admin access
$stmt = $conn->prepare("SELECT created_by FROM players WHERE uid = ?");
$stmt->bind_param("i", $player_uid);
$stmt->execute();
$stmt->bind_result($created_by);
$stmt->fetch();
$stmt->close();

if (!$is_admin && $created_by !== $user_id) {
    die("Access denied: You can only delete your own players.");
}

// Delete player
$stmt = $conn->prepare("DELETE FROM players WHERE uid = ?");
$stmt->bind_param("i", $player_uid);
if ($stmt->execute()) {
    echo "<p class='success'>Player deleted successfully!</p>";
} else {
    echo "<p class='error'>Error deleting player.</p>";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Player</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Delete Player</h1>
        <p><a href="register_player.php">Return to Player List</a></p>
    </div>
</body>
</html>

<?php ob_end_flush(); ?>
