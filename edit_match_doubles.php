<?php include 'header.php'; ?>

<?php
// edit_match_doubles.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//require_once 'permissions.php';

require_once 'conn.php';

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
        c.type AS category_type, /* Doubles or Mixed Doubles */
        p1.name AS team1_player1_name,
        p2.name AS team1_player2_name,
        p3.name AS team2_player1_name,
        p4.name AS team2_player2_name,
        m.stage,
        m.match_date,
        m.match_time,
        m.set1_team1_points,
        m.set1_team2_points,
        m.set2_team1_points,
        m.set2_team2_points,
        m.set3_team1_points,
        m.set3_team2_points
    FROM matches m
    INNER JOIN tournaments t ON m.tournament_id = t.id
    INNER JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.team1_player1_id = p1.id
    LEFT JOIN players p2 ON m.team1_player2_id = p2.id
    LEFT JOIN players p3 ON m.team2_player1_id = p3.id
    LEFT JOIN players p4 ON m.team2_player2_id = p4.id
    WHERE c.type IN ('doubles', 'mixed doubles')
";

if ($tournament_id) {
    $query .= " AND m.tournament_id = $tournament_id";
}

if ($category_id) {
    $query .= " AND m.category_id = $category_id";
}

if ($player_id) {
    $query .= " AND (
        m.team1_player1_id = $player_id OR 
        m.team1_player2_id = $player_id OR 
        m.team2_player1_id = $player_id OR 
        m.team2_player2_id = $player_id
    )";
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
    <title>Doubles Match Management</title>
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
    <h1>Doubles Match Management</h1>

    <!-- Filter Form -->
    <form method="get">
        <!-- Similar filter form as above -->
    </form>

    <!-- Results Table -->
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Match ID</th>
                <th>Tournament</th>
                <th>Category</th>
                <th>Team 1</th>
                <th>Team 2</th>
                <th>Stage</th>
                <th>Match Date</th>
                <th>Match Time</th>
                <th>Set 1</th>
                <th>Set 2</th>
                <th>Set 3</th>
                <th>Winner</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): 
                $team1 = $row['team1_player1_name'] . " & " . $row['team1_player2_name'];
                $team2 = $row['team2_player1_name'] . " & " . $row['team2_player2_name'];
                $team1_total = $row['set1_team1_points'] + $row['set2_team1_points'] + $row['set3_team1_points'];
                $team2_total = $row['set1_team2_points'] + $row['set2_team2_points'] + $row['set3_team2_points'];
                $winner = $team1_total > $team2_total ? $team1 : ($team1_total < $team2_total ? $team2 : 'Draw');
            ?>
                <tr>
                    <td><?= $row['match_id'] ?></td>
                    <td><?= $row['tournament_name'] ?></td>
                    <td><?= $row['category_name'] ?></td>
                    <td><?= $team1 ?></td>
                    <td><?= $team2 ?></td>
                    <td><?= $row['stage'] ?></td>
                    <td><?= $row['match_date'] ? date("d-m-Y", strtotime($row['match_date'])) : 'N/A' ?></td>
                    <td><?= $row['match_time'] ? date("h:i A", strtotime($row['match_time'])) : 'N/A' ?></td>
                    <td><?= $row['set1_team1_points'] . ' - ' . $row['set1_team2_points'] ?></td>
                    <td><?= $row['set2_team1_points'] . ' - ' . $row['set2_team2_points'] ?></td>
                    <td><?= $row['set3_team1_points'] . ' - ' . $row['set3_team2_points'] ?></td>
                    <td><?= $winner ?></td>
                    <td>
                        <?php if ($_SESSION['permissions']['edit']): ?>
                            <a href="edit_match_doubles.php?id=<?= $row['match_id'] ?>">Edit</a>
                        <?php endif; ?>
                        <?php if ($_SESSION['permissions']['delete']): ?>
                            <a href="delete_match_doubles.php?id=<?= $row['match_id'] ?>" onclick="return confirm('Are you sure you want to delete this match?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No matches found for the selected filters.</p>
    <?php endif; ?>
    <?php $conn->close(); ?>
</body>
</html>
