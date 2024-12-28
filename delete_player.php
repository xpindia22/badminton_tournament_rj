<?php
//delete_player.php
$servername = "localhost";
$username = "root";
$password = "xxx";
$dbname = "badminton_tournament";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Player ID is required.");
}

$player_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
if ($stmt->execute()) {
    echo "<p>Player deleted successfully! <a href='insert_player.php'>Go back</a></p>";
} else {
    echo "<p>Error: {$stmt->error}</p>";
}
$stmt->close();
$conn->close();
?>
