<?php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin() && !is_user()) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];
    $stage = $_POST['stage'];
    $set1_p1 = $_POST['set1_player1_points'];
    $set1_p2 = $_POST['set1_player2_points'];
    $set2_p1 = $_POST['set2_player1_points'];
    $set2_p2 = $_POST['set2_player2_points'];
    $set3_p1 = $_POST['set3_player1_points'] ?? 0;
    $set3_p2 = $_POST['set3_player2_points'] ?? 0;
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        INSERT INTO matches (
            tournament_id, category_id, player1_id, player2_id, stage, 
            set1_player1_points, set1_player2_points, set2_player1_points, 
            set2_player2_points, set3_player1_points, set3_player2_points, created_by
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiiiiiiiiiii", 
        $tournament_id, $category_id, $player1_id, $player2_id, $stage,
        $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2, $created_by
    );
    if ($stmt->execute()) {
        echo "<p>Match added successfully!</p>";
    } else {
        echo "<p>Error: {$stmt->error}</p>";
    }
    $stmt->close();
}

// Fetch matches created by the user
$query = is_admin() ? "SELECT * FROM matches" : "SELECT * FROM matches WHERE created_by = ?";
$stmt = $conn->prepare($query);
if (!is_admin()) $stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Match</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Insert Match</h1>
    <form method="post">
        <label for="tournament_id">Tournament:</label>
        <select name="tournament_id" id="tournament_id" required>
            <option value="">Select Tournament</option>
            <?php
            $tournaments = $conn->query("SELECT id, name FROM tournaments");
            while ($row = $tournaments->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="">Select Category</option>
            <?php
            $categories = $conn->query("SELECT id, name FROM categories");
            while ($row = $categories->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="player1_id">Player 1:</label>
        <select name="player1_id" id="player1_id" required>
            <option value="">Select Player</option>
            <?php
            $players = $conn->query("SELECT id, name FROM players WHERE created_by = {$_SESSION['user_id']}");
            while ($row = $players->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="player2_id">Player 2:</label>
        <select name="player2_id" id="player2_id" required>
            <option value="">Select Player</option>
            <?php
            $players = $conn->query("SELECT id, name FROM players WHERE created_by = {$_SESSION['user_id']}");
            while ($row = $players->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="stage">Match Stage:</label>
        <select name="stage" id="stage" required>
            <option value="Pre Quarter Finals">Pre Quarter Finals</option>
            <option value="Quarter Finals">Quarter Finals</option>
            <option value="Semi Finals">Semi Finals</option>
            <option value="Finals">Finals</option>
        </select>

        <label for="set1_player1_points">Set 1 Player 1 Points:</label>
        <input type="number" name="set1_player1_points" id="set1_player1_points" required>

        <label for="set1_player2_points">Set 1 Player 2 Points:</label>
        <input type="number" name="set1_player2_points" id="set1_player2_points" required>
