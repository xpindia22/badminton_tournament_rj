<?php
// matches.php
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (is_logged_in()) {
    $username = $_SESSION['username'];
}

// Fetch matches
$query = "
    SELECT m.id, t.name AS tournament_name, c.name AS category_name, 
           p1.name AS player1_name, p2.name AS player2_name, m.stage,
           m.set1_player1_points, m.set1_player2_points,
           m.set2_player1_points, m.set2_player2_points,
           m.set3_player1_points, m.set3_player2_points,
           m.match_date, m.match_time
    FROM matches m
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.player1_id = p1.id
    LEFT JOIN players p2 ON m.player2_id = p2.id
";

$result = $conn->query($query);

if (!$result) {
    die("<p class='error'>Error fetching matches: " . $conn->error . "</p>");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches</title>
    <link rel="stylesheet" href="style-table.css">
</head>

<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
    <div class="container">
        <h1>Matches</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tournament</th>
                        <th>Category</th>
                        <th>Player 1</th>
                        <th>Player 2</th>
                        <th>Stage</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Set 1</th>
                        <th>Set 2</th>
                        <th>Set 3</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['tournament_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['player1_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['player2_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['stage'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars(date('d-m-Y', strtotime($row['match_date'] ?? ''))) ?></td>
                            <td><?= htmlspecialchars($row['match_time'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['set1_player1_points'] ?? '0') ?> - <?= htmlspecialchars($row['set1_player2_points'] ?? '0') ?></td>
                            <td><?= htmlspecialchars($row['set2_player1_points'] ?? '0') ?> - <?= htmlspecialchars($row['set2_player2_points'] ?? '0') ?></td>
                            <td><?= htmlspecialchars($row['set3_player1_points'] ?? '0') ?> - <?= htmlspecialchars($row['set3_player2_points'] ?? '0') ?></td>
                            <td>
                                <a href="edit_match.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-edit">Edit</a>
                                <a href="delete_match.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this match?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No matches found.</p>
        <?php endif; ?>
    </div>
</body>

</html>
