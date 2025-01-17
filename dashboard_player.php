<?php
// dashboard_player.php

session_start();
if (!isset($_SESSION['player_uid'])) {
    header("Location: login_player.php");
    exit;
}

$player_name = $_SESSION['player_name'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
</body>
</html>
