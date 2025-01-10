<?php
include 'header.php';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'conn.php';
//require_once 'permissions.php';

// Fetch filter data
$tournaments = $conn->query("SELECT id, name FROM tournaments ORDER BY name ASC");
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$players = $conn->query("SELECT id, name FROM players ORDER BY name ASC");

// Get filter values from the request
$selected_tournament = isset($_GET['tournament']) ? $_GET['tournament'] : '';
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_player = isset($_GET['player']) ? $_GET['player'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$ranking_type = isset($_GET['ranking_type']) ? $_GET['ranking_type'] : 'singles'; // Default to singles

// Build the WHERE clause dynamically
$where_clauses = [];
if ($selected_tournament) {
    $where_clauses[] = "tournaments.id = " . intval($selected_tournament);
}
if ($selected_category) {
    $where_clauses[] = "categories.id = " . intval($selected_category);
}
if ($selected_player) {
    $where_clauses[] = "(matches.player1_id = " . intval($selected_player) . " OR matches.player2_id = " . intval($selected_player) . ")";
}
if ($selected_date) {
    $where_clauses[] = "DATE(matches.match_date) = '" . $conn->real_escape_string($selected_date) . "'";
}
// Add condition to filter categories containing BD, GD, XD
$where_clauses[] = "categories.name LIKE '%BD%' OR categories.name LIKE '%GD%' OR categories.name LIKE '%XD%'";

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Adjust query based on ranking type
if ($ranking_type === 'team') {
    // Team-based ranking for doubles and mixed doubles
    $query = "
        SELECT 
            categories.name AS category_name,
            CONCAT(player1.name, ' / ', player2.name) AS team_name,
            COUNT(matches.id) AS no_of_matches,
            SUM(matches.set1_team1_points + matches.set2_team1_points + matches.set3_team1_points) AS total_points
        FROM 
            matches
        JOIN 
            categories ON matches.category_id = categories.id
        JOIN 
            players AS player1 ON matches.team1_player1_id = player1.id
        JOIN 
            players AS player2 ON matches.team1_player2_id = player2.id
        JOIN 
            tournaments ON matches.tournament_id = tournaments.id
        $where_sql
        GROUP BY 
            team_name, categories.name
        ORDER BY 
            categories.name ASC, total_points DESC;
    ";
} else {
    // Player-based ranking for singles
    $query = "
        SELECT 
            categories.name AS category_name,
            players.name AS player_name,
            players.uid AS player_uid,
            players.sex AS player_sex,
            players.age AS player_age,
            COUNT(matches.id) AS no_of_matches,
            SUM(
                CASE WHEN matches.player1_id = players.id THEN 
                    (matches.set1_player1_points + matches.set2_player1_points + matches.set3_player1_points)
                WHEN matches.player2_id = players.id THEN
                    (matches.set1_player2_points + matches.set2_player2_points + matches.set3_player2_points)
                ELSE 0 END
            ) AS total_points
        FROM 
            players
        JOIN 
            matches ON matches.player1_id = players.id OR matches.player2_id = players.id
        JOIN 
            categories ON matches.category_id = categories.id
        JOIN 
            tournaments ON matches.tournament_id = tournaments.id
        $where_sql
        GROUP BY 
            categories.name, players.id
        ORDER BY 
            categories.name ASC, total_points DESC;
    ";
}

$result = $conn->query($query);

if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rankings</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1><?php echo $ranking_type === 'team' ? 'Team Rankings (Doubles & Mixed Doubles)' : 'Singles Rankings'; ?></h1>

    <!-- Filters -->
    <form method="GET" action="">
        <label for="ranking_type">Ranking Type:</label>
        <select name="ranking_type" id="ranking_type">
            <option value="singles" <?php echo $ranking_type === 'singles' ? 'selected' : ''; ?>>Singles</option>
            <option value="team" <?php echo $ranking_type === 'team' ? 'selected' : ''; ?>>Team (Doubles & Mixed Doubles)</option>
        </select>

        <label for="tournament">Tournament:</label>
        <select name="tournament" id="tournament">
            <option value="">All</option>
            <?php while ($row = $tournaments->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php echo $selected_tournament == $row['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="">All</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php echo $selected_category == $row['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="player">Player:</label>
        <select name="player" id="player">
            <option value="">All</option>
            <?php while ($row = $players->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php echo $selected_player == $row['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="date">Date:</label>
        <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($selected_date); ?>">

        <button type="submit">Filter</button>
    </form>

    <p>Date: <?php echo date('Y-m-d'); ?></p>
    <p>Time: <?php echo date('H:i:s'); ?></p>
    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        if ($ranking_type === 'team') {
            echo "<tr><th>Category</th><th>Team Name</th><th>No of Matches</th><th>Total Points</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['team_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['no_of_matches']) . "</td>";
                echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><th>Category</th><th>Player Name</th><th>UID</th><th>Sex</th><th>Age</th><th>No of Matches</th><th>Total Points</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['player_uid']) . "</td>";
                echo "<td>" . htmlspecialchars($row['player_sex']) . "</td>";
                echo "<td>" . htmlspecialchars($row['player_age']) . "</td>";
                echo "<td>" . htmlspecialchars($row['no_of_matches']) . "</td>";
                echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p>No data available.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
