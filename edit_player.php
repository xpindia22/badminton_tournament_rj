<?php
//edit_player.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "xxx";
$dbname = "badminton_tournament";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Player ID is required.");
}

$player_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];

    // Calculate age dynamically
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    $stmt = $conn->prepare("UPDATE players SET name = ?, dob = ?, age = ?, sex = ?, uid = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $dob, $age, $sex, $uid, $player_id);
    if ($stmt->execute()) {
        echo "<p>Player updated successfully! <a href='insert_player.php'>Go back</a></p>";
    } else {
        echo "<p>Error: {$stmt->error}</p>";
    }
    $stmt->close();
    exit;
}

$result = $conn->query("SELECT * FROM players WHERE id = $player_id");
if ($result->num_rows === 0) {
    die("Player not found.");
}
$player = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Player</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        input, select, button { padding: 10px; margin-bottom: 10px; width: 100%; max-width: 300px; }
    </style>
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
    <h1>Edit Player</h1>
    <form method="post">
        <label for="name">Player Name:</label>
        <input type="text" name="name" id="name" value="<?= $player['name'] ?>" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" name="dob" id="dob" value="<?= $player['dob'] ?>" onchange="calculateAge()" required>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" value="<?= $player['age'] ?>" readonly required>

        <label for="sex">Sex:</label>
        <select name="sex" id="sex" required>
            <option value="M" <?= $player['sex'] === 'M' ? 'selected' : '' ?>>Male</option>
            <option value="F" <?= $player['sex'] === 'F' ? 'selected' : '' ?>>Female</option>
        </select>

        <label for="uid">Unique ID:</label>
        <input type="text" name="uid" id="uid" value="<?= $player['uid'] ?>" required>

        <button type="submit">Update Player</button>
    </form>
</body>
</html>
