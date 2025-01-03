<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "conn.php";
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $match_type = $_POST['match_type'] ?? null;

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
        $tournament_id = $_POST['tournament_id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        
        if ($match_type === 'singles') {
            $player1_id = $_POST['player1_id'] ?? null;
            $player2_id = $_POST['player2_id'] ?? null;

            $stmt = $conn->prepare("INSERT INTO matches (
                tournament_id, category_id, player1_id, player2_id,
                set1_team1_points, set1_team2_points, set2_team1_points, 
                set2_team2_points, set3_team1_points, set3_team2_points
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "iiiiffffff",
                $tournament_id, $category_id, $player1_id, $player2_id,
                $_POST['set1_team1_points'], $_POST['set1_team2_points'],
                $_POST['set2_team1_points'], $_POST['set2_team2_points'],
                $_POST['set3_team1_points'] ?? 0, $_POST['set3_team2_points'] ?? 0
            );
        } elseif ($match_type === 'doubles') {
            $team1_player1_id = $_POST['team1_player1_id'] ?? null;
            $team1_player2_id = $_POST['team1_player2_id'] ?? null;
            $team2_player1_id = $_POST['team2_player1_id'] ?? null;
            $team2_player2_id = $_POST['team2_player2_id'] ?? null;

            $stmt = $conn->prepare("INSERT INTO matches (
                tournament_id, category_id, team1_player1_id, team1_player2_id, 
                team2_player1_id, team2_player2_id, set1_team1_points, 
                set1_team2_points, set2_team1_points, set2_team2_points, 
                set3_team1_points, set3_team2_points
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "iiiiffffff",
                $tournament_id, $category_id, $team1_player1_id, $team1_player2_id,
                $team2_player1_id, $team2_player2_id, 
                $_POST['set1_team1_points'], $_POST['set1_team2_points'],
                $_POST['set2_team1_points'], $_POST['set2_team2_points'],
                $_POST['set3_team1_points'] ?? 0, $_POST['set3_team2_points'] ?? 0
            );
        } else {
            die("Invalid match type selected.");
        }

        if ($stmt->execute()) {
            $message = "Match added successfully!";
        } else {
            $message = "Error adding match: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch tournaments
$tournaments = $conn->query("SELECT id, name FROM tournaments");

// Fetch categories for the locked tournament
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
} else {
    $categories = $conn->query("SELECT id, name, age_group, sex FROM categories");
}

// Fetch players
$players = $conn->query("SELECT id, name, age, sex FROM players");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
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

        .error {
            color: #dc3545;
        }

        .top-bar {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        .singles-field, .doubles-field {
            display: none;
        }
    </style>
    <script>
        const players = <?= json_encode($players->fetch_all(MYSQLI_ASSOC)) ?>;

        function updatePlayerDropdown() {
            const categoryId = document.getElementById('category_id').value;
            const singlesFields = document.querySelectorAll('.singles-field');
            const doublesFields = document.querySelectorAll('.doubles-field');

            const matchType = document.getElementById('match_type').value;

            if (matchType === 'singles') {
                singlesFields.forEach(field => field.style.display = 'block');
                doublesFields.forEach(field => field.style.display = 'none');
            } else {
                singlesFields.forEach(field => field.style.display = 'none');
                doublesFields.forEach(field => field.style.display = 'block');
            }

            if (categoryId) {
                const category = document.querySelector(`#category_id option[value="${categoryId}"]`);
                const ageGroup = category.dataset.ageGroup;
                const sex = category.dataset.sex;

                players.forEach(player => {
                    if ((sex === 'Any' || player.sex === sex) && isPlayerEligible(player.age, ageGroup)) {
                        const option = `<option value="${player.id}">${player.name}</option>`;

                        document.querySelectorAll('select.singles').forEach(dropdown => dropdown.innerHTML += option);
                        document.querySelectorAll('select.doubles').forEach(dropdown => dropdown.innerHTML += option);
                    }
                });
            }
        }

        function isPlayerEligible(playerAge, ageGroup) {
            const ageRange = ageGroup.match(/\d+/g);
            if (!ageRange) return true;
            const [minAge, maxAge] = ageRange.length === 2 ? ageRange : [0, ageRange[0]];
            return playerAge >= parseInt(minAge, 10) && playerAge <= parseInt(maxAge, 10);
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
    <div class="container">
        <h1>Insert Match</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
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
            <label for="match_type">Match Type:</label>
            <select name="match_type" id="match_type" onchange="updatePlayerDropdown()" required>
                <option value="singles">Singles</option>
                <option value="doubles">Doubles</option>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" onchange="updatePlayerDropdown()" required>
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" data-age-group="<?= $row['age_group'] ?>" data-sex="<?= $row['sex'] ?>">
                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>, <?= htmlspecialchars($row['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Singles Fields -->
            <div class="singles-field">
                <label for="player1_id">Player 1:</label>
                <select name="player1_id" id="player1_id" class="singles" required>
                    <option value="">Select Player</option>
                </select>

                <label for="player2_id">Player 2:</label>
                <select name="player2_id" id="player2_id" class="singles" required>
                    <option value="">Select Player</option>
                </select>
            </div>

            <!-- Doubles Fields -->
            <div class="doubles-field">
                <label for="team1_player1_id">Team 1 Player 1:</label>
                <select name="team1_player1_id" id="team1_player1_id" class="doubles" required>
                    <option value="">Select Player</option>
                </select>

                <label for="team1_player2_id">Team 1 Player 2:</label>
                <select name="team1_player2_id" id="team1_player2_id" class="doubles" required>
                    <option value="">Select Player</option>
                </select>

                <label for="team2_player1_id">Team 2 Player 1:</label>
                <select name="team2_player1_id" id="team2_player1_id" class="doubles" required>
                    <option value="">Select Player</option>
                </select>

                <label for="team2_player2_id">Team 2 Player 2:</label>
                <select name="team2_player2_id" id="team2_player2_id" class="doubles" required>
                    <option value="">Select Player</option>
                </select>
            </div>

            <!-- Scores -->
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
