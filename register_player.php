<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include 'header.php';
require_once 'conn.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$name = '';
$dob = '';
$sex = '';
$password = '';
$uid = '';

// Fetch next available UID
$next_uid = 1;
$result = $conn->query("SELECT MAX(uid) + 1 AS next_uid FROM players");
if ($result) {
    $row = $result->fetch_assoc();
    $next_uid = $row['next_uid'] ?? 1; // Default to 1 if table is empty
    $result->close();
}

// Display success message after redirection
if (isset($_GET['success'])) {
    $message = "Player registration successful!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = trim($_POST['uid'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $sex = trim($_POST['sex'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Input Validation
    if (empty($uid) || empty($name) || empty($dob) || empty($password)) {
        $message = "All fields are required.";
    } elseif (!is_numeric($uid) || $uid < 1) {
        $message = "Invalid UID.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $name)) {
        $message = "Name can only contain letters and spaces.";
    } elseif (!in_array($sex, ['M', 'F'])) {
        $message = "Invalid gender selection.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        // Check if UID already exists
        $check_stmt = $conn->prepare("SELECT uid FROM players WHERE uid = ?");
        $check_stmt->bind_param("i", $uid);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Error: UID already exists. Please choose another.";
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

            // Insert player into database with timestamp
            $stmt = $conn->prepare("INSERT INTO players (uid, name, dob, age, sex, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ississ", $uid, $name, $dob, $age, $sex, $hashed_password);

            if ($stmt->execute()) {
                // Prevent form resubmission by redirecting after success
                header("Location: register_player.php?success=1");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }

        $check_stmt->close();
    }
}

// Fetch all registered players sorted by most recent entry
$players = [];
$result = $conn->query("SELECT uid, name, dob, age, sex, created_at FROM players ORDER BY id DESC");
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
                <label for="uid">UID (Editable Auto-Increment):</label>
                <input type="number" name="uid" id="uid" value="<?= htmlspecialchars($next_uid) ?>" required>
            </div>
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
                    <th>Registered At</th>
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
                        <td><?= date("d-m-Y h:i A", strtotime($player['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
