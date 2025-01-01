<?php
// results.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "xxx";
$dbname = "badminton_tournament";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch filters
$tournament_id = $_GET['tournament_id'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$player_id = $_GET['player_id'] ?? '';
$match_date = $_GET['match_date'] ?? '';
$datetime = $_GET['datetime'] ?? '';

// Fetch data for dropdowns
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name FROM categories");
$players = $conn->query("SELECT id, name FROM players");
$dates = $conn->query("SELECT DISTINCT match_date FROM matches ORDER BY match_date");
$datetimes = $conn->query("SELECT DISTINCT match_time FROM matches ORDER BY match_time");

// Build the query with optional filters
$query = "
    SELECT 
        m.id AS match_id,
        t.name AS tournament_name,
        c.name AS category_name,
        p1.name AS player1_name,
        p2.name AS player2_name,
        m.stage,
        m.match_date,
        m.match_time,
        m.set1_player1_points,
        m.set1_player2_points,
        m.set2_player1_points,
        m.set2_player2_points,
        m.set3_player1_points,
        m.set3_player2_points
    FROM matches m
    INNER JOIN tournaments t ON m.tournament_id = t.id
    INNER JOIN categories c ON m.category_id = c.id
    INNER JOIN players p1 ON m.player1_id = p1.id
    INNER JOIN players p2 ON m.player2_id = p2.id
    WHERE 1=1
";

if ($tournament_id) {
    $query .= " AND m.tournament_id = $tournament_id";
}

if ($category_id) {
    $query .= " AND m.category_id = $category_id";
}

if ($player_id) {
    $query .= " AND (m.player1_id = $player_id OR m.player2_id = $player_id)";
}

if ($match_date) {
    $query .= " AND m.match_date = '$match_date'";
}

if ($datetime) {
    $query .= " AND m.match_time = '$datetime'";
}

$query .= " ORDER BY m.id";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        form { margin-bottom: 20px; }
        label, select, button { margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Tournament Results</h1>

    <!-- Filter Form -->
    <form method="get">
        <label for="tournament_id">Tournament:</label>
        <select name="tournament_id" id="tournament_id">
            <option value="">All Tournaments</option>
            <?php while ($row = $tournaments->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= $tournament_id == $row['id'] ? 'selected' : '' ?>>
                    <?= $row['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id">
            <option value="">All Categories</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= $category_id == $row['id'] ? 'selected' : '' ?>>
                    <?= $row['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="player_id">Player:</label>
        <select name="player_id" id="player_id">
            <option value="">All Players</option>
            <?php while ($row = $players->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= $player_id == $row['id'] ? 'selected' : '' ?>>
                    <?= $row['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="match_date">Match Date:</label>
        <select name="match_date" id="match_date">
            <option value="">All Dates</option>
            <?php while ($row = $dates->fetch_assoc()): ?>
                <option value="<?= $row['match_date'] ?>" <?= $match_date == $row['match_date'] ? 'selected' : '' ?>>
                    <?= $row['match_date'] ? date("d-m-Y", strtotime($row['match_date'])) : 'N/A' ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="datetime">Match Time:</label>
        <select name="datetime" id="datetime">
            <option value="">All Times</option>
            <?php while ($row = $datetimes->fetch_assoc()): ?>
                <option value="<?= $row['match_time'] ?>" <?= $datetime == $row['match_time'] ? 'selected' : '' ?>>
                    <?= $row['match_time'] ? date("h:i A", strtotime($row['match_time'])) : 'N/A' ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Filter</button>
    </form>

    <!-- Results Table -->
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Match ID</th>
                <th>Tournament</th>
                <th>Category</th>
                <th>Player 1</th>
                <th>Player 2</th>
                <th>Stage</th>
                <th>Match Date</th>
                <th>Match Time</th>
                <th>Set 1</th>
                <th>Set 2</th>
                <th>Set 3</th>
                <th>Winner</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): 
                $p1_total = $row['set1_player1_points'] + $row['set2_player1_points'] + $row['set3_player1_points'];
                $p2_total = $row['set1_player2_points'] + $row['set2_player2_points'] + $row['set3_player2_points'];
                $winner = $p1_total > $p2_total ? $row['player1_name'] : ($p1_total < $p2_total ? $row['player2_name'] : 'Draw');
            ?>
                <tr>
                    <td><?= $row['match_id'] ?></td>
                    <td><?= $row['tournament_name'] ?></td>
                    <td><?= $row['category_name'] ?></td>
                    <td><?= $row['player1_name'] ?></td>
                    <td><?= $row['player2_name'] ?></td>
                    <td><?= $row['stage'] ?></td>
                    <td><?= $row['match_date'] ? date("d-m-Y", strtotime($row['match_date'])) : 'N/A' ?></td>
                    <td><?= $row['match_time'] ? date("h:i A", strtotime($row['match_time'])) : 'N/A' ?></td>
                    <td><?= $row['set1_player1_points'] ?> - <?= $row['set1_player2_points'] ?></td>
                    <td><?= $row['set2_player1_points'] ?> - <?= $row['set2_player2_points'] ?></td>
                    <td><?= $row['set3_player1_points'] ?> - <?= $row['set3_player2_points'] ?></td>
                    <td><?= $winner ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No matches found for the selected filters.</p>
    <?php endif; ?>
    <?php $conn->close(); ?>
</body>
</html>
