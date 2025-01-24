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

$userId = $_SESSION['user_id']; // Assuming user_id is stored in the session after login
$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['lock_tournament'])) {
        $lockedTournament = intval($_POST['tournament_id']);
        $_SESSION['locked_tournament'] = $lockedTournament;

        $stmt = $conn->prepare("SELECT name FROM tournaments WHERE id = ? AND (created_by = ? OR id IN (SELECT tournament_id FROM tournament_moderators WHERE user_id = ?))");
        $stmt->bind_param("iii", $lockedTournament, $userId, $userId);
        $stmt->execute();
        $stmt->bind_result($lockedTournamentName);
        if ($stmt->fetch()) {
            $_SESSION['locked_tournament_name'] = $lockedTournamentName;
            $message = "Tournament locked: " . htmlspecialchars($lockedTournamentName);
        } else {
            $message = "Unauthorized access to the selected tournament.";
            unset($_SESSION['locked_tournament']);
        }
        $stmt->close();
    } elseif (isset($_POST['unlock_tournament'])) {
        unset($_SESSION['locked_tournament'], $_SESSION['locked_tournament_name']);
        $lockedTournament = null;
    } else {
        // Fetch inputs with default values
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
            // Validate user's access to the tournament and category
            $stmt = $conn->prepare("SELECT COUNT(*) FROM tournament_categories tc
                INNER JOIN tournaments t ON tc.tournament_id = t.id
                WHERE tc.category_id = ? AND t.id = ? AND (t.created_by = ? OR t.id IN (SELECT tournament_id FROM tournament_moderators WHERE user_id = ?))");
            $stmt->bind_param("iiii", $category_id, $tournament_id, $userId, $userId);
            $stmt->execute();
            $stmt->bind_result($isAuthorized);
            $stmt->fetch();
            $stmt->close();

            if (!$isAuthorized) {
                $message = "Unauthorized access to this category.";
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
}

// Fetch tournaments created or moderated by the logged-in user
$tournaments = $conn->prepare("
    SELECT id, name 
    FROM tournaments 
    WHERE created_by = ? OR id IN (SELECT tournament_id FROM tournament_moderators WHERE user_id = ?)
");
$tournaments->bind_param("ii", $userId, $userId);
$tournaments->execute();
$tournamentResults = $tournaments->get_result();

if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM categories c 
        INNER JOIN tournament_categories tc ON c.id = tc.category_id 
        WHERE tc.tournament_id = ? AND c.name LIKE '%BD%'
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
} else {
    $categories = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Insert Boys Doubles Match</title>
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
</head>
<body>
    <div class="container">
        <h1>Insert Boys Doubles Match</h1>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!$lockedTournament): ?>
            <form method="post">
                <label for="tournament_id">Select Tournament:</label>
                <select name="tournament_id" id="tournament_id" required>
                    <option value="">Select Tournament</option>
                    <?php while ($row = $tournamentResults->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="lock_tournament">Lock Tournament</button>
            </form>
        <?php else: ?>
            <form method="post">
                <p>Locked Tournament: <?= htmlspecialchars($_SESSION['locked_tournament_name'] ?? 'Unknown') ?></p>
                <button type="submit" name="unlock_tournament">Unlock Tournament</button>
            </form>

            <form method="post">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" required onchange="loadPlayers(this.value)">
                    <option value="">Select Category</option>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>, <?= htmlspecialchars($row['sex']) ?>)
                        </option>
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
        <?php endif; ?>
    </div>
</body>
</html>
