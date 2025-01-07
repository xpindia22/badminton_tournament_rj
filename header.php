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
        .header .links {
            display: flex;
            gap: 15px;
            position: relative;
        }
        .header .links a {
            text-decoration: none;
            color: #333;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .header .links a:hover {
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
            min-width: 200px; /* Ensures dropdown content is consistent */
        }
        .dropdown-content a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 8px 16px;
            white-space: nowrap; /* Prevents text from wrapping to new lines */
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        @media screen and (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header .links {
                flex-direction: column;
                gap: 10px;
            }
            .dropdown-content {
                position: relative; /* Adjust position for smaller screens */
                box-shadow: none;
            }
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
            <a href="readme.php">Help-Readme</a>
            <a href="results.php">Singles Match Results</a>
            <a href="ranking_singles.php">Ranking Singles</a>
 
            <div class="dropdown">
                <a href="#">Singles Matches</a>
                <div class="dropdown-content">
                    <a href="register.php">Register User</a>
                    <a href="insert_tournament.php">Insert Tournaments</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match.php">Insert Match</a>
                    <a href="results.php">Results</a>
                    <a href="matches.php">Edit Matches</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="#">Boys Doubles</a>
                <div class="dropdown-content">
                    <a href="register.php">Register User</a>
                    <a href="insert_player.php">Insert Player</a>
                    <a href="insert_match_bd.php">Insert Boys Doubles</a>
                    <a href="results_bd.php">Result Boys Doubles</a>
                    <a href="edit_results_bd.php">Edit Boys Doubles</a>
                    <a href="edit_results_doubles.php">Edit ALL Doubles</a>
                </div>
            </div>

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
            <div class="dropdown">
                <a href="#">Player Ranking</a>
                <div class="dropdown-content">
                <a href="ranking_singles.php">Singles Ranking</a>
                <a href="ranking_doubles.php">Double & Mixed Doubles</a>

                </div>
            </div>

            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
