<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure session is only started once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
require_once 'conn.php';
require_once 'auth.php'; // Ensure authentication functions are available

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = trim($_POST['uid'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($uid) || empty($password)) {
        $message = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT uid, name, password FROM players WHERE uid = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->bind_result($player_uid, $player_name, $hashed_password);
        $stmt->fetch();
        $stmt->close();

        if ($player_uid && password_verify($password, $hashed_password)) {
            // Set session variables for the player
            $_SESSION['player_uid'] = $player_uid;
            $_SESSION['player_name'] = $player_name;
            $_SESSION['user_role'] = 'visitor'; // Assign visitor role for read-only access

            // Debugging output to check session data
            error_log("Player Login Successful: UID = $player_uid, Name = $player_name");

            // Redirect to dashboard.php
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid UID or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Player Login</h1>
        <?php if ($message): ?>
            <p class="error"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="uid">Player UID:</label>
            <input type="number" name="uid" id="uid" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
