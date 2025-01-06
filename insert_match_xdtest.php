<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'header.php';
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;

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
        // Fetch inputs with default values to avoid warnings
        $tournament_id = $lockedTournament ?? ($_POST['tournament_id'] ?? null);
        $category_id = $_POST['category_id'] ?? null;
        $team1_player1_id = $_POST['team1_player1_id'] ?? null;
        $team1_player2_id = $_POST['team1_player2_id'] ?? null;
        $team2_player1_id = $_POST['team2_player1_id'] ?? null;
        $team2_player2_id = $_POST['team2_player2_id'] ?? null;
        $stage = $_POST['stage'] ?? null;

        $dateInput = $_POST['date'] ?? null;
        $match_date = $dateInput ? DateTime::createFromFormat('Y-m-d', $dateInput) : null;
        if ($match_date === false) {
            $message = "Invalid date format!";
        } else {
            $match_date = $match_date ? $match_date->format('Y-m-d') : null; 
        }

        $match_time = $_POST['time'] ?? null;
        $set1_team1 = $_POST['set1_team1_points'] ?? null;
        $set1_team2 = $_POST['set1_team2_points'] ?? null;
        $set2_team1 = $_POST['set2_team1_points'] ?? null;
        $set2_team2 = $_POST['set2_team2_points'] ?? null;
        $set3_team1 = $_POST['set3_team1_points'] ?? 0;
        $set3_team2 = $_POST['set3_team2_points'] ?? 0;

        // Validate required fields
        if (empty($tournament_id) || empty($category_id) || empty($stage) || empty($match_date) || empty($match_time)) {
            $message = "Please fill in all required fields.";
        } else {
            // Insert match details into the database
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

$tournaments = $conn->query("SELECT id, name FROM tournaments");

if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM categories c 
        INNER JOIN tournament_categories tc ON c.id = tc.category_id 
        WHERE tc.tournament_id = ? 
        AND c.name LIKE '%XD%'
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
} else {
    $categories = $conn->query("
        SELECT id, name, age_group, sex 
        FROM categories
        WHERE name LIKE '%XD%'
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Doubles Match</title>
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Insert Doubles Match</h1>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
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
            <select name="category_id" id="category_id" required onchange="loadPlayers(this.value)">
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>, <?= htmlspecialchars($row['sex']) ?>)</option>
                <?php endwhile; ?>
            </select>

            <label for="team1_player1_id">Team 1 - Player 1:</label>
            <select name="team1_player1_id" id="team1_player1_id" required></select>

            <label for="team1_player2_id">Team 1 - Player 2:</label>
            <select name="team1_player2_id" id="team1_player2_id" required></select>

            <label for="team2_player1_id">Team 2 - Player 1:</label>
            <select name="team2_player1_id" id="team2_player1_id" required></select>

            <label for="team2_player2_id">Team 2 - Player 2:</label>
            <select name="team2_player2_id" id="team2_player2_id" required></select>

            <label for="stage">Match Stage:</label>
            <select name="stage" id="stage" required>
                <option value="">Select Stage</option>
                <option value="Pre Quarter Finals">Pre Quarter Finals</option>
                <option value="Quarter Finals">Quarter Finals</option>
                <option value="Semi Finals">Semi Finals</option>
                <option value="Finals">Finals</option>
            </select>

            <label for="date">Match Date:</label>
            <input type="date" name="date" id="date" required>

            <label for="time">Match Time:</label>
            <input type="time" name="time" id="time" required>

            <label for="set1_team1_points">Set 1 Team 1 Points:</label>
            <input type="number" name="set1_team1_points" id="set1_team1_points" required>

            <label for="set1_team2_points">Set 1 Team 2 Points:</label>
            <input type="number" name="set1_team2_points" id="set1_team2_points" required>

            <label for="set2_team1_points">Set 2 Team 1 Points:</label>
            <input type="number" name="set2_team1_points" id="set2_team1_points" required>

            <label for="set2_team2_points">Set 2 Team 2 Points:</label>
            <input type="number" name="set2_team2_points" id="set2_team2_points" required>

            <label for="set3_team1_points">Set 3 Team 1 Points:</label>
            <input type="number" name="set3_team1_points" id="set3_team1_points">

            <label for="set3_team2_points">Set 3 Team 2 Points:</label>
            <input type="number" name="set3_team2_points" id="set3_team2_points">

            <button type="submit">Add Match</button>
        </form>
    </div>

    <script>
        function loadPlayers(categoryId) {
            if (!categoryId) return;

            fetch(`get_players.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    const playerSelects = ['team1_player1_id', 'team1_player2_id', 'team2_player1_id', 'team2_player2_id'];
                    playerSelects.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">Select Player</option>';
                        data.forEach(player => {
                            const option = document.createElement('option');
                            option.value = player.id;
                            option.textContent = `${player.name} (${player.age} years, ${player.sex})`;
                            select.appendChild(option);
                        });
                    });
                })
                .catch(error => console.error('Error fetching players:', error));
        }
    </script>
</body>
</html>
