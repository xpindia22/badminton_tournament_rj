<?php
ob_start(); // Start output buffering to prevent "headers already sent" errors

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine the logged-in user's name or default to 'Guest'
$logged_in_user = 'Guest';

if (isset($_SESSION['username'])) {
    $logged_in_user = $_SESSION['username']; // For admins and users
} elseif (isset($_SESSION['player_name'])) {
    $logged_in_user = $_SESSION['player_name']; // For players
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badminton Tournament</title>
    <style>
        /* Reset margins and padding for the body */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        /* Header styling */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f4f4f4;
            padding: 10px 20px;
            border-bottom: 1px solid #ccc;
        }

        /* Welcome message */
        .header .welcome {
            font-size: 14px;
            color: #333;
        }

        /* Links container */
        .header .links {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Individual links */
        .header .links a {
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        /* Dropdown styling */
        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #f9f9f9;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            min-width: 220px;
            border-radius: 4px;
        }

        .dropdown-content a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 10px 16px;
            border-bottom: 1px solid #ddd;
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="welcome">
            <span>Welcome, <?= htmlspecialchars($logged_in_user) ?></span>
        </div>
        <div class="links">
            <a href="dashboard.php">Dashboard</a>
            <a href="register.php">Register Tournament Manager</a>
            <a href="register_player.php">Register Player</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
<?php ob_end_flush(); // Flush output buffer ?>
