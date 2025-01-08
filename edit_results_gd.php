<?php
// edit_results_gd.php
include 'header.php';
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Define stages
$stages = ['Preliminary', 'Quarterfinal', 'Semifinal', 'Final', 'Champion'];

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_match'])) {
        $match_id = $_POST['match_id'];
        $stage = $_POST['stage'];
        $match_date = $_POST['match_date'];
        $match_time = $_POST['match_time'];
        $set1_team1_points = $_POST['set1_team1_points'];
        $set1_team2_points = $_POST['set1_team2_points'];
        $set2_team1_points = $_POST['set2_team1_points'];
        $set2_team2_points = $_POST['set2_team2_points'];
        $set3_team1_points = $_POST['set3_team1_points'];
        $set3_team2_points = $_POST['set3_team2_points'];

        $update_query = "
            UPDATE matches SET
                stage = '$stage',
                match_date = '$match_date',
                match_time = '$match_time',
                set1_team1_points = $set1_team1_points,
                set1_team2_points = $set1_team2_points,
                set2_team1_points = $set2_team1_points,
                set2_team2_points = $set2_team2_points,
                set3_team1_points = $set3_team1_points,
                set3_team2_points = $set3_team2_points
            WHERE id = $match_id
        ";
        $conn->query($update_query);
    }

    if (isset($_POST['delete_match'])) {
        $match_id = $_POST['match_id'];
        $delete_query = "DELETE FROM matches WHERE id = $match_id";
        $conn->query($delete_query);
    }
}

// Fetch data for Girls Doubles
$query = "
    SELECT 
        m.id AS match_id,
        t.name AS tournament_name,
        c.name AS category_name,
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
    WHERE c.type = 'doubles' AND c.sex = 'F'
    ORDER BY m.id
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Girls Doubles Match Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle; }
        th { background-color: #f4f4f4; }
        td.team-column { width: 25%; text-align: left; }
        td.set-column { width: 5%; text-align: center; }
        form { margin-bottom: 20px; }
        label, select, button { margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Girls Doubles Match Results</h1>

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
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="post">
                    <tr>
                        <td><?= $row['match_id'] ?><input type="hidden" name="match_id" value="<?= $row['match_id'] ?>"></td>
                        <td><?= $row['tournament_name'] ?></td>
                        <td><?= $row['category_name'] ?></td>
                        <td class="team-column"><?= $row['team1_player1_name'] . " & " . $row['team1_player2_name'] ?></td>
                        <td class="team-column"><?= $row['team2_player1_name'] . " & " . $row['team2_player2_name'] ?></td>
                        <td>
                            <select name="stage">
                                <?php foreach ($stages as $stage): ?>
                                    <option value="<?= $stage ?>" <?= $row['stage'] === $stage ? 'selected' : '' ?>>
                                        <?= $stage ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="date" name="match_date" value="<?= $row['match_date'] ?>"></td>
                        <td><input type="time" name="match_time" value="<?= $row['match_time'] ?>"></td>
                        <td class="set-column"><input type="number" name="set1_team1_points" value="<?= $row['set1_team1_points'] ?>"> - <input type="number" name="set1_team2_points" value="<?= $row['set1_team2_points'] ?>"></td>
                        <td class="set-column"><input type="number" name="set2_team1_points" value="<?= $row['set2_team1_points'] ?>"> - <input type="number" name="set2_team2_points" value="<?= $row['set2_team2_points'] ?>"></td>
                        <td class="set-column"><input type="number" name="set3_team1_points" value="<?= $row['set3_team1_points'] ?>"> - <input type="number" name="set3_team2_points" value="<?= $row['set3_team2_points'] ?>"></td>
                        <td><?= $row['set1_team1_points'] + $row['set2_team1_points'] + $row['set3_team1_points'] > $row['set1_team2_points'] + $row['set2_team2_points'] + $row['set3_team2_points'] ? "Team 1" : "Team 2" ?></td>
                        <td>
                            <button type="submit" name="edit_match">Edit</button>
                            <button type="submit" name="delete_match" onclick="return confirm('Are you sure you want to delete this match?')">Delete</button>
                        </td>
                    </tr>
                </form>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No matches found.</p>
    <?php endif; ?>
    <?php $conn->close(); ?>
</body>
</html>
