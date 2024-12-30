<?php
//dashboard.php
require 'auth.php';
redirect_if_not_logged_in();

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
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= $username ?>!</span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Dashboard</h1>
        <div class="card-container">

            <!-- Admin Links -->
            <?php if ($is_admin): ?>
            <div class="card">
                <h2>Manage Users</h2>
                <p>View, edit, or delete user accounts.</p>
                <a href="manage_users.php" class="btn-primary">Manage Users</a>
            </div>
            <div class="card">
                <h2>View All Tournaments</h2>
                <p>See all tournaments created by all users.</p>
                <a href="tournaments.php" class="btn-primary">View Tournaments</a>
            </div>
            <div class="card">
                <h2>Manage Categories</h2>
                <p>Edit or delete categories created by any user.</p>
                <a href="insert_category.php" class="btn-primary">Manage Categories</a>
            </div>
            <div class="card">
                <h2>All Matches</h2>
                <p>View, edit, or delete matches created by all users.</p>
                <a href="matches.php" class="btn-primary">Manage Matches</a>
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
