<?php
session_start();
ob_start(); // Start output buffering

include 'header.php'; // Ensure no output in header.php
require_once 'conn.php'; // Database connection settings
require_once 'admin_auth.php'; // Include admin authentication

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("Access denied: You must log in first.");
}

$username = $_SESSION['username'];

// Check if the user is listed in the authentication file
if (!array_key_exists($username, $adminAuth)) {
    die("Access denied: You do not have the required permissions.");
}

// Secondary authentication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_submit'])) {
    $provided_password = $_POST['auth_password'];
    $stored_password = $adminAuth[$username];

    if ($provided_password === $stored_password) {
        $_SESSION['double_authenticated'] = true;
    } else {
        die("<p style='color: red;'>Invalid secondary password. Access denied.</p>");
    }
}

// Display secondary authentication form if not authenticated
if (!isset($_SESSION['double_authenticated']) || $_SESSION['double_authenticated'] !== true) {
    echo <<<HTML
        <form method="POST" action="">
            <h1>Admin Authentication</h1>
            <label for="auth_password">Enter Secondary Password:</label>
            <input type="password" id="auth_password" name="auth_password" required>
            <button type="submit" name="auth_submit">Authenticate</button>
        </form>
    HTML;
    ob_end_flush(); // Ensure output is flushed properly
    exit;
}

// Authentication successful, proceed with the rest of the page
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tournaments
$tournamentQuery = "
    SELECT t.id, t.name AS tournament_name, 
    IFNULL(GROUP_CONCAT(u.username SEPARATOR ', '), '') AS moderators 
    FROM tournaments t 
    LEFT JOIN tournament_moderators tm ON t.id = tm.tournament_id 
    LEFT JOIN users u ON tm.user_id = u.id 
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $tournamentId = $_POST['tournament_id'];
        $moderatorId = $_POST['moderator_id'];
        $deleteQuery = "DELETE FROM tournament_moderators WHERE tournament_id = ? AND user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('ii', $tournamentId, $moderatorId);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Moderator removed from the tournament.</p>";
        } else {
            echo "<p style='color: red;'>Error removing moderator: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['edit'])) {
        $tournamentId = $_POST['tournament_id'];
        $moderatorId = $_POST['moderator_id'];

        $insertQuery = "INSERT INTO tournament_moderators (tournament_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = user_id";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('ii', $tournamentId, $moderatorId);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Moderator added to the tournament.</p>";
        } else {
            echo "<p style='color: red;'>Error adding moderator: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    }

    // Refresh the page to reflect changes
    header("Refresh:0");
    exit;
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
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>! You have successfully authenticated.</h1>

    <h2>Assign a Moderator to a Tournament</h2>
    <form action="" method="POST">
        <label for="tournament_id">Select Tournament:</label>
        <select name="tournament_id" id="tournament_id" required>
            <option value="">-- Select Tournament --</option>
            <?php $tournamentResult->data_seek(0); while ($tournament = $tournamentResult->fetch_assoc()) { ?>
                <option value="<?php echo $tournament['id']; ?>">
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
                $moderators = $row['moderators'] !== '' ? explode(', ', $row['moderators']) : [];
                foreach ($moderators as $moderator) {
                    $moderatorIdQuery = "SELECT id FROM users WHERE username = ?";
                    $stmt = $conn->prepare($moderatorIdQuery);
                    $stmt->bind_param('s', $moderator);
                    $stmt->execute();
                    $stmt->bind_result($moderatorId);
                    $stmt->fetch();
                    $stmt->close();

                    echo "<form action='' method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='tournament_id' value='" . $row['id'] . "'>";
                    echo "<input type='hidden' name='moderator_id' value='" . $moderatorId . "'>";
                    echo "<button type='submit' name='delete'>Remove " . htmlspecialchars($moderator) . "</button>";
                    echo "</form> ";
                }
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
ob_end_flush();
?>
