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
    if (isset($_POST['lock_tournament']) && isset($_POST['tournament_id'])) {
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
    }
}

// Fetch only BS & GS categories from the locked tournament
$categories = [];
if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM categories c
        INNER JOIN tournament_categories tc ON c.id = tc.category_id
        WHERE tc.tournament_id = ? AND (c.name LIKE '%BS%' OR c.name LIKE '%GS%')
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
}

// Fetch all players
$players = [];
$playerQuery = "SELECT id, name, age, sex FROM players";
$playerResult = $conn->query($playerQuery);
while ($row = $playerResult->fetch_assoc()) {
    $players[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Singles Match</title>
</head>
<body>
<div class="container">
    <h1>Insert Singles Match</h1>
    <form method="post">
        <label for="tournament_id">Select Tournament:</label>
        <select name="tournament_id" id="tournament_id" required <?= $lockedTournament ? 'disabled' : '' ?>>
            <option value="">Select Tournament</option>
            <?php while ($row = $tournamentResult->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= ($lockedTournament == $row['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <?php if ($lockedTournament): ?>
            <button type="submit" name="unlock_tournament">Unlock Tournament</button>
        <?php else: ?>
            <button type="submit" name="lock_tournament">Lock Tournament</button>
        <?php endif; ?>
    </form>
    
    <?php if ($lockedTournament): ?>
    <form method="post">
        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="">Select Category</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        
        <label for="stage">Stage:</label>
        <select name="stage" id="stage" required>
            <option value="">Select Stage</option>
            <option value="Round 1">Round 1</option>
            <option value="Quarterfinals">Quarterfinals</option>
            <option value="Semifinals">Semifinals</option>
            <option value="Finals">Finals</option>
        </select>
        
        <label for="player1_id">Player 1:</label>
        <select name="player1_id" id="player1_id" required></select>
        
        <label for="player2_id">Player 2:</label>
        <select name="player2_id" id="player2_id" required></select>
        
        <label>Set Scores:</label>
        <input type="number" name="set1_p1" placeholder="Set 1 Player 1" required>
        <input type="number" name="set1_p2" placeholder="Set 1 Player 2" required>
        <input type="number" name="set2_p1" placeholder="Set 2 Player 1" required>
        <input type="number" name="set2_p2" placeholder="Set 2 Player 2" required>
        <input type="number" name="set3_p1" placeholder="Set 3 Player 1" required>
        <input type="number" name="set3_p2" placeholder="Set 3 Player 2" required>
        
        <label for="match_date">Match Date:</label>
        <input type="date" name="match_date" required>
        
        <label for="match_time">Match Time:</label>
        <input type="time" name="match_time" required>
        
        <button type="submit">Add Match</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
