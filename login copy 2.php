<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth.php';
require_once 'conn.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

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
}

function get_images_from_directory($directory) {
    $images = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!is_dir($directory)) {
        return $images;
    }

    $dir_iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dir_iterator);

    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), $allowed_extensions)) {
            $images[] = $file->getPathname();
        }
    }

    return $images;
}

$images = get_images_from_directory('images');
shuffle($images);
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
            margin: 0;
            padding: 0;
            height: 100vh;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            display: flex;
            width: 90%;
            margin-top: 80px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        .left-section {
            flex: 1;
            position: relative;
        }

        .slideshow-container img {
            width: 100%;
            height: 80%;
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
            padding: 10px;
            background: #fff;
            text-align: center;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        form label, form input, form button {
            margin-bottom: 15px;
            width: 80%;
            max-width: 200px;
        }

        .player-login-btn, .register-btn, button {
            padding: 10px;
            width: 80%;
            max-width: 200px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease-in-out;
        }

        .player-login-btn:hover, .register-btn:hover, button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <div class="left-section">
            <div class="slideshow-container">
                <?php foreach ($images as $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Slideshow Image">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="right-section">
            <h2>Login</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <button type="submit">Login</button>
            </form>

            <form action="login_player.php" method="get">
                <button type="submit" class="player-login-btn">Player Login</button>
            </form>

            <form action="register.php" method="get">
                <button type="submit" class="register-btn">Register Tournament Manager</button>
            </form>

            <form action="register_player.php" method="get">
                <button type="submit" class="register-btn">Register Player</button>
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
            setTimeout(showSlides, 3000);
        }

        if (slides.length > 0) {
            slides[0].classList.add('active');
            showSlides();
        }
    </script>

</body>
</html>
