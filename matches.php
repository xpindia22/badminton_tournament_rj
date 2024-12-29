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
           p1.name AS player1_name, p2.name AS player2_name, m.stage,
           m.set1_player1_points, m.set1_player2_points,
           m.set2_player1_points, m.set2_player2_points,
           m.set3_player1_points, m.set3_player2_points
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
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 95%;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        th.set-col, td.set-col {
            width: 120px; /* Widen Set columns */
        }
        .top-bar {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: right;
        }
        .top-bar span {
            float: left;
            font-size: 18px;
            margin-left: 10px;
        }
        .logout-link {
            color: white;
            text-decoration: none;
            font-size: 16px;
            margin-right: 10px;
        }
    </style>
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
                        <th class="set-col">Set 1</th>
                        <th class="set-col">Set 2</th>
                        <th class="set-col">Set 3</th>
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
                            <td class="set-col"><?= htmlspecialchars($row['set1_player1_points']) ?> - <?= htmlspecialchars($row['set1_player2_points']) ?></td>
                            <td class="set-col"><?= htmlspecialchars($row['set2_player1_points']) ?> - <?= htmlspecialchars($row['set2_player2_points']) ?></td>
                            <td class="set-col"><?= htmlspecialchars($row['set3_player1_points']) ?> - <?= htmlspecialchars($row['set3_player2_points']) ?></td>
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
