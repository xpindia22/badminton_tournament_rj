<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once 'conn.php'; // Database connection
require_once 'auth.php'; // Authentication functions
require_once 'permissions.php'; // Permissions functions

// Ensure the user is logged in
redirect_if_not_logged_in();

// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Fetch tournaments with matches and moderators
$tournamentQuery = "
    SELECT t.id AS tournament_id,
           t.name AS tournament_name,
           IFNULL(GROUP_CONCAT(DISTINCT u.username SEPARATOR ', '), '') AS moderators,
           IFNULL(GROUP_CONCAT(DISTINCT 
               CONCAT(
                   'Match ID: ', m.id, 
                   ', Category: ', c.name, 
                   ', Players: ', 
                   COALESCE(p1.name, 'TBD'), ' vs ', COALESCE(p2.name, 'TBD'),
                   ', Date: ', COALESCE(m.match_date, 'TBD'),
                   ', Time: ', COALESCE(m.match_time, 'TBD')
               ) SEPARATOR '; '), '') AS matches,
           t.created_by AS tournament_owner
    FROM tournaments t
    LEFT JOIN tournament_moderators tm ON t.id = tm.tournament_id
    LEFT JOIN users u ON tm.user_id = u.id
    LEFT JOIN matches m ON t.id = m.tournament_id
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.player1_id = p1.id
    LEFT JOIN players p2 ON m.player2_id = p2.id
    GROUP BY t.id
    ORDER BY t.name
";
$tournamentResult = $conn->query($tournamentQuery);
if (!$tournamentResult) {
    die("Error fetching tournaments: " . $conn->error);
}

// Fetch users
$userQuery = "SELECT id, username FROM users ORDER BY username";
$userResult = $conn->query($userQuery);
if (!$userResult) {
    die("Error fetching users: " . $conn->error);
}

// Helper function to check if a user can edit/delete matches
function can_edit_or_delete($userId, $tournamentId, $conn) {
    $query = "
        SELECT 1
        FROM tournaments t
        LEFT JOIN tournament_moderators tm ON t.id = tm.tournament_id
        WHERE (t.created_by = ? OR tm.user_id = ?) AND t.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $userId, $userId, $tournamentId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Returns true if the user is either the creator or a moderator
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_match'])) {
        if (!isset($_POST['match_id']) || !is_numeric($_POST['match_id'])) {
            echo "<p style='color: red;'>Invalid match ID.</p>";
            exit;
        }

        $matchId = (int)$_POST['match_id'];

        // Verify the match exists and the user has permission
        $tournamentIdQuery = "SELECT tournament_id FROM matches WHERE id = ?";
        $stmt = $conn->prepare($tournamentIdQuery);
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $stmt->bind_result($tournamentId);
        if ($stmt->fetch() && can_edit_or_delete($userId, $tournamentId, $conn)) {
            header("Location: edit_match.php?match_id=" . $matchId);
            exit;
        } else {
            echo "<p style='color: red;'>You do not have permission to edit this match.</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_match'])) {
        $matchId = $_POST['match_id'];

        // Get the tournament ID for the match
        $tournamentIdQuery = "SELECT tournament_id FROM matches WHERE id = ?";
        $stmt = $conn->prepare($tournamentIdQuery);
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $stmt->bind_result($tournamentId);
        $stmt->fetch();
        $stmt->close();

        // Check if the user has permissions
        if (can_edit_or_delete($userId, $tournamentId, $conn)) {
            // Delete the match
            $deleteMatchQuery = "DELETE FROM matches WHERE id = ?";
            $stmt = $conn->prepare($deleteMatchQuery);
            $stmt->bind_param('i', $matchId);
            if ($stmt->execute()) {
                echo "<p style='color: green;'>Match deleted successfully.</p>";
            } else {
                echo "<p style='color: red;'>Error deleting match: " . htmlspecialchars($stmt->error) . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color: red;'>You do not have permission to delete this match.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Moderator</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Assign a Moderator to a Tournament</h1>
    <form action="" method="POST">
        <label for="tournament_id">Select Tournament:</label>
        <select name="tournament_id" id="tournament_id" required>
            <option value="">-- Select Tournament --</option>
            <?php $tournamentResult->data_seek(0); while ($tournament = $tournamentResult->fetch_assoc()) { ?>
                <option value="<?php echo $tournament['tournament_id']; ?>">
                    <?php echo htmlspecialchars($tournament['tournament_name']); ?>
                </option>
            <?php } ?>
        </select>
        <br><br>

        <label for="moderator_id">Select Moderator:</label>
        <select name="moderator_id" id="moderator_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $userResult->fetch_assoc()) { ?>
                <option value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['username']); ?>
                </option>
            <?php } ?>
        </select>
        <br><br>

        <button type="submit" name="edit">Add Moderator</button>
    </form>

    <h2>Tournaments and Moderators</h2>
    <table>
        <thead>
            <tr>
                <th>Tournament Name</th>
                <th>Moderators</th>
                <th>Matches</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tournamentResult->data_seek(0);
            while ($row = $tournamentResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['tournament_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['moderators']) . "</td>";
                echo "<td>";

                // Display matches
                $matches = $row['matches'] !== '' ? explode('; ', $row['matches']) : [];
                foreach ($matches as $match) {
                    echo htmlspecialchars($match);

                    // Check if the user can edit/delete this match
                    if (can_edit_or_delete($userId, $row['tournament_id'], $conn)) {
                        echo " <form action='' method='POST' style='display:inline;'>";
                        echo "<input type='hidden' name='match_id' value='" . htmlspecialchars(explode(': ', $match)[1]) . "'>";
                        echo "<button type='submit' name='edit_match'>Edit</button>";
                        echo "<button type='submit' name='delete_match'>Delete</button>";
                        echo "</form>";
                    }
                    echo "<br>";
                }
                echo "</td>";

                echo "<td>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>
