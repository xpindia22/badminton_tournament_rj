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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_match'])) {
    $categoryId = intval($_POST['category_id']);
    $player1Id = intval($_POST['player1_id']);
    $player2Id = intval($_POST['player2_id']);
    $stage = $_POST['stage'];
    $matchDate = $_POST['date'];
    $matchTime = $_POST['match_time'];
    $set1P1 = intval($_POST['set1_player1_points']);
    $set1P2 = intval($_POST['set1_player2_points']);
    $set2P1 = intval($_POST['set2_player1_points']);
    $set2P2 = intval($_POST['set2_player2_points']);
    $set3P1 = isset($_POST['set3_player1_points']) ? intval($_POST['set3_player1_points']) : null;
    $set3P2 = isset($_POST['set3_player2_points']) ? intval($_POST['set3_player2_points']) : null;

    if ($categoryId && $player1Id && $player2Id && $stage && $matchDate && $matchTime) {
        // Ensure players are not the same
        if ($player1Id === $player2Id) {
            $message = "Players cannot be the same.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO matches 
                (tournament_id, category_id, player1_id, player2_id, stage, match_date, match_time, 
                set1_player1_points, set1_player2_points, set2_player1_points, set2_player2_points, 
                set3_player1_points, set3_player2_points) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt) {
                $stmt->bind_param("iiiissiiiiiii", 
                    $lockedTournament, $categoryId, $player1Id, $player2Id, $stage, 
                    $matchDate, $matchTime, 
                    $set1P1, $set1P2, $set2P1, $set2P2, 
                    $set3P1, $set3P2
                );

                if ($stmt->execute()) {
                    $message = "Match successfully added!";
                } else {
                    $message = "Error inserting match: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "SQL Prepare Error: " . $conn->error;
            }
        }
    } else {
        $message = "All fields are required!";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
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
        button.locked {
            background-color: #28a745;
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
    <h1>Insert Singles Match</h1>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

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
            <button type="submit" name="unlock_tournament" style="background-color: red;">Unlock Tournament</button>
        <?php else: ?>
            <button type="submit" name="lock_tournament">Lock Tournament</button>
        <?php endif; ?>
    </form>

    <?php if ($lockedTournament): ?>
    <form method="post">
        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" onchange="updatePlayerDropdown()" required>
            <option value="">Select Category</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" data-sex="<?= $row['sex'] ?>" data-age="<?= $row['age_group'] ?>">
                    <?= htmlspecialchars($row['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="player1_id">Player 1:</label>
        <select name="player1_id" id="player1_id" required></select>

        <label for="player2_id">Player 2:</label>
        <select name="player2_id" id="player2_id" required></select>

        <label for="stage">Match Stage:</label>
        <select name="stage" required>
            <option value="Pre Quarter Finals">Pre Quarter Finals</option>
            <option value="Quarter Finals">Quarter Finals</option>
            <option value="Semi Finals">Semi Finals</option>
            <option value="Finals">Finals</option>
        </select>

        <label for="date">Match Date:</label>
        <input type="date" name="date" required>

        <label for="match_time">Match Time (12-hour format AM/PM):</label>
        <input type="time" name="match_time" required>

        <label>Set 1:</label>
        <input type="number" name="set1_player1_points" placeholder="Player 1 Score" required>
        <input type="number" name="set1_player2_points" placeholder="Player 2 Score" required>

        <label>Set 2:</label>
        <input type="number" name="set2_player1_points" placeholder="Player 1 Score" required>
        <input type="number" name="set2_player2_points" placeholder="Player 2 Score" required>

        <label>Set 3:</label>
        <input type="number" name="set3_player1_points" placeholder="Player 1 Score">
        <input type="number" name="set3_player2_points" placeholder="Player 2 Score">

        <button type="submit" name="add_match">Add Match</button>
 
    </form>
    <?php endif; ?>
</div>

<script>
    const players = <?= json_encode($players) ?>;

    function updatePlayerDropdown() {
        const category = document.getElementById('category_id');
        const player1Dropdown = document.getElementById('player1_id');
        const player2Dropdown = document.getElementById('player2_id');

        const selectedCategory = category.options[category.selectedIndex];
        const categorySex = selectedCategory.dataset.sex;
        const categoryAge = selectedCategory.dataset.age;

        let maxAge = 100;
        if (categoryAge.includes("Under")) {
            maxAge = parseInt(categoryAge.replace(/\D/g, ''), 10);
        } else if (categoryAge.includes("Plus") || categoryAge.includes("+")) {
            maxAge = parseInt(categoryAge.replace(/\D/g, ''), 10);
        }

        player1Dropdown.innerHTML = '<option value="">Select Player 1</option>';
        player2Dropdown.innerHTML = '<option value="">Select Player 2</option>';

        players.forEach(player => {
            if (player.sex === categorySex && player.age < maxAge) {
                const option = `<option value="${player.id}">${player.name} (${player.age}, ${player.sex})</option>`;
                player1Dropdown.innerHTML += option;
                player2Dropdown.innerHTML += option;
            }
        });
    }
</script>

</body>
</html>