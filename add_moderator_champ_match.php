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
    $result = $stmt->num_rows > 0;
    $stmt->free_result();
    $stmt->close();
    return $result;
}

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_match'])) {
        if (!isset($_POST['match_id']) || !is_numeric($_POST['match_id'])) {
            echo "<p style='color: red;'>Invalid match ID.</p>";
            var_dump($_POST); // Debugging: Output form submission data
            exit;
        }

        $matchId = (int)$_POST['match_id'];

        // Verify the match exists and the user has permission
        $tournamentIdQuery = "SELECT tournament_id FROM matches WHERE id = ?";
        $stmt = $conn->prepare($tournamentIdQuery);
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $stmt->bind_result($tournamentId);
        $stmt->fetch();
        $stmt->close();

        if ($tournamentId && can_edit_or_delete($userId, $tournamentId, $conn)) {
            header("Location: edit_match.php?match_id=" . $matchId);
            exit;
        } else {
            echo "<p style='color: red;'>You do not have permission to edit this match or the match does not exist.</p>";
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

                $matches = $row['matches'] !== '' ? explode('; ', $row['matches']) : [];
                foreach ($matches as $match) {
                    if (preg_match('/Match ID: (\d+),/', $match, $matchIdMatches)) {
                        $matchId = $matchIdMatches[1];
                        echo htmlspecialchars($match);

                        if (can_edit_or_delete($userId, $row['tournament_id'], $conn)) {
                            echo " <form action='' method='POST' style='display:inline;'>";
                            echo "<input type='hidden' name='match_id' value='" . htmlspecialchars($matchId) . "'>";
                            echo "<button type='submit' name='edit_match'>Edit</button>";
                            echo "</form>";
                        }
                        echo "<br>";
                    } else {
                        echo "<p style='color: red;'>Error extracting Match ID for: " . htmlspecialchars($match) . "</p>";
                    }
                }
                echo "</td>";
                echo "<td></td>";
                echo "</tr>";
            }
            $tournamentResult->free();
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>
