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
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            display: flex;
            width: 80%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .left-section {
            flex: 1;
            position: relative;
        }
        .slideshow-container img {
            width: 100%;
            height: auto;
            display: none;
            position: absolute;
            top: 0;
            left: 0;
        }
        .slideshow-container img.active {
            display: block;
        }
        .right-section {
            flex: 1;
            padding: 20px;
            background: #fff;
        }
        .error {
            color: red;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        form label, form input, form button {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
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
    <script>
        let slideIndex = 0;
        const slides = document.querySelectorAll('.slideshow-container img');

        function showSlides() {
            slides.forEach((slide, index) => {
                slide.classList.remove('active');
                if (index === slideIndex) {
                    slide.classList.add('active');
                }
            });
            slideIndex = (slideIndex + 1) % slides.length;
            setTimeout(showSlides, 3000); // Change image every 3 seconds
        }

        if (slides.length > 0) {
            slides[0].classList.add('active'); // Start with the first image
            showSlides();
        }
    </script>
</body>
</html>
