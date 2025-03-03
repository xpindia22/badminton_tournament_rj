<?php
// insert_player.php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin() && !is_user()) {
    die("Access denied.");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];
    $user_id = $_SESSION['user_id'];

    // Check if player exists
    $stmt = $conn->prepare("SELECT id FROM players WHERE uid = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $stmt->bind_result($player_id);
    $player_exists = $stmt->fetch();
    $stmt->close();

    if ($player_exists) {
        // Link player to current user if not already linked
        $stmt = $conn->prepare("SELECT id FROM player_access WHERE player_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $player_id, $user_id);
        $stmt->execute();
        $access_exists = $stmt->fetch();
        $stmt->close();

        if (!$access_exists) {
            $stmt = $conn->prepare("INSERT INTO player_access (player_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $player_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Player linked to your account.";
        } else {
            $message = "Player already linked to your account.";
        }
    } else {
        // Add new player
        $stmt = $conn->prepare("INSERT INTO players (name, dob, age, sex, uid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $name, $dob, $age, $sex, $uid);
        if ($stmt->execute()) {
            $player_id = $stmt->insert_id;
            $stmt->close();

            // Link new player to the current user
            $stmt = $conn->prepare("INSERT INTO player_access (player_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $player_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Player added and linked successfully.";
        } else {
            $message = "Error adding player: " . $stmt->error;
        }
    }
}

// Fetch players linked to the current user
$players = $conn->query("
    SELECT p.*, GROUP_CONCAT(u.username) AS linked_users
    FROM players p
    LEFT JOIN player_access pa ON p.id = pa.player_id
    LEFT JOIN users u ON pa.user_id = u.id
    GROUP BY p.id
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Player</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function calculateAge() {
            const dob = document.getElementById('dob').value;
            const ageField = document.getElementById('age');
            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageField.value = age;
            }
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Insert Player</h1>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" class="form-styled">
            <label for="name">Player Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" onchange="calculateAge()" required>

            <label for="age">Age:</label>
            <input type="number" name="age" id="age" readonly required>

            <label for="sex">Sex:</label>
            <select name="sex" id="sex" required>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>

            <label for="uid">Unique ID:</label>
            <input type="text" name="uid" id="uid" required>

            <button type="submit" class="btn-primary">Add Player</button>
        </form>

        <h2>All Players</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>UID</th>
                    <th>Linked Users</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $players->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['dob']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td><?= htmlspecialchars($row['sex']) ?></td>
                        <td><?= htmlspecialchars($row['uid']) ?></td>
                        <td><?= htmlspecialchars($row['linked_users'] ?: 'No users linked') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
