<?php
require_once 'auth.php';
require_once 'permissions.php';
redirect_if_not_logged_in();

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch the match ID
$match_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$match_id) {
    header("Location: results_singles.php?error=invalid_id");
    exit;
}

// Fetch match details
$query = "SELECT * FROM matches WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();
$match = $result->fetch_assoc();

if (!$match) {
    header("Location: results_singles.php?error=match_not_found");
    exit;
}

// Ensure the user has permission to edit this match
if (!is_admin() && $match['created_by'] != $_SESSION['user_id']) {
    die("You do not have permission to edit this match.");
}

// Handle form submission for editing the match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stage = $_POST['stage'];
    $match_date = $_POST['match_date'];
    $match_time = $_POST['match_time'];
    $set1_player1_points = $_POST['set1_player1_points'];
    $set1_player2_points = $_POST['set1_player2_points'];
    $set2_player1_points = $_POST['set2_player1_points'];
    $set2_player2_points = $_POST['set2_player2_points'];
    $set3_player1_points = $_POST['set3_player1_points'];
    $set3_player2_points = $_POST['set3_player2_points'];

    $update_query = "
        UPDATE matches
        SET 
            stage = ?, 
            match_date = ?, 
            match_time = ?, 
            set1_player1_points = ?, 
            set1_player2_points = ?, 
            set2_player1_points = ?, 
            set2_player2_points = ?, 
            set3_player1_points = ?, 
            set3_player2_points = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($update_query);

    if (!$stmt) {
        die("Error preparing update statement: " . $conn->error);
    }

    $stmt->bind_param(
        "ssiiiiiiii",
        $stage,
        $match_date,
        $match_time,
        $set1_player1_points,
        $set1_player2_points,
        $set2_player1_points,
        $set2_player2_points,
        $set3_player1_points,
        $set3_player2_points,
        $match_id
    );

    $stmt->execute();

    header("Location: results_singles.php?success=updated");
    exit;
}

// Predefined stages for dropdown
$stages = ['Preliminary', 'Quarterfinal', 'Semifinal', 'Final'];

// Format match time for the input field
$match_time_formatted = date("H:i", strtotime($match['match_time']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Match</title>
    <link rel="stylesheet" href="styles.css">
 
</head>
<body>
    <h1>Edit Match</h1>

    <form method="POST">
        <label for="stage">Stage:</label>
        <select name="stage" id="stage" required>
            <option value="">Select Stage</option>
            <?php foreach ($stages as $option): ?>
                <option value="<?= $option ?>" <?= $match['stage'] === $option ? 'selected' : '' ?>>
                    <?= $option ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="match_date">Match Date:</label>
        <input type="date" name="match_date" id="match_date" value="<?= $match['match_date'] ?>" required>
        <br>

        <label for="match_time">Match Time:</label>
        <input type="time" name="match_time" id="match_time" value="<?= $match_time_formatted ?>" required>
        <br>

        <label for="set1_player1_points">Set 1 - Player 1 Points:</label>
        <input type="number" name="set1_player1_points" id="set1_player1_points" value="<?= $match['set1_player1_points'] ?>" required>
        <br>

        <label for="set1_player2_points">Set 1 - Player 2 Points:</label>
        <input type="number" name="set1_player2_points" id="set1_player2_points" value="<?= $match['set1_player2_points'] ?>" required>
        <br>

        <label for="set2_player1_points">Set 2 - Player 1 Points:</label>
        <input type="number" name="set2_player1_points" id="set2_player1_points" value="<?= $match['set2_player1_points'] ?>" required>
        <br>

        <label for="set2_player2_points">Set 2 - Player 2 Points:</label>
        <input type="number" name="set2_player2_points" id="set2_player2_points" value="<?= $match['set2_player2_points'] ?>" required>
        <br>

        <label for="set3_player1_points">Set 3 - Player 1 Points:</label>
        <input type="number" name="set3_player1_points" id="set3_player1_points" value="<?= $match['set3_player1_points'] ?>" required>
        <br>

        <label for="set3_player2_points">Set 3 - Player 2 Points:</label>
        <input type="number" name="set3_player2_points" id="set3_player2_points" value="<?= $match['set3_player2_points'] ?>" required>
        <br>

        <button type="submit">Update Match</button>
    </form>
</body>
</html>
