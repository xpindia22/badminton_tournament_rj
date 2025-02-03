<?php
// Include database connection
require_once 'conn.php';

// Fetch player rankings based on total points scored in matches by category
$query = "
    SELECT 
        players.name AS player_name,
        categories.name AS category_name,
        SUM(match_details.points_scored) AS total_points
    FROM 
        match_details
    JOIN 
        players ON match_details.player_id = players.id
    JOIN 
        categories ON match_details.category_id = categories.id
    GROUP BY 
        players.id, categories.id
    ORDER BY 
        categories.name ASC, total_points DESC;
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Rankings</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Player Rankings</h1>
    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Rank</th><th>Player Name</th><th>Category</th><th>Total Points</th></tr>";

        // Display data in a table with ranking
        $rank = 1;
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $rank . "</td>";
            echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
            echo "</tr>";
            $rank++;
        }

        echo "</table>";
    } else {
        echo "<p>No data available.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
