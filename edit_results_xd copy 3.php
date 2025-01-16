<?php
// Start output buffering
ob_start();

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

// Redirect helper function
function redirect_with_message($location, $message) {
    header("Location: $location?$message");
    exit;
}

// Ensure the user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];

// Handle POST requests
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

        if (!$match_id || !$stage || !$match_date || !$match_time) {
            redirect_with_message('edit_results_xd.php', 'error=missing_fields');
        }

        if (!in_array($stage, $stages)) {
            redirect_with_message('edit_results_xd.php', 'error=invalid_stage');
        }

        // Update query with access control
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
        if ($stmt) {
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
                redirect_with_message('edit_results_xd.php', 'success=match_updated');
            } else {
                redirect_with_message('edit_results_xd.php', 'error=no_changes');
            }
        } else {
            redirect_with_message('edit_results_xd.php', 'error=query_failed');
        }
    }

    if (isset($_POST['delete_match'])) {
        $match_id = $_POST['match_id'] ?? null;

        if ($match_id) {
            // Delete query with access control
            $delete_query = "
                DELETE FROM matches 
                WHERE id = ? AND (created_by = ? OR EXISTS (
                    SELECT 1 FROM tournaments t 
                    INNER JOIN tournament_moderators tm ON t.id = tm.tournament_id
                    WHERE t.id = matches.tournament_id AND tm.user_id = ?
                ))
            ";
            $stmt = $conn->prepare($delete_query);
            if ($stmt) {
                $stmt->bind_param("iii", $match_id, $user_id, $user_id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    redirect_with_message('edit_results_xd.php', 'success=match_deleted');
                } else {
                    redirect_with_message('edit_results_xd.php', 'error=delete_failed');
                }
            } else {
                redirect_with_message('edit_results_xd.php', 'error=query_failed');
            }
        }
    }
}

// Fetch matches for Mixed Doubles with access control
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
    WHERE c.type = 'mixed doubles'
    AND (m.created_by = ? OR EXISTS (
        SELECT 1 FROM tournaments t 
        INNER JOIN tournament_moderators tm ON t.id = tm.tournament_id
        WHERE t.id = m.tournament_id AND tm.user_id = ?
    ))
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
    <title>Girls Doubles Match Results</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Girls Doubles Match Results</h1>
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
                <th>Set 1 (Team 1 - Team 2)</th>
                <th>Set 2 (Team 1 - Team 2)</th>
                <th>Set 3 (Team 1 - Team 2)</th>
                <th>Winner</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="post">
                    <tr>
                        <td><?= $row['match_id'] ?><input type="hidden" name="match_id" value="<?= $row['match_id'] ?>"></td>
                        <td><?= $row['tournament_name'] ?></td>
                        <td><?= $row['category_name'] ?></td>
                        <td><?= $row['team1_player1_name'] . " & " . $row['team1_player2_name'] ?></td>
                        <td><?= $row['team2_player1_name'] . " & " . $row['team2_player2_name'] ?></td>
                        <td>
                            <select name="stage">
                                <?php foreach ($stages as $stage): ?>
                                    <option value="<?= $stage ?>" <?= $row['stage'] === $stage ? 'selected' : '' ?>><?= $stage ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="date" name="match_date" value="<?= $row['match_date'] ?>"></td>
                        <td><input type="time" name="match_time" value="<?= $row['match_time'] ?>"></td>
                        <td>
                            <input type="number" name="set1_team1_points" value="<?= $row['set1_team1_points'] ?>" style="width: 50px;"> -
                            <input type="number" name="set1_team2_points" value="<?= $row['set1_team2_points'] ?>" style="width: 50px;">
                        </td>
                        <td>
                            <input type="number" name="set2_team1_points" value="<?= $row['set2_team1_points'] ?>" style="width: 50px;"> -
                            <input type="number" name="set2_team2_points" value="<?= $row['set2_team2_points'] ?>" style="width: 50px;">
                        </td>
                        <td>
                            <input type="number" name="set3_team1_points" value="<?= $row['set3_team1_points'] ?>" style="width: 50px;"> -
                            <input type="number" name="set3_team2_points" value="<?= $row['set3_team2_points'] ?>" style="width: 50px;">
                        </td>
                        <td>
                            <?= ($row['set1_team1_points'] + $row['set2_team1_points'] + $row['set3_team1_points']) > 
                                ($row['set1_team2_points'] + $row['set2_team2_points'] + $row['set3_team2_points']) 
                                ? 'Team 1' : 'Team 2' ?>
                        </td>
                        <td>
                            <button type="submit" name="edit_match">Edit</button>
                            <button type="submit" name="delete_match" onclick="return confirm('Are you sure?')">Delete</button>
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

<?php $conn->close(); ?>