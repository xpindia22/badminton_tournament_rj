<?php
//edit_match.php
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

if (is_logged_in()) {
    $username = $_SESSION['username'];
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p class='error'>Invalid match ID.</p>");
}

$match_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Verify ownership or admin access
$stmt = $conn->prepare("SELECT created_by FROM matches WHERE id = ?");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$stmt->bind_result($created_by);
if (!$stmt->fetch()) {
    die("<p class='error'>Match not found.</p>");
}
$stmt->close();

if ($created_by !== $user_id && !$is_admin) {
    die("<p class='error'>Access denied: You can only edit your own matches.</p>");
}

// Fetch match details
$stmt = $conn->prepare("
    SELECT m.*, t.name AS tournament_name, c.name AS category_name, 
           p1.name AS player1_name, p2.name AS player2_name
    FROM matches m
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.player1_id = p1.id
    LEFT JOIN players p2 ON m.player2_id = p2.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();
$match = $result->fetch_assoc();
$stmt->close();

if (!$match) {
    die("<p class='error'>Match not found.</p>");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = intval($_POST['tournament_id']);
    $category_id = intval($_POST['category_id']);
    $player1_id = intval($_POST['player1_id']);
    $player2_id = intval($_POST['player2_id']);
    $stage = $_POST['stage'];
    $set1_p1 = intval($_POST['set1_player1_points']);
    $set1_p2 = intval($_POST['set1_player2_points']);
    $set2_p1 = intval($_POST['set2_player1_points']);
    $set2_p2 = intval($_POST['set2_player2_points']);
    $set3_p1 = isset($_POST['set3_player1_points']) ? intval($_POST['set3_player1_points']) : null;
    $set3_p2 = isset($_POST['set3_player2_points']) ? intval($_POST['set3_player2_points']) : null;

    $allowed_stages = ['Pre Quarter Finals', 'Quarter Finals', 'Semi Finals', 'Finals'];
    if (!in_array($stage, $allowed_stages)) {
        die("Invalid stage value.");
    }

    $query = "
        UPDATE matches 
        SET tournament_id = ?, category_id = ?, player1_id = ?, player2_id = ?, stage = ?, 
            set1_player1_points = ?, set1_player2_points = ?, set2_player1_points = ?, 
            set2_player2_points = ?, set3_player1_points = IFNULL(?, set3_player1_points), 
            set3_player2_points = IFNULL(?, set3_player2_points)
        WHERE id = ?
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("<p class='error'>Database error: " . $conn->error . "</p>");
    }

    $stmt->bind_param(
        "iiiisiiiiiii", 
        $tournament_id, $category_id, $player1_id, $player2_id, $stage,
        $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2, $match_id
    );

    if ($stmt->execute()) {
        $message = "<p class='success'>Match updated successfully!</p>";
        // Refresh match data after update
        $stmt = $conn->prepare("
            SELECT m.*, t.name AS tournament_name, c.name AS category_name, 
                   p1.name AS player1_name, p2.name AS player2_name
            FROM matches m
            LEFT JOIN tournaments t ON m.tournament_id = t.id
            LEFT JOIN categories c ON m.category_id = c.id
            LEFT JOIN players p1 ON m.player1_id = p1.id
            LEFT JOIN players p2 ON m.player2_id = p2.id
            WHERE m.id = ?
        ");
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $match = $result->fetch_assoc();
        $stmt->close();
    } else {
        $message = "<p class='error'>Error updating match: " . $stmt->error . "</p>";
    }
}

// Calculate winner
$p1_total = $match['set1_player1_points'] + $match['set2_player1_points'] + $match['set3_player1_points'];
$p2_total = $match['set1_player2_points'] + $match['set2_player2_points'] + $match['set3_player2_points'];
$winner = $p1_total > $p2_total ? $match['player1_name'] : ($p1_total < $p2_total ? $match['player2_name'] : 'Draw');

// Fetch data for dropdowns
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name FROM categories");
$players = $conn->query("SELECT id, name FROM players");
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
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
    <div class="container">
        <h1>Edit Match</h1>
        <?= $message ?>
        <form method="post">
            <!-- Tournament, Category, Player Dropdowns, Stage, and Set Scores -->
            <label for="tournament_id">Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <?php while ($row = $tournaments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['tournament_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="player1_id">Player 1:</label>
            <select name="player1_id" id="player1_id" required>
                <?php while ($row = $players->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['player1_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="player2_id">Player 2:</label>
            <select name="player2_id" id="player2_id" required>
                <?php mysqli_data_seek($players, 0); ?>
                <?php while ($row = $players->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['player2_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="stage">Match Stage:</label>
            <select name="stage" id="stage" required>
                <option value="Pre Quarter Finals" <?= $match['stage'] == 'Pre Quarter Finals' ? 'selected' : '' ?>>Pre Quarter Finals</option>
                <option value="Quarter Finals" <?= $match['stage'] == 'Quarter Finals' ? 'selected' : '' ?>>Quarter Finals</option>
                <option value="Semi Finals" <?= $match['stage'] == 'Semi Finals' ? 'selected' : '' ?>>Semi Finals</option>
                <option value="Finals" <?= $match['stage'] == 'Finals' ? 'selected' : '' ?>>Finals</option>
            </select>

            <label for="set1_player1_points">Set 1 Player 1 Points:</label>
            <input type="number" name="set1_player1_points" id="set1_player1_points" value="<?= $match['set1_player1_points'] ?>" required>

            <label for="set1_player2_points">Set 1 Player 2 Points:</label>
            <input type="number" name="set1_player2_points" id="set1_player2_points" value="<?= $match['set1_player2_points'] ?>" required>

            <label for="set2_player1_points">Set 2 Player 1 Points:</label>
            <input type="number" name="set2_player1_points" id="set2_player1_points" value="<?= $match['set2_player1_points'] ?>" required>

            <label for="set2_player2_points">Set 2 Player 2 Points:</label>
            <input type="number" name="set2_player2_points" id="set2_player2_points" value="<?= $match['set2_player2_points'] ?>" required>

            <label for="set3_player1_points">Set 3 Player 1 Points:</label>
            <input type="number" name="set3_player1_points" id="set3_player1_points" value="<?= $match['set3_player1_points'] ?>">

            <label for="set3_player2_points">Set 3 Player 2 Points:</label>
            <input type="number" name="set3_player2_points" id="set3_player2_points" value="<?= $match['set3_player2_points'] ?>">

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>

        <h2>Match Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tournament</th>
                    <th>Category</th>
                    <th>Player 1</th>
                    <th>Player 2</th>
                    <th>Stage</th>
                    <th>Set 1</th>
                    <th>Set 2</th>
                    <th>Set 3</th>
                    <th>Winner</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($match['id']) ?></td>
                    <td><?= htmlspecialchars($match['tournament_name']) ?></td>
                    <td><?= htmlspecialchars($match['category_name']) ?></td>
                    <td><?= htmlspecialchars($match['player1_name']) ?></td>
                    <td><?= htmlspecialchars($match['player2_name']) ?></td>
                    <td><?= htmlspecialchars($match['stage']) ?></td>
                    <td><?= htmlspecialchars($match['set1_player1_points']) ?> - <?= htmlspecialchars($match['set1_player2_points']) ?></td>
                    <td><?= htmlspecialchars($match['set2_player1_points']) ?> - <?= htmlspecialchars($match['set2_player2_points']) ?></td>
                    <td><?= htmlspecialchars($match['set3_player1_points']) ?> - <?= htmlspecialchars($match['set3_player2_points']) ?></td>
                    <td><?= htmlspecialchars($winner) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
