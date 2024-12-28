<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "xxx";
$dbname = "badminton_tournament";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch details of players who participated in matches
$sql = "
    SELECT 
        m.id AS match_id,
        t.name AS tournament_name,
        c.name AS category_name,
        p1.name AS player1_name,
        p2.name AS player2_name,
        m.stage
    FROM matches m
    INNER JOIN tournaments t ON m.tournament_id = t.id
    INNER JOIN categories c ON m.category_id = c.id
    INNER JOIN players p1 ON m.player1_id = p1.id
    INNER JOIN players p2 ON m.player2_id = p2.id
    WHERE m.stage IS NOT NULL
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Tournament Results</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Match ID</th>
                <th>Tournament</th>
                <th>Category</th>
                <th>Player 1</th>
                <th>Player 2</th>
                <th>Stage</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['match_id'] ?></td>
                    <td><?= $row['tournament_name'] ?></td>
                    <td><?= $row['category_name'] ?></td>
                    <td><?= $row['player1_name'] ?></td>
                    <td><?= $row['player2_name'] ?></td>
                    <td><?= $row['stage'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No matches found for participated players.</p>
    <?php endif; ?>
    <?php $conn->close(); ?>
</body>
</html>
