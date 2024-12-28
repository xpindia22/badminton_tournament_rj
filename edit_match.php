<?php
//edit_match.php
$conn = new mysqli("localhost", "root", "xxx", "badminton_tournament");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if match ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Match ID is required.");
}

$match_id = intval($_GET['id']);

// Handle form submission to update match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];
    $pool = $_POST['pool'];
    $stage = $_POST['stage'];
    $set1_p1 = $_POST['set1_player1_points'];
    $set1_p2 = $_POST['set1_player2_points'];
    $set2_p1 = $_POST['set2_player1_points'];
    $set2_p2 = $_POST['set2_player2_points'];
    $set3_p1 = $_POST['set3_player1_points'] ?? 0;
    $set3_p2 = $_POST['set3_player2_points'] ?? 0;

    $stmt = $conn->prepare("
        UPDATE matches
        SET tournament_id = ?, category_id = ?, player1_id = ?, player2_id = ?, pool = ?, stage = ?,
            set1_player1_points = ?, set1_player2_points = ?, set2_player1_points = ?, set2_player2_points = ?, set3_player1_points = ?, set3_player2_points = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "iiiissiiiiiii",
        $tournament_id, $category_id, $player1_id, $player2_id, $pool, $stage,
        $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2, $match_id
    );

    if ($stmt->execute()) {
        echo "<p>Match updated successfully! <a href='insert_match.php'>Go back</a></p>";
    } else {
        echo "<p>Error updating match: {$stmt->error}</p>";
    }

    $stmt->close();
    exit;
}

// Fetch match details for pre-filling the form
$result = $conn->query("
    SELECT * FROM matches WHERE id = $match_id
");

if ($result->num_rows === 0) {
    die("Match not found.");
}

$match = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Match</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        select, input, button { padding: 10px; margin-bottom: 10px; width: 100%; max-width: 300px; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Edit Match</h1>
    <form method="post">
        <label for="tournament_id">Tournament:</label>
        <select name="tournament_id" id="tournament_id" required>
            <?php
            $tournaments = $conn->query("SELECT id, name FROM tournaments");
            while ($row = $tournaments->fetch_assoc()) {
                $selected = $row['id'] == $match['tournament_id'] ? "selected" : "";
                echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <?php
            $categories = $conn->query("SELECT id, name FROM categories");
            while ($row = $categories->fetch_assoc()) {
                $selected = $row['id'] == $match['category_id'] ? "selected" : "";
                echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="player1_id">Player 1:</label>
        <select name="player1_id" id="player1_id" required>
            <?php
            $players = $conn->query("SELECT id, name FROM players");
            while ($row = $players->fetch_assoc()) {
                $selected = $row['id'] == $match['player1_id'] ? "selected" : "";
                echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="player2_id">Player 2:</label>
        <select name="player2_id" id="player2_id" required>
            <?php
            $players = $conn->query("SELECT id, name FROM players");
            while ($row = $players->fetch_assoc()) {
                $selected = $row['id'] == $match['player2_id'] ? "selected" : "";
                echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="pool">Pool:</label>
        <select name="pool" id="pool">
            <option value="">None</option>
            <option value="A" <?= $match['pool'] == 'A' ? 'selected' : '' ?>>A</option>
            <option value="B" <?= $match['pool'] == 'B' ? 'selected' : '' ?>>B</option>
        </select>

        <label for="stage">Match Stage:</label>
        <select name="stage" id="stage" required>
            <option value="Pre Quarter Finals" <?= $match['stage'] == 'Pre Quarter Finals' ? 'selected' : '' ?>>Pre Quarter Finals</option>
            <option value="Quarter Finals" <?= $match['stage'] == 'Quarter Finals' ? 'selected' : '' ?>>Quarter Finals</option>
            <option value="Semi Finals" <?= $match['stage'] == 'Semi Finals' ? 'selected' : '' ?>>Semi Finals</option>
            <option value="Finals" <?= $match['stage'] == 'Finals' ? 'selected' : '' ?>>Finals</option>
        </select>

        <label for="set1_player1_points">Set 1 Player 1 Points:</label>
        <input type="number" name="set1_player1_points" value="<?= $match['set1_player1_points'] ?>" required>

        <label for="set1_player2_points">Set 1 Player 2 Points:</label>
        <input type="number" name="set1_player2_points" value="<?= $match['set1_player2_points'] ?>" required>

        <label for="set2_player1_points">Set 2 Player 1 Points:</label>
        <input type="number" name="set2_player1_points" value="<?= $match['set2_player1_points'] ?>" required>

        <label for="set2_player2_points">Set 2 Player 2 Points:</label>
        <input type="number" name="set2_player2_points" value="<?= $match['set2_player2_points'] ?>" required>

        <label for="set3_player1_points">Set 3 Player 1 Points:</label>
        <input type="number" name="set3_player1_points" value="<?= $match['set3_player1_points'] ?>">

        <label for="set3_player2_points">Set 3 Player 2 Points:</label>
        <input type="number" name="set3_player2_points" value="<?= $match['set3_player2_points'] ?>">

        <button type="submit">Update Match</button>
    </form>
</body>
</html>
