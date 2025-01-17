<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            $_SESSION['user_role'] = 'visitor';

            // Debugging output to check session data
            error_log("Player Login Successful: UID = $player_uid, Name = $player_name");

            // Redirect before sending any HTML output
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid UID or password.";
        }
    }
}

// Include header after session and logic execution
include 'header.php';

/**
 * Function to get images from a directory
 */
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
shuffle($images); // Randomize the image order
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f4f4f4;
        }

        .container {
            display: flex;
            width: 70%;
            margin-top: 80px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        .left-section {
            flex: 1;
            position: relative;
            max-height: 100%;
            overflow: hidden;
        }

        .slideshow-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slideshow-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .slideshow-container img.active {
            opacity: 1;
        }

        .right-section {
            flex: 1;
            padding: 30px;
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
            max-width: 300px;
        }

        .login-btn {
            padding: 10px;
            width: 80%;
            max-width: 300px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease-in-out;
        }

        .login-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="left-section">
            <div class="slideshow-container">
                <?php foreach ($images as $index => $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Slideshow Image" class="<?= $index === 0 ? 'active' : '' ?>">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="right-section">
            <h2>Player Login</h2>
            <?php if (!empty($message)): ?>
                <p class="error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="post">
                <label for="uid">Player UID:</label>
                <input type="number" name="uid" id="uid" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <button type="submit" class="login-btn">Login</button>
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
            slides[0].classList.add('active'); // Ensure the first image is visible
            showSlides();
        }
    </script>

</body>
</html>
