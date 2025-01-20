<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conn.php';
include 'header.php';
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;
$username = $_SESSION['username'];

// Get logged-in user's ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($loggedInUserId);
$stmt->fetch();
$stmt->close();

// Fetch tournaments where the user is either the creator or moderator
$tournamentsQuery = "
    SELECT id, name FROM tournaments 
    WHERE created_by = ? OR moderated_by = ?";
$tournaments = $conn->prepare($tournamentsQuery);
$tournaments->bind_param("ii", $loggedInUserId, $loggedInUserId);
$tournaments->execute();
$tournamentResult = $tournaments->get_result();
$tournaments->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['lock_tournament'])) {
        $lockedTournament = intval($_POST['tournament_id']);
        $_SESSION['locked_tournament'] = $lockedTournament;

        $stmt = $conn->prepare("SELECT name FROM tournaments WHERE id = ?");
        $stmt->bind_param("i", $lockedTournament);
        $stmt->execute();
        $stmt->bind_result($lockedTournamentName);
        $stmt->fetch();
        $_SESSION['locked_tournament_name'] = $lockedTournamentName;
        $stmt->close();
    } elseif (isset($_POST['unlock_tournament'])) {
        unset($_SESSION['locked_tournament'], $_SESSION['locked_tournament_name']);
        $lockedTournament = null;
    } else {
        // Match Insertion
        $tournament_id = $lockedTournament ?? $_POST['tournament_id'];
        $category_id = $_POST['category_id'];
        $stage = $_POST['stage'];
        $date = $_POST['date'];
        $match_time = date("H:i", strtotime($_POST['time'])); // Convert to 24-hour format

        // Check if the category is singles or doubles
        $stmt = $conn->prepare("SELECT type FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $stmt->bind_result($categoryType);
        $stmt->fetch();
        $stmt->close();

        if ($categoryType === 'singles') {
            $player1_id = $_POST['player1_id'];
            $player2_id = $_POST['player2_id'];
            $team1_player1 = $team1_player2 = $team2_player1 = $team2_player2 = null;
        } else {
            $team1_player1 = $_POST['team1_player1_id'];
            $team1_player2 = $_POST['team1_player2_id'];
            $team2_player1 = $_POST['team2_player1_id'];
            $team2_player2 = $_POST['team2_player2_id'];
            $player1_id = $player2_id = null;
        }

        // Set Scores
        $set1_p1 = $_POST['set1_player1_points'];
        $set1_p2 = $_POST['set1_player2_points'];
        $set2_p1 = $_POST['set2_player1_points'];
        $set2_p2 = $_POST['set2_player2_points'];
        $set3_p1 = $_POST['set3_player1_points'] ?? 0;
        $set3_p2 = $_POST['set3_player2_points'] ?? 0;

        $stmt = $conn->prepare("
            INSERT INTO matches (
                tournament_id, category_id, stage, match_date, match_time, 
                player1_id, player2_id, team1_player1_id, team1_player2_id, 
                team2_player1_id, team2_player2_id,
                set1_player1_points, set1_player2_points, 
                set2_player1_points, set2_player2_points,
                set3_player1_points, set3_player2_points
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iisssiiiiiiiiiiii",
            $tournament_id, $category_id, $stage, $date, $match_time,
            $player1_id, $player2_id, $team1_player1, $team1_player2,
            $team2_player1, $team2_player2,
            $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2
        );

        if ($stmt->execute()) {
            $message = "Match added successfully!";
        } else {
            $message = "Error adding match: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch categories for the locked tournament
$categories = [];
if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex, c.type 
        FROM categories c
        INNER JOIN tournament_categories tc ON c.id = tc.category_id
        WHERE tc.tournament_id = ?
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
</head>
<body>
    <div>
        <h1>Insert Match</h1>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="tournament_id">Select Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <option value="">Select Tournament</option>
                <?php while ($row = $tournamentResult->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="lock_tournament">Lock Tournament</button>
        </form>

        <form method="post">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" data-type="<?= $row['type'] ?>">
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div id="singles_players">
                <label for="player1_id">Player 1:</label>
                <select name="player1_id" required></select>

                <label for="player2_id">Player 2:</label>
                <select name="player2_id" required></select>
            </div>

            <div id="doubles_players" style="display:none;">
                <label>Team 1 Players:</label>
                <select name="team1_player1_id" required></select>
                <select name="team1_player2_id" required></select>

                <label>Team 2 Players:</label>
                <select name="team2_player1_id" required></select>
                <select name="team2_player2_id" required></select>
            </div>
        </form>
    </div>
</body>
</html>
