<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine the logged-in user's name or default to 'Guest'
$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
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
        <!-- Welcome Message -->
        <div class="welcome">
            <span>Welcome, <?= htmlspecialchars($logged_in_user) ?></span>
        </div>
        <!-- Navigation Links -->
        <div class="links">
            <a href="dashboard.php">Dashboard</a>
            <!-- <a href="readme.php">Help-Readme</a> -->
            <a href="results.php">Singles Match Results</a>
            <!-- <a href="ranking_singles.php">Ranking Singles</a> -->
            <!-- <a href="add_moderator.php">Add Moderator</a> -->
            <a href="filter_doubles_userbased.php">Filter Doubles UserBased</a>
            <a href="filter_singles_userbased.php">Filter Singles UserBased</a>




            <!-- Dropdown: Singles Matches -->
            <div class="dropdown">
                <a href="#">Singles Matches</a>
                <div class="dropdown-content">
                    <a href="register.php">Register User</a>
                    <a href="insert_tournament.php">Insert Tournaments</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match.php">Insert Match</a>
                    <a href="results_singles.php">Singles Results</a>
                    <a href="edit_results_singles_link.php">Edit Singles Matches</a>
                </div>
            </div>

            <!-- Dropdown: Boys Doubles -->
            <div class="dropdown">
                <a href="#">Boys Doubles</a>
                <div class="dropdown-content">
                    <a href="register.php">Register User</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match_bd.php">Insert Boys Doubles</a>
                    <a href="results_bd.php">Result Boys Doubles</a>
                    <a href="edit_results_bd.php">Edit Boys Doubles</a>
                    <a href="edit_results_doubles.php">Edit All Doubles</a>
                </div>
            </div>

            <!-- Dropdown: Girls Doubles -->
            <div class="dropdown">
                <a href="#">Girls Doubles</a>
                <div class="dropdown-content">
                    <a href="register.php">Register User</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match_gd.php">Insert Girls Doubles</a>
                    <a href="results_gd.php">Result Girls Doubles</a>
                    <a href="edit_results_gd.php">Edit Girls Doubles</a>
                </div>
            </div>

            <!-- Dropdown: Mixed Doubles -->
            <div class="dropdown">
                <a href="#">Mixed Doubles</a>
                <div class="dropdown-content">
                    <a href="register.php">Register User</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match_xd.php">Create Mixed Doubles</a>
                    <a href="results_xd.php">Results Mixed Doubles</a>
                    <a href="edit_results_xd.php">Edit Mixed Doubles</a>
                </div>
            </div>

            <!-- Dropdown: Player Ranking -->
            <div class="dropdown">
                <a href="#">Player Ranking</a>
                <div class="dropdown-content">
                    <a href="ranking_singles.php">Singles Ranking</a>
                    <a href="ranking_doubles.php">Double & Mixed Doubles</a>
                </div>
            </div>

            <!-- Logout -->
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
