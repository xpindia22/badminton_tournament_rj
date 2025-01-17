<?php
<<<<<<< HEAD
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
=======
// dashboard_player.php

session_start();
if (!isset($_SESSION['player_uid'])) {
    header("Location: login_player.php");
    exit;
}

$player_name = $_SESSION['player_name'];

>>>>>>> f59479a23af9e500d49532d6110ee720122dbfad
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
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
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            text-align: center;
            padding: 20px;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            display: block;
            margin: 10px;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            width: 200px;
            text-align: center;
            border-radius: 5px;
        }
        .links a:hover {
            background-color: #0056b3;
        }
        .logout-btn {
            margin-top: 20px;
            padding: 10px;
            width: 200px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($player_name) ?>!</h1>
        <h2>Player Dashboard</h2>

        <div class="links">
            <a href="results_bd.php">Results - Badminton Doubles</a>
            <a href="results_xd.php">Results - Mixed Doubles</a>
            <a href="results_singles.php">Results - Singles</a>
            <a href="ranking_singles.php">Ranking - Singles</a>
            <a href="ranking_doubles.php">Ranking - Doubles</a>
        </div>

        <a href="logout_player.php" class="logout-btn">Logout</a>
    </div>
>>>>>>> f59479a23af9e500d49532d6110ee720122dbfad
</body>
</html>
