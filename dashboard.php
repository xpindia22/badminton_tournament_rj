<?php

// Ensure session is only started once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
redirect_if_not_logged_in();
// Include the header after checking login status
include 'header.php';

// Fetch session information
$username = htmlspecialchars($_SESSION['username'] ?? $_SESSION['player_name'] ?? "Guest");
$is_admin = is_admin();
$is_user = is_user();
$is_player = is_player();
$is_visitor = is_visitor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    </head>
<body>
    <div class="container">
        <!-- <h1>Dashboard</h1> -->
        <!-- <p style="text-align: center;">Welcome, <?= $username; ?>!</p> -->
        <div class="card-container">
            
            <!-- Admin Links -->
            <?php if ($is_admin): ?>
                <div class="card">
                    <h2>Manage Users</h2>
                    <p>View, edit, or delete user accounts.</p>
                    <a href="register.php" class="btn-primary">Manage Users</a>
                </div>
                <div class="card">
                    <h2>Manage All Tournaments</h2>
                    <p>See all tournaments created by all users.</p>
                    <a href="insert_tournament.php" class="btn-primary">View Tournaments</a>
                </div>
                <div class="card">
                    <h2>Manage Categories</h2>
                    <p>Edit or delete categories created by any user.</p>
                    <a href="insert_category.php" class="btn-primary">Manage Categories</a>
                </div>
                <div class="card">
                    <h2>Manage All Matches</h2>
                    <p>View, edit, or delete matches created by all users.</p>
                    <a href="matches.php" class="btn-primary">Manage Matches</a>
                </div>
                <div class="card">
                    <h2>Manage Players</h2>
                    <p>View all players and their details.</p>
                    <a href="insert_player.php" class="btn-primary">Manage Players</a>
                </div>
            <?php endif; ?>

            <!-- User Links -->
            <?php if ($is_user): ?>
                <div class="card">
                    <h2>Create Tournament</h2>
                    <p>Create and manage your tournaments.</p>
                    <a href="insert_tournament.php" class="btn-primary">Create Tournament</a>
                </div>
                <!-- <div class="card">
                    <h2>Create Categories</h2>
                    <p>Add categories for your tournaments.</p>
                    <a href="insert_category.php" class="btn-primary">Add Categories</a>
                </div> -->
                <div class="card">
                    <h2>Enter Matches</h2>
                    <p>Enter match scores for your tournaments.</p>
                    <a href="insert_match.php" class="btn-primary">Enter Matches</a>
                </div>
                <div class="card">
                    <h2>View Your Players</h2>
                    <p>Manage players you added to the system.</p>
                    <a href="insert_player.php" class="btn-primary">View Players</a>
                </div>
                <div class="card">
                    <h2>View Your Data</h2>
                    <p>View and manage tournaments, categories, and matches you created.</p>
                    <a href="user_data.php" class="btn-primary">View Your Data</a>
                </div>
            <?php endif; ?>

            <!-- Player Links -->
            <?php if ($is_player): ?>
                <div class="card">
                    <h2>View Tournament Results</h2>
                    <p>Check results of different tournament categories.</p>
                    <a href="results_bd.php" class="btn-primary">Doubles Results</a>
                    <a href="results_xd.php" class="btn-primary">Mixed Doubles Results</a>
                    <a href="results_singles.php" class="btn-primary">Singles Results</a>
                </div>
                <div class="card">
                    <h2>View Rankings</h2>
                    <p>Check rankings of players in singles and doubles.</p>
                    <a href="ranking_singles.php" class="btn-primary">Singles Rankings</a>
                    <a href="ranking_doubles.php" class="btn-primary">Doubles Rankings</a>
                </div>
                <div class="card">
                    <h2>Player Profile</h2>
                    <p>View your registered details and update them if needed.</p>
                    <a href="player_profile.php" class="btn-primary">View Profile</a>
                </div>
            <?php endif; ?>

            <!-- Public Links -->
            <div class="card">
                <h2>View Results</h2>
                <p>Check tournament results and match standings.</p>
                <a href="results.php" class="btn-primary">View Results</a>
            </div>
        </div>
    </div>
    <!-- <footer id="kopeerightFuter">
  Robert James, Whatsapp no 7432001215, Email xpindia@gmail.com
</footer> -->

</body>
</html>
