<?php
session_start();
require 'conn.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Both fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM players WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            $_SESSION['player_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: player_dashboard.php");
            exit;
        } else {
            $message = "Invalid username or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Player Login</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</body>
</html>
