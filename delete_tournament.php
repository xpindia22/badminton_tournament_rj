<?php
// delete_tournament.php
require 'auth.php';
redirect_if_not_logged_in();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p class='error'>Invalid tournament ID.</p>");
}

$tournament_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Verify permissions
$query = $is_admin 
    ? "DELETE FROM tournaments WHERE id = ?"
    : "DELETE FROM tournaments WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("<p class='error'>Database error: " . $conn->error . "</p>");
}

if ($is_admin) {
    $stmt->bind_param("i", $tournament_id);
} else {
    $stmt->bind_param("ii", $tournament_id, $user_id);
}

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "<p class='success'>Tournament deleted successfully!</p>";
} else {
    echo "<p class='error'>Error deleting tournament or access denied.</p>";
}

$stmt->close();
header("Location: insert_tournament.php");
exit;
?>
