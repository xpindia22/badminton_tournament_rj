<?php
session_start();
require 'conn.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check if email exists in `users` table
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $token = bin2hex(random_bytes(50));
            $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();

            mail($email, "Password Reset", "Click the link to reset your password: http://yourwebsite.com/reset_password.php?token=$token");
            $message = "Reset link sent to your email.";
        } else {
            $message = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Forgot Password</title></head>
<body>
    <h2>Forgot Password</h2>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
