<?php
// manage_match_permissions.php
include 'header.php';
require_once 'conn.php'; // Include database connection settings

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch championships
$championshipQuery = "SELECT id, name FROM tournaments ORDER BY name";
$championshipResult = $conn->query($championshipQuery);
if (!$championshipResult) {
    die("Error fetching championships: " . $conn->error);
}

// Fetch users
$userQuery = "SELECT id, username FROM users ORDER BY username";
$userResult = $conn->query($userQuery);
if (!$userResult) {
    die("Error fetching users: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['grant_permission'])) {
        $matchId = $_POST['match_id'];
        $userId = $_POST['user_id'];
        $permissionType = $_POST['permission_type'];

        $grantQuery = "INSERT INTO match_permissions (match_id, user_id, permission_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE permission_type = VALUES(permission_type)";
        $stmt = $conn->prepare($grantQuery);
        $stmt->bind_param('iis', $matchId, $userId, $permissionType);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Permission granted successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error granting permission: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['revoke_permission'])) {
        $matchId = $_POST['match_id'];
        $userId = $_POST['user_id'];

        $revokeQuery = "DELETE FROM match_permissions WHERE match_id = ? AND user_id = ?";
        $stmt = $conn->prepare($revokeQuery);
        $stmt->bind_param('ii', $matchId, $userId);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Permission revoked successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error revoking permission: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    }

    // Refresh the page to reflect changes
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Match Permissions</title>
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
    <script>
        function fetchMatches(championshipId) {
            if (championshipId === "") {
                document.getElementById('matches').innerHTML = "";
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `fetch_matches.php?championship_id=${championshipId}`, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('matches').innerHTML = this.responseText;
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <h1>Manage Match Permissions</h1>

    <form action="" method="POST">
        <label for="user_id">Select User:</label>
        <select name="user_id" id="user_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $userResult->fetch_assoc()) { ?>
                <option value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['username']); ?>
                </option>
            <?php } ?>
        </select>
        <br><br>

        <label for="championship_id">Select Championship:</label>
        <select name="championship_id" id="championship_id" onchange="fetchMatches(this.value)" required>
            <option value="">-- Select Championship --</option>
            <?php while ($championship = $championshipResult->fetch_assoc()) { ?>
                <option value="<?php echo $championship['id']; ?>">
                    <?php echo htmlspecialchars($championship['name']); ?>
                </option>
            <?php } ?>
        </select>
        <br><br>

        <div id="matches">
            <!-- Matches will be dynamically loaded here -->
        </div>
    </form>
</body>
</html>

<?php
$conn->close();
?>
