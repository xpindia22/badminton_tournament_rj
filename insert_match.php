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

// Fetch all players
$players = [];
$playerQuery = "SELECT id, name, dob, sex FROM players";
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
    <title>Insert Match</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        select, input, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
            padding: 12px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Insert Match</h1>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
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
        <select name="category_id" id="category_id" onchange="updatePlayerDropdown()" required>
            <option value="">Select Category</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" data-type="<?= $row['type'] ?>" data-sex="<?= $row['sex'] ?>" data-age="<?= $row['age_group'] ?>">
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

        <button type="submit">Add Match</button>
    </form>
</div>

<script>
    const players = <?= json_encode($players) ?>;

    function updatePlayerDropdown() {
        const categoryElement = document.getElementById('category_id');
        const categoryType = categoryElement.selectedOptions[0].dataset.type;
        const categorySex = categoryElement.selectedOptions[0].dataset.sex;
        const categoryAge = categoryElement.selectedOptions[0].dataset.age;

        const singlesSection = document.getElementById('singles_players');
        const doublesSection = document.getElementById('doubles_players');
        
        if (categoryType === 'singles') {
            singlesSection.style.display = 'block';
            doublesSection.style.display = 'none';
            populatePlayers(['player1_id', 'player2_id'], categorySex, categoryAge);
        } else {
            singlesSection.style.display = 'none';
            doublesSection.style.display = 'block';
            let team1 = ['team1_player1_id', 'team1_player2_id'];
            let team2 = ['team2_player1_id', 'team2_player2_id'];

            if (categoryType === 'mixed doubles') {
                populatePlayersMixed(team1, team2, categoryAge);
            } else {
                populatePlayers([...team1, ...team2], categorySex, categoryAge);
            }
        }
    }

    function populatePlayers(dropdownIds, sex, age) {
        dropdownIds.forEach(id => {
            let dropdown = document.getElementsByName(id)[0];
            dropdown.innerHTML = '<option value="">Select Player</option>';
            players.forEach(player => {
                if (player.sex === sex) {
                    dropdown.innerHTML += `<option value="${player.id}">${player.name}</option>`;
                }
            });
        });
    }

    function populatePlayersMixed(team1, team2, age) {
        populatePlayers(team1, 'M', age);
        populatePlayers(team2, 'F', age);
    }
</script>

</body>
</html>
