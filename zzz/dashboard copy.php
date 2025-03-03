<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'auth.php'; // Ensure this path is correct
redirect_if_not_logged_in();

// Include the header after checking login status
include 'header.php';

$username = htmlspecialchars($_SESSION['username']);
$is_admin = is_admin();
$is_user = is_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #007BFF;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 300px;
        }
        .btn-primary {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            color: #fff;
            background: #007BFF;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <!-- <p>Welcome, <?= $username; ?>!</p> -->
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
                <div class="card">
                    <h2>Create Categories</h2>
                    <p>Add categories for your tournaments.</p>
                    <a href="insert_category.php" class="btn-primary">Add Categories</a>
                </div>
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

            <!-- Public Links -->
            <div class="card">
                <h2>View Results</h2>
                <p>Check tournament results and match standings.</p>
                <a href="results.php" class="btn-primary">View Results</a>
            </div>
        </div>
    </div>
</body>
</html>
