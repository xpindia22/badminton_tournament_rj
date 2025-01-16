<?php
session_start();
require 'conn.php';

if (!isset($_SESSION['player_id'])) {
    header("Location: login.php");
    exit;
}

$player_id = $_SESSION['player_id'];

$stmt = $conn->prepare("SELECT name, username, dob, age, sex FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$stmt->bind_result($name, $username, $dob, $age, $sex);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($name) ?></h1>
    <p>Username: <?= htmlspecialchars($username) ?></p>
    <p>Date of Birth: <?= htmlspecialchars($dob) ?></p>
    <p>Age: <?= htmlspecialchars($age) ?></p>
    <p>Sex: <?= htmlspecialchars($sex) ?></p>

    <a href="insert_player.php">Enter Tournament</a>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
