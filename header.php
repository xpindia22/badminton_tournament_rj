<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Assuming the logged-in user's name is stored in the session
$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Badminton Tournament</title>
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f4f4f4;
            padding: 10px 20px;
            border-bottom: 1px solid #ccc;
        }
        .header .links a {
            margin-right: 15px;
            text-decoration: none;
            color: #333;
        }
        .header .links a:hover {
            text-decoration: underline;
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
            <a href="register.php">Register User</a>
            <a href="insert_tournament.php">Insert Tournaments</a>
            <a href="insert_player.php">Insert Player</a>
            <a href="insert_match.php">Insert Match</a>
            <a href="results.php">Results</a>
            <a href="matches.php">Edit Matches</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
