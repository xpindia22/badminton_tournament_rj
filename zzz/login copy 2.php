<?php
<<<<<<< HEAD
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

=======
// login.php
include 'header.php';
////require_once 'permissions.php';

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


// Get all image files from the images directory and its subdirectories
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
function get_images_from_directory($directory) {
    $images = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

<<<<<<< HEAD
    if (!is_dir($directory)) {
        return $images;
    }

    $dir_iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
=======
    // Recursive directory iterator
    $dir_iterator = new RecursiveDirectoryIterator($directory);
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
    $iterator = new RecursiveIteratorIterator($dir_iterator);

    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), $allowed_extensions)) {
            $images[] = $file->getPathname();
        }
    }

    return $images;
}

<<<<<<< HEAD
$images = get_images_from_directory('images');
shuffle($images);
=======
// Fetch and shuffle images
$images = get_images_from_directory('images');
shuffle($images); // Randomize the image order
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
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
<<<<<<< HEAD
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
=======
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }

        .header {
            width: 100%;
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 1200px;
            display: flex;
            overflow: hidden;
            margin-top: 20px;
        }

        .left-section, .right-section {
            width: 50%;
        }

        .left-section {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #007bff;
            padding: 20px;
            position: relative;
        }

        .slideshow-container {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
            position: relative;
        }

        .slideshow-container img {
            width: 100%;
<<<<<<< HEAD
            height: 80%;
            display: none;
            position: absolute;
            top: 0;
            left: 0;
=======
            display: none;
            border-radius: 8px;
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
        }

        .slideshow-container img.active {
            display: block;
        }

<<<<<<< HEAD
        .right-section {
            flex: 1;
            padding: 10px;
            background: #fff;
            text-align: center;
=======
        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
        }

        .arrow.left {
            left: 10px;
        }

        .arrow.right {
            right: 10px;
        }

        .right-section {
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #007bff;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        p {
            color: #555;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #0056b3;
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
        }

        .error {
            color: red;
<<<<<<< HEAD
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
=======
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.8rem;
            color: #666;
        }

        .footer p {
            margin: 5px 0;
        }

        b {
            color: #333;
        }
    </style>
    <script>
        let currentIndex;

        function showSlides(index) {
            const slides = document.querySelectorAll(".slideshow-container img");
            slides.forEach(slide => slide.classList.remove("active"));
            slides[index].classList.add("active");
        }

        function nextSlide() {
            const slides = document.querySelectorAll(".slideshow-container img");
            currentIndex = (currentIndex + 1) % slides.length;
            showSlides(currentIndex);
        }

        function prevSlide() {
            const slides = document.querySelectorAll(".slideshow-container img");
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            showSlides(currentIndex);
        }

        document.addEventListener("DOMContentLoaded", () => {
            const slides = document.querySelectorAll(".slideshow-container img");
            currentIndex = Math.floor(Math.random() * slides.length); // Start with a random image
            showSlides(currentIndex);
            setInterval(nextSlide, 3000); // Change image every 3 seconds
        });
    </script>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <?php include 'header.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Left Section with Slideshow -->
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
        <div class="left-section">
            <div class="slideshow-container">
                <?php foreach ($images as $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Slideshow Image">
                <?php endforeach; ?>
<<<<<<< HEAD
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

=======
                <button class="arrow left" onclick="prevSlide()">&#10094;</button>
                <button class="arrow right" onclick="nextSlide()">&#10095;</button>
            </div>
        </div>

        <!-- Right Section with Login Form -->
        <div class="right-section">
            <h1>Welcome to Badminton Tournament Login</h1>
            <p><em>Beta release</em></p>
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <div class="footer">
                <p>Please click on the links above to start. You can browse as a Guest also.</p>
                <p>As a Visitor, you can view the matches and results only. For increased functionality, please register for free.</p>
                <p>Email or WhatsApp us for any queries. Kindly report any errors or bugs to us at:</p>
                <p>You are free to create tournaments, categories, add new players, and edit matches. Suggest a feature if you want something new!</p>
                <p><b>Dr. Robert James</b>      -----  <b>WhatsApp:</b> 91-7432001215 -----       <b>Email:</b> xpindia@gmail.com</p>
            </div>
        </div>
    </div>
>>>>>>> 2cd3f3bae59c517a1d5312740121d9ddb41deb0c
</body>
</html>
