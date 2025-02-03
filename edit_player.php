<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'header.php';
require_once 'conn.php';
require 'auth.php';
require_non_player();
// Ensure only logged-in users can access
redirect_if_not_logged_in();

// Check if the user is an admin
if (!is_admin()) {
    die("Access denied.");
}

// Validate UID
if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    die("<p class='error'>Invalid player UID.</p>");
}

$player_uid = intval($_GET['uid']);
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $dob = trim($_POST['dob']);
    $sex = trim($_POST['sex']);
    $new_uid = trim($_POST['uid']);

    // Calculate Age from DOB
    $stmt = $conn->prepare("SELECT TIMESTAMPDIFF(YEAR, ?, CURDATE()) AS age");
    $stmt->bind_param("s", $dob);
    $stmt->execute();
    $stmt->bind_result($age);
    $stmt->fetch();
    $stmt->close();

    // Update player details, including UID change
    $stmt = $conn->prepare("UPDATE players SET name = ?, dob = ?, age = ?, sex = ?, uid = ? WHERE uid = ?");
    $stmt->bind_param("ssissi", $name, $dob, $age, $sex, $new_uid, $player_uid);
    
    if ($stmt->execute()) {
        // Redirect to register_player.php after successful update
        header("Location: register_player.php?success=1");
        exit;
    } else {
        $message = "Error updating player: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch player details based on UID
$stmt = $conn->prepare("SELECT * FROM players WHERE uid = ?");
$stmt->bind_param("i", $player_uid);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();
$stmt->close();

if (!$player) {
    die("<p class='error'>Player not found.</p>");
}
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
    <div class="container">
        <h1>Edit Player</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
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

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>

<?php ob_end_flush(); ?>
