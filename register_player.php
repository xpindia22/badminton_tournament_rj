<?php
// register_player.php

include 'header.php';
require_once 'conn.php';
session_start(); // Start session for login

$message = '';
$name = '';
$dob = '';
$sex = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $sex = trim($_POST['sex'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Input Validation
    if (empty($name) || empty($dob) || empty($password)) {
        $message = "All fields are required.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $name)) {
        $message = "Name can only contain letters and spaces.";
    } elseif (!in_array($sex, ['M', 'F'])) {
        $message = "Invalid gender selection.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Calculate age
        $stmt = $conn->prepare("SELECT TIMESTAMPDIFF(YEAR, ?, CURDATE()) AS age");
        $stmt->bind_param("s", $dob);
        $stmt->execute();
        $stmt->bind_result($age);
        $stmt->fetch();
        $stmt->close();

        // Insert player into database
        $stmt = $conn->prepare("INSERT INTO players (name, dob, age, sex, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $name, $dob, $age, $sex, $hashed_password);

        if ($stmt->execute()) {
            $message = "Player registration successful!";
            $name = $dob = $sex = $password = '';
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all players
$players = [];
$result = $conn->query("SELECT uid, name, dob, age, sex FROM players ORDER BY uid DESC");
if ($result) {
    $players = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Player Registration</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($dob) ?>" required>
            </div>
            <div class="form-group">
                <label for="sex">Gender:</label>
                <select name="sex" id="sex" required>
                    <option value="M" <?= $sex === 'M' ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= $sex === 'F' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn-primary">Register</button>
        </form>

        <h2>Registered Players</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>UID</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
                    <th>Gender</th>
                </tr>
            </thead>
            <tbody>
                <?php $id = 1; foreach ($players as $player): ?>
                    <tr>
                        <td><?= $id++ ?></td>
                        <td><?= htmlspecialchars($player['uid']) ?></td>
                        <td><?= htmlspecialchars($player['name']) ?></td>
                        <td><?= htmlspecialchars($player['dob']) ?></td>
                        <td><?= htmlspecialchars($player['age']) ?></td>
                        <td><?= htmlspecialchars($player['sex']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
