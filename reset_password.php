<?php
session_start();
require 'conn.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['token'])) {
    $token = $_GET['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $new_password, $token);
    if ($stmt->execute()) {
        $message = "Password updated successfully!";
    } else {
        $message = "Invalid token or error updating password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
    <h2>Reset Password</h2>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="post">
        <input type="password" name="password" placeholder="New Password" required>
        <button type="submit">Change Password</button>
    </form>
</body>
</html>
