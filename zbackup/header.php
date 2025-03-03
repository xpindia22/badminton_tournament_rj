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
        
        .welcome {
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
            margin-right: 50px; /* Adds space between "Welcome, Guest" and the links */
        }

        .links {
            display: flex;
            gap: 15px;
            position: relative;
            flex-grow: 1; /* Pushes links to the right */
        }

        .links a {
            text-decoration: none;
            color: #333;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            min-width: 160px;
        }

        .dropdown-content a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 8px 16px;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
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
            <a href="register.php">Register User</a>

            <div class="dropdown">
                <a href="#">Singles Matches</a>
                <div class="dropdown-content">
                    <a href="insert_tournament.php">Insert Tournaments</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match.php">Insert Match</a>
                    <a href="results.php">Results</a>
                    <a href="matches.php">Edit Matches</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="#">Doubles Matches</a>
                <div class="dropdown-content">
                    <a href="insert_match_doubles.php">Insert Doubles Match</a>
                    <a href="results_doubles.php">Results Doubles Matches</a>
                    <a href="edit_results_doubles.php">Edit Doubles Matches</a>
                </div>
            </div>

            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
