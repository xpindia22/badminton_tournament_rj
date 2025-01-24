<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
////require_once 'permissions.php';

include 'header.php';
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;

// Function to calculate age
function calculate_age($dob) {
    return date('Y') - date('Y', strtotime($dob));
}

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
        $tournament_id = $lockedTournament ?? $_POST['tournament_id'];
        $category_id = $_POST['category_id'];
        $team1_player1_id = $_POST['team1_player1_id'];
        $team1_player2_id = $_POST['team1_player2_id'];
        $team2_player1_id = $_POST['team2_player1_id'];
        $team2_player2_id = $_POST['team2_player2_id'];
        $stage = $_POST['stage'];

        // Validate and format the match_date
        $dateInput = $_POST['date'];
        $match_date = DateTime::createFromFormat('Y-m-d', $dateInput);
        if ($match_date === false) {
            $message = "Invalid date format!";
        } else {
            $match_date = $match_date->format('Y-m-d'); // Ensure correct format
        }

        $match_time = $_POST['time'];
        $set1_team1 = $_POST['set1_team1_points'];
        $set1_team2 = $_POST['set1_team2_points'];
        $set2_team1 = $_POST['set2_team1_points'];
        $set2_team2 = $_POST['set2_team2_points'];
        $set3_team1 = $_POST['set3_team1_points'] ?? 0;
        $set3_team2 = $_POST['set3_team2_points'] ?? 0;

        if ($match_date !== false) { // Proceed only if the date is valid
            $stmt = $conn->prepare("INSERT INTO matches (
                tournament_id, category_id, team1_player1_id, team1_player2_id,
                team2_player1_id, team2_player2_id, stage, match_date, match_time,
                set1_team1_points, set1_team2_points, set2_team1_points, 
                set2_team2_points, set3_team1_points, set3_team2_points
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "iiiiisssiiiiiii",
                $tournament_id, $category_id, $team1_player1_id, $team1_player2_id,
                $team2_player1_id, $team2_player2_id, $stage, $match_date, $match_time,
                $set1_team1, $set1_team2, $set2_team1, $set2_team2, $set3_team1, $set3_team2
            );

            if ($stmt->execute()) {
                $message = "Match added successfully!";
            } else {
                $message = "Error adding match: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch tournaments
$tournaments = $conn->query("SELECT id, name FROM tournaments");

// Fetch categories for the locked tournament, filtering for doubles and mixed doubles
if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM categories c 
        INNER JOIN tournament_categories tc ON c.id = tc.category_id 
        WHERE tc.tournament_id = ? 
        AND (c.name NOT LIKE '%S' AND (c.name LIKE '%D' OR c.name LIKE '%XD'))
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
} else {
    $categories = $conn->query("
        SELECT id, name, age_group, sex 
        FROM categories 
        WHERE name NOT LIKE '%S' AND (name LIKE '%D' OR name LIKE '%XD')
    ");
}

// Fetch players
$players = $conn->query("SELECT id, name, dob, sex FROM players");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Doubles Match</title>
    <style>
/* Your CSS styles here */
body {
    font-family: Arial, sans-serif;
    background-color: #f7f9fc;
    margin: 0;
    padding: 0;
    color: #333;
}
.container {
    max-width: 800px;
    margin: 50px auto;
    background: #ffffff;
    padding: 20px 30px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}
h1 {
    text-align: center;
    color: #007bff;
    font-size: 24px;
    margin-bottom: 20px;
}
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #555;
}
select, input, button {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 100%;
}
select:focus, input:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
}
button {
    background-color: #007bff;
    color: #fff;
    border: none;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background-color: #0056b3;
}
.message {
    padding: 15px;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
}
.message.success {
    background-color: #d4edda;
    color: #155724;
}
.message.error {
    background-color: #f8d7da;
    color: #721c24;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Insert Doubles Match</h1>
        <?php if ($message): ?>
            <p class="message <?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if (!$lockedTournament): ?>
            <form method="post">
                <label for="tournament_id">Select Tournament:</label>
                <select name="tournament_id" id="tournament_id" required>
                    <option value="">Select Tournament</option>
                    <?php while ($row = $tournaments->fetch_assoc()): ?>
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
        <?php endif; ?>

        <form method="post">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>, <?= htmlspecialchars($row['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="team1_player1_id">Team 1 - Player 1:</label>
            <select name="team1_player1_id" id="team1_player1_id" required>
                <option value="">Select Player</option>
                <?php while ($player = $players->fetch_assoc()): 
                    $age = calculate_age($player['dob']); ?>
                    <option value="<?= $player['id'] ?>">
                        <?= htmlspecialchars($player['name']) ?> (<?= $age ?>, <?= htmlspecialchars($player['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="team1_player2_id">Team 1 - Player 2:</label>
            <select name="team1_player2_id" id="team1_player2_id" required>
                <option value="">Select Player</option>
                <?php $players->data_seek(0); ?>
                <?php while ($player = $players->fetch_assoc()): 
                    $age = calculate_age($player['dob']); ?>
                    <option value="<?= $player['id'] ?>">
                        <?= htmlspecialchars($player['name']) ?> (<?= $age ?>, <?= htmlspecialchars($player['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="team2_player1_id">Team 2 - Player 1:</label>
            <select name="team2_player1_id" id="team2_player1_id" required>
                <option value="">Select Player</option>
                <?php $players->data_seek(0); ?>
                <?php while ($player = $players->fetch_assoc()): 
                    $age = calculate_age($player['dob']); ?>
                    <option value="<?= $player['id'] ?>">
                        <?= htmlspecialchars($player['name']) ?> (<?= $age ?>, <?= htmlspecialchars($player['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="team2_player2_id">Team 2 - Player 2:</label>
            <select name="team2_player2_id" id="team2_player2_id" required>
                <option value="">Select Player</option>
                <?php $players->data_seek(0); ?>
                <?php while ($player = $players->fetch_assoc()): 
                    $age = calculate_age($player['dob']); ?>
                    <option value="<?= $player['id'] ?>">
                        <?= htmlspecialchars($player['name']) ?> (<?= $age ?>, <?= htmlspecialchars($player['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="stage">Match Stage:</label>
            <select name="stage" id="stage" required>
                <option value="Pre Quarter Finals">Pre Quarter Finals</option>
                <option value="Quarter Finals">Quarter Finals</option>
                <option value="Semi Finals">Semi Finals</option>
                <option value="Finals">Finals</option>
            </select>

            <label for="date">Match Date:</label>
            <input type="date" name="date" required>

            <label for="time">Match Time:</label>
            <input type="time" name="time" required>

            <label for="set1_team1_points">Set 1 Team 1 Points:</label>
            <input type="number" name="set1_team1_points" required>

            <label for="set1_team2_points">Set 1 Team 2 Points:</label>
            <input type="number" name="set1_team2_points" required>

            <label for="set2_team1_points">Set 2 Team 1 Points:</label>
            <input type="number" name="set2_team1_points" required>

            <label for="set2_team2_points">Set 2 Team 2 Points:</label>
            <input type="number" name="set2_team2_points" required>

            <label for="set3_team1_points">Set 3 Team 1 Points:</label>
            <input type="number" name="set3_team1_points">

            <label for="set3_team2_points">Set 3 Team 2 Points:</label>
            <input type="number" name="set3_team2_points">

            <button type="submit">Add Match</button>
        </form>
    </div>
</body>
</html>
