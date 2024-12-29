<?php
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

if (is_logged_in()) {
    $username = $_SESSION['username'];
}

// Fetch matches
$query = "
    SELECT m.id, t.name AS tournament_name, c.name AS category_name, 
           p1.name AS player1_name, p2.name AS player2_name, m.stage
    FROM matches m
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.player1_id = p1.id
    LEFT JOIN players p2 ON m.player2_id = p2.id
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches</title>
    <link rel="stylesheet" href="styles.css">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['tournament_name']) ?></td>
                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                            <td><?= htmlspecialchars($row['player1_name']) ?></td>
                            <td><?= htmlspecialchars($row['player2_name']) ?></td>
                            <td><?= htmlspecialchars($row['stage']) ?></td>
                            <td>
                                <a href="edit_match.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                <a href="delete_match.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this match?')">Delete</a>
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
