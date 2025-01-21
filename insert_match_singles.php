<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conn.php';
include 'header.php';
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
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

// Fetch tournaments where the user is either the **creator** or **moderator**
$tournaments = $conn->prepare("
    SELECT id, name FROM tournaments 
    WHERE created_by = ? OR moderated_by = ?
");
$tournaments->bind_param("ii", $loggedInUserId, $loggedInUserId);
$tournaments->execute();
$tournamentResult = $tournaments->get_result();
$tournaments->close();

// Handle tournament locking and match insertions
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
        // **Match Insertion**
        $tournament_id = $lockedTournament ?? $_POST['tournament_id'];
        $category_id = $_POST['category_id'];
        $player1_id = $_POST['player1_id'];
        $player2_id = $_POST['player2_id'];
        $stage = $_POST['stage'] ?? 'Pre Quarter Finals'; // Default stage to prevent null error
        $date = $_POST['date'];
        $match_time = $_POST['time'];

        // Prevent Undefined Array Key Warnings
        $set1_p1 = $_POST['set1_player1_points'] ?? 0;
        $set1_p2 = $_POST['set1_player2_points'] ?? 0;
        $set2_p1 = $_POST['set2_player1_points'] ?? 0;
        $set2_p2 = $_POST['set2_player2_points'] ?? 0;
        $set3_p1 = $_POST['set3_player1_points'] ?? 0;
        $set3_p2 = $_POST['set3_player2_points'] ?? 0;

        $stmt = $conn->prepare("
            INSERT INTO matches (
                tournament_id, category_id, player1_id, player2_id, stage, 
                match_date, match_time, set1_player1_points, set1_player2_points, 
                set2_player1_points, set2_player2_points, set3_player1_points, set3_player2_points
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiisssiiiiii",
            $tournament_id, $category_id, $player1_id, $player2_id, $stage,
            $date, $match_time, $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2
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
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM categories c
        INNER JOIN tournament_categories tc ON c.id = tc.category_id
        WHERE tc.tournament_id = ?
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
}

// Fetch players
$players = $conn->query("SELECT id, name, dob, sex FROM players");
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

        <!-- Tournament Locking -->
        <?php if (!$lockedTournament): ?>
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
        <?php else: ?>
            <form method="post">
                <p>Locked Tournament: <?= htmlspecialchars($_SESSION['locked_tournament_name'] ?? '') ?></p>
                <button type="submit" name="unlock_tournament">Unlock Tournament</button>
            </form>

            <form method="post">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="player1_id">Player 1:</label>
                <select name="player1_id" required>
                    <option value="">Select Player</option>
                    <?php while ($row = $players->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="player2_id">Player 2:</label>
                <select name="player2_id" required>
                    <option value="">Select Player</option>
                    <?php $players->data_seek(0); while ($row = $players->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="date">Match Date:</label>
                <input type="date" name="date" required>

                <label for="match_time">Match Time:</label>
                <input type="time" name="time" required>

                <!-- Set Scores -->
                <label for="set1_player1_points">Set 1 Player 1 Points:</label>
                <input type="number" name="set1_player1_points" required>

                <label for="set1_player2_points">Set 1 Player 2 Points:</label>
                <input type="number" name="set1_player2_points" required>

                <label for="set2_player1_points">Set 2 Player 1 Points:</label>
                <input type="number" name="set2_player1_points" required>

                <label for="set2_player2_points">Set 2 Player 2 Points:</label>
                <input type="number" name="set2_player2_points" required>

                <label for="set3_player1_points">Set 3 Player 1 Points:</label>
                <input type="number" name="set3_player1_points">

                <label for="set3_player2_points">Set 3 Player 2 Points:</label>
                <input type="number" name="set3_player2_points">

                <button type="submit">Add Match</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
