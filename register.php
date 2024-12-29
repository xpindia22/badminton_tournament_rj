<?php
require 'auth.php';
redirect_if_not_logged_in();

// Allow access only for admins
if (!is_admin()) {
    die("<p class='error'>Access denied: Only admins can register users.</p>");
}

if (is_logged_in()) {
    $username = $_SESSION['username']; // Assuming username is stored in the session
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $password = $_POST['password'];
    $hashed_password = hash_password($password);
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $role = $_POST['role'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, email, mobile, role, notes) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("<p class='error'>Database error: " . $conn->error . "</p>");
    }
    $stmt->bind_param("ssssss", $new_username, $hashed_password, $email, $mobile, $role, $notes);
    if ($stmt->execute()) {
        echo "<p class='success'>User registered successfully!</p>";
    } else {
        echo "<p class='error'>Error registering user: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <link rel="stylesheet" href="styles.css">
    <script src="session.js"></script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Register User</h1>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" placeholder="Enter username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Enter email address">

            <label for="mobile">Mobile Number:</label>
            <input type="text" name="mobile" id="mobile" placeholder="Enter mobile number">

            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="user">User</option>
                <option value="visitor">Visitor</option>
                <?php if (is_admin()): ?>
                    <option value="admin">Admin</option>
                <?php endif; ?>
            </select>

            <label for="notes">Notes:</label>
            <textarea name="notes" id="notes" placeholder="Enter additional notes"></textarea>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
