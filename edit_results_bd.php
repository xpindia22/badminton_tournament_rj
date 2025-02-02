<?php
include 'header.php';
//require_once 'permissions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_non_player();
require_once 'conn.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Define stages
$stages = ['Pre Quarter Finals', 'Quarter Finals', 'Semifinals', 'Finals', 'Preliminary'];

// Redirect logic handler
function redirect_with_message($location, $message) {
    header("Location: $location?$message");
    exit;
}
 
// Get logged-in user information
if (!isset($_SESSION['user_id'])) {
    redirect_with_message('login.php', 'error=not_logged_in');
}
$user_id = $_SESSION['user_id'];

// Handle updates and deletes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_match'])) {
        $match_id = $_POST['match_id'] ?? null;
        $stage = $_POST['stage'] ?? null;
        $match_date = $_POST['match_date'] ?? null;
        $match_time = $_POST['match_time'] ?? null;

        $set1_team1_points = $_POST['set1_team1_points'] ?? 0;
        $set1_team2_points = $_POST['set1_team2_points'] ?? 0;
        $set2_team1_points = $_POST['set2_team1_points'] ?? 0;
        $set2_team2_points = $_POST['set2_team2_points'] ?? 0;
        $set3_team1_points = $_POST['set3_team1_points'] ?? 0;
        $set3_team2_points = $_POST['set3_team2_points'] ?? 0;

        // Validate mandatory fields
        if (!$stage || !$match_date || !$match_time) {
            redirect_with_message('results_bd.php', 'error=missing_fields');
        }

        // Validate that stage is one of the allowed values
        if (!in_array($stage, $stages)) {
            redirect_with_message('results_bd.php', 'error=invalid_stage');
        }

        $update_query = "
            UPDATE matches SET
                stage = ?,
                match_date = ?,
                match_time = ?,
                set1_team1_points = ?,
                set1_team2_points = ?,
                set2_team1_points = ?,
                set2_team2_points = ?,
                set3_team1_points = ?,
                set3_team2_points = ?
            WHERE id = ? AND (created_by = ? OR EXISTS (
                SELECT 1 FROM tournaments t 
                INNER JOIN tournament_moderators tm ON t.id = tm.tournament_id
                WHERE t.id = matches.tournament_id AND tm.user_id = ?
            ))
        ";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(
            "sssiiiiiiiii",
            $stage,
            $match_date,
            $match_time,
            $set1_team1_points,
            $set1_team2_points,
            $set2_team1_points,
            $set2_team2_points,
            $set3_team1_points,
            $set3_team2_points,
            $match_id,
            $user_id,
            $user_id
        );
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            redirect_with_message('results_bd.php', 'success=edited');
        } else {
            redirect_with_message('results_bd.php', 'error=no_changes');
        }
    }

    if (isset($_POST['delete_match'])) {
        $match_id = $_POST['match_id'] ?? null;
        $delete_query = "
            DELETE FROM matches 
            WHERE id = ? AND (created_by = ? OR EXISTS (
                SELECT 1 FROM tournaments t 
                INNER JOIN tournament_moderators tm ON t.id = tm.tournament_id
                WHERE t.id = matches.tournament_id AND tm.user_id = ?
            ))
        ";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("iii", $match_id, $user_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            redirect_with_message('results_bd.php', 'success=deleted');
        } else {
            redirect_with_message('results_bd.php', 'error=delete_failed');
        }
    }
}

// Fetch Boys Doubles matches with permission check
// Fetch Boys Doubles matches with permission check
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
        m.set3_team2_points,
        m.created_by
    FROM matches m
    INNER JOIN tournaments t ON m.tournament_id = t.id
    INNER JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.team1_player1_id = p1.id
    LEFT JOIN players p2 ON m.team1_player2_id = p2.id
    LEFT JOIN players p3 ON m.team2_player1_id = p3.id
    LEFT JOIN players p4 ON m.team2_player2_id = p4.id
    WHERE c.type = 'doubles' 
      AND c.sex = 'M'
      AND (
          m.created_by = ? OR EXISTS (
              SELECT 1 
              FROM tournament_moderators tm 
              WHERE tm.tournament_id = m.tournament_id 
                AND tm.user_id = ?
          )
      )
    ORDER BY m.id
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boys Doubles Match Results</title>
</head>
<body>
    <h1>Boys Doubles Match Results</h1>

    <!-- Results Table -->
    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellspacing="0" cellpadding="5">
            <tr>
                <th>Match ID</th>
                <th>Tournament</th>
                <th>Category</th>
                <th>Team 1</th>
                <th>Team 2</th>
                <th>Stage</th>
                <th>Match Date</th>
                <th>Match Time</th>
                <th>Set 1 (Team 1 - Team 2)</th>
                <th>Set 2 (Team 1 - Team 2)</th>
                <th>Set 3 (Team 1 - Team 2)</th>
                <th>Winner</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="post">
                    <?php
                    // Determine overall winner
                    $team1_points = $row['set1_team1_points'] + $row['set2_team1_points'] + $row['set3_team1_points'];
                    $team2_points = $row['set1_team2_points'] + $row['set2_team2_points'] + $row['set3_team2_points'];
                    $overall_winner = $team1_points > $team2_points ? 'Team 1' : ($team1_points < $team2_points ? 'Team 2' : 'Draw');
                    ?>
                    <tr>
                        <td><?= $row['match_id'] ?><input type="hidden" name="match_id" value="<?= $row['match_id'] ?>"></td>
                        <td><?= $row['tournament_name'] ?></td>
                        <td><?= $row['category_name'] ?></td>
                        <td><?= $row['team1_player1_name'] . " & " . $row['team1_player2_name'] ?></td>
                        <td><?= $row['team2_player1_name'] . " & " . $row['team2_player2_name'] ?></td>
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
                        <td>
                            <input type="number" name="set1_team1_points" value="<?= $row['set1_team1_points'] ?>" style="width: 50px;">
                            -
                            <input type="number" name="set1_team2_points" value="<?= $row['set1_team2_points'] ?>" style="width: 50px;">
                        </td>
                        <td>
                            <input type="number" name="set2_team1_points" value="<?= $row['set2_team1_points'] ?>" style="width: 50px;">
                            -
                            <input type="number" name="set2_team2_points" value="<?= $row['set2_team2_points'] ?>" style="width: 50px;">
                        </td>
                        <td>
                            <input type="number" name="set3_team1_points" value="<?= $row['set3_team1_points'] ?>" style="width: 50px;">
                            -
                            <input type="number" name="set3_team2_points" value="<?= $row['set3_team2_points'] ?>" style="width: 50px;">
                        </td>
                        <td><?= $overall_winner ?></td>
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
