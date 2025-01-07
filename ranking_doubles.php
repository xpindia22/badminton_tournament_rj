<?php
include 'header.php';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'conn.php';

// Fetch tournaments for dropdown
$tournaments = $conn->query("SELECT id, name FROM tournaments ORDER BY name ASC");

// Fetch categories dynamically based on the selected tournament
$selected_tournament = isset($_GET['tournament']) ? $_GET['tournament'] : '';
if ($selected_tournament) {
    $categories_query = "
        SELECT DISTINCT categories.id, categories.name 
        FROM categories 
        JOIN matches ON categories.id = matches.category_id
        WHERE matches.tournament_id = " . intval($selected_tournament) . " 
        AND (categories.name LIKE '%BD%' OR categories.name LIKE '%GD%' OR categories.name LIKE '%XD%')
        ORDER BY categories.name ASC
    ";
} else {
    $categories_query = "
        SELECT DISTINCT categories.id, categories.name 
        FROM categories 
        WHERE categories.name LIKE '%BD%' OR categories.name LIKE '%GD%' OR categories.name LIKE '%XD%'
        ORDER BY categories.name ASC
    ";
}
$categories = $conn->query($categories_query);

// Fetch players
$players = $conn->query("SELECT id, name FROM players ORDER BY name ASC");

// Get filter values from the request
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_player = isset($_GET['player']) ? $_GET['player'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build the WHERE clause dynamically
$where_clauses = [];
if ($selected_tournament) {
    $where_clauses[] = "tournaments.id = " . intval($selected_tournament);
}
if ($selected_category) {
    $where_clauses[] = "categories.id = " . intval($selected_category);
}
if ($selected_player) {
    $where_clauses[] = "(matches.team1_player1_id = " . intval($selected_player) . " 
        OR matches.team1_player2_id = " . intval($selected_player) . " 
        OR matches.team2_player1_id = " . intval($selected_player) . " 
        OR matches.team2_player2_id = " . intval($selected_player) . ")";
}
if ($selected_date) {
    $where_clauses[] = "DATE(matches.match_date) = '" . $conn->real_escape_string($selected_date) . "'";
}
$where_clauses[] = "(categories.name LIKE '%BD%' OR categories.name LIKE '%GD%' OR categories.name LIKE '%XD%')"; // Only Doubles categories

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Query for Doubles rankings
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
    <title>Doubles Rankings</title>
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
    <h1>Doubles Rankings</h1>

    <!-- Filters -->
    <form method="GET" action="">
        <label for="tournament">Tournament:</label>
        <select name="tournament" id="tournament" onchange="this.form.submit()">
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
            <?php if ($categories): ?>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $selected_category == $row['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
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
        echo "<tr><th>Category</th><th>Team Name</th><th>No of Matches</th><th>Total Points</th></tr>";

        // Display data in a table
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['team_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['no_of_matches']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No data available.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
