<?php
// login.php

require 'auth.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $role);
    $stmt->fetch();
    $stmt->close();

    if ($user_id && verify_password($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

function get_images_from_directory($directory) {
    $images = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    $dir_iterator = new RecursiveDirectoryIterator($directory);
    $iterator = new RecursiveIteratorIterator($dir_iterator);

    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), $allowed_extensions)) {
            $images[] = $file->getPathname();
        }
    }

    return $images;
}

$images = get_images_from_directory('images');
shuffle($images); // Randomize the image order
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Include your existing CSS styles here */
    </style>
</head>
<body>
    <div class="header">
        <?php include 'header.php'; ?>
    </div>

    <div class="container">
        <div class="left-section">
            <div class="slideshow-container">
                <?php foreach ($images as $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Slideshow Image">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="right-section">
            <h1>Welcome to Badminton Tournament Login</h1>
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
