<?php include 'header.php'; ?>
<!-- Rest of your page content -->

<?php
// edit_player.php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin()) {
    die("Access denied.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p class='error'>Invalid player ID.</p>");
}

$player_id = intval($_GET['id']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];

    $stmt = $conn->prepare("
        UPDATE players 
        SET name = ?, dob = ?, age = ?, sex = ?, uid = ?
        WHERE id = ?
    ");
    if (!$stmt) {
        die("<p class='error'>Database error: " . $conn->error . "</p>");
    }
    $stmt->bind_param("ssissi", $name, $dob, $age, $sex, $uid, $player_id);
    if ($stmt->execute()) {
        header("Location: insert_player.php");
        exit;
    } else {
        $message = "Error updating player: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch player details
$stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();
$stmt->close();

if (!$player) {
    die("<p class='error'>Player not found.</p>");
}

// Fetch all players
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
    <title>Edit Player</title>
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
        <h1>Edit Player</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" class="form-styled">
            <label for="name">Player Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($player['name']) ?>" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($player['dob']) ?>" onchange="calculateAge()" required>

            <label for="age">Age:</label>
            <input type="number" name="age" id="age" value="<?= htmlspecialchars($player['age']) ?>" readonly required>

            <label for="sex">Sex:</label>
            <select name="sex" id="sex" required>
                <option value="M" <?= $player['sex'] === 'M' ? 'selected' : '' ?>>Male</option>
                <option value="F" <?= $player['sex'] === 'F' ? 'selected' : '' ?>>Female</option>
            </select>

            <label for="uid">Unique ID:</label>
            <input type="text" name="uid" id="uid" value="<?= htmlspecialchars($player['uid']) ?>" required>

            <button type="submit" class="btn-primary">Save Changes</button>
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
