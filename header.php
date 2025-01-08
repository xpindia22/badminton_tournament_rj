<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Assuming the logged-in user's name is stored in the session
$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badminton Tournament</title>
    <style>
        .header {
            display: flex; /* Use flexbox for row alignment */
            justify-content: space-between; /* Space between welcome message and links */
            align-items: center; /* Align items vertically in the center */
            background-color: #f4f4f4;
            padding: 10px 20px; /* Adjust padding for spacing */
            border-bottom: 1px solid #ccc;
        }

        .header .welcome {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .header .links {
            display: flex;
            gap: 15px; /* Spacing between links */
            position: relative;
            align-items: center; /* Align links vertically */
        }

        .header .links a {
            text-decoration: none;
            color: #333;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5; /* Consistent vertical alignment */
            display: inline-block;
            padding: 5px 0;
        }

        .header .links a:hover {
            text-decoration: underline;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none; /* Hidden by default */
            position: absolute; /* Position relative to parent */
            top: 100%; /* Position dropdown below the main link */
            left: 0;
            background-color: #f9f9f9;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            min-width: 220px; /* Explicitly set width to ensure space for long text */
            border-radius: 4px; /* Rounded corners */
            overflow: hidden;
        }

        .dropdown-content a {
            color: #333;
            text-decoration: none;
            display: block; /* Ensures each link occupies one row */
            width: 100%; /* Ensure links take full width of the dropdown */
            padding: 10px 16px; /* Increased padding for better spacing */
            white-space: nowrap; /* Prevents text wrapping */
            border-bottom: 1px solid #ddd; /* Divider between links */
            box-sizing: border-box; /* Include padding in width calculations */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #007bff; /* Highlight with primary color */
            color: #fff; /* White text on hover */
        }

        .dropdown-content a:last-child {
            border-bottom: none; /* Remove divider for the last link */
        }

        .dropdown:hover .dropdown-content {
            display: block; /* Show dropdown on hover */
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
