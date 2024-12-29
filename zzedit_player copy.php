<?php
require 'auth.php';
redirect_if_not_logged_in();

$player_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

$stmt = $conn->prepare("SELECT created_by FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$stmt->bind_result($created_by);
$stmt->fetch();
$stmt->close();

if ($created_by !== $user_id && !$is_admin) {
    die("Access denied: You can only edit your own players.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];

    $stmt = $conn->prepare("UPDATE players SET name = ?, dob = ?, age = ?, sex = ?, uid = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $dob, $age, $sex, $uid, $player_id);
    $stmt->execute();
    header("Location: insert_player.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Player</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Edit Player</h1>
    <form method="post">
        <label for="name">Player Name:</label>
        <input type="text" name="name" id="name" value="<?= $player['name'] ?>" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" name="dob" id="dob" value="<?= $player['dob'] ?>" required>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" value="<?= $player['age'] ?>" required readonly>

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
