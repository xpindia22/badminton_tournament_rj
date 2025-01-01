<?php
// insert_match.php
session_start();
require 'auth.php';
require_once 'conn.php'; // Database connection file
redirect_if_not_logged_in();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];
    $category_id = $_POST['category_id'];
    $match_date = $_POST['match_date'];

    // Ensure players are not the same
    if ($player1_id === $player2_id) {
        $message = "A player cannot play against themselves.";
    } else {
        // Insert match into the database
        $stmt = $conn->prepare("INSERT INTO matches (player1_id, player2_id, category_id, match_date) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiis", $player1_id, $player2_id, $category_id, $match_date);

        if ($stmt->execute()) {
            $message = "Match successfully added.";
        } else {
            $message = "Error adding match: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch players
$players = $conn->query("SELECT id, name FROM players ORDER BY name ASC");
if (!$players) {
    die("Error fetching players: " . $conn->error);
}

// Fetch categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if (!$categories) {
    die("Error fetching categories: " . $conn->error);
}
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
    <div class="container">
        <h1>Insert Match</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" class="form-styled">
            <label for="player1_id">Player 1:</label>
            <select name="player1_id" id="player1_id" required>
                <option value="">Select Player 1</option>
                <?php while ($player = $players->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($player['id']) ?>"><?= htmlspecialchars($player['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="player2_id">Player 2:</label>
            <select name="player2_id" id="player2_id" required>
                <option value="">Select Player 2</option>
                <?php
                // Reset player result pointer for second dropdown
                $players->data_seek(0);
                while ($player = $players->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($player['id']) ?>"><?= htmlspecialchars($player['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select a Category</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="match_date">Match Date:</label>
            <input type="date" name="match_date" id="match_date" required>

            <button type="submit" class="btn-primary">Add Match</button>
        </form>
    </div>
</body>
</html>
