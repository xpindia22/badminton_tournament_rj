<?php
// Include database connection
//require_once 'permissions.php';

require_once 'conn.php';

// Fetch singles rankings based on total points scored by players across all matches
$query = "
    SELECT 
        players.name AS player_name,
        players.uid AS player_uid,
        players.sex AS player_sex,
        players.age AS player_age,
        SUM(
            CASE WHEN matches.player1_id = players.id THEN 
                (matches.set1_player1_points + matches.set2_player1_points + matches.set3_player1_points)
            WHEN matches.player2_id = players.id THEN
                (matches.set1_player2_points + matches.set2_player2_points + matches.set3_player2_points)
            ELSE 0 END
        ) AS total_points
    FROM 
        players
    JOIN 
        matches ON matches.player1_id = players.id OR matches.player2_id = players.id
    JOIN 
        categories ON matches.category_id = categories.id
    WHERE 
        categories.type = 'singles'
    GROUP BY 
        players.id
    ORDER BY 
        total_points DESC;
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Singles Rankings</title>
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
    <h1>Overall Singles Rankings</h1>
    <p>Date: <?php echo date('Y-m-d'); ?></p>
    <p>Time: <?php echo date('H:i:s'); ?></p>
    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Player Name</th><th>UID</th><th>Sex</th><th>Age</th><th>Total Points</th></tr>";

        // Display data in a table
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['player_uid']) . "</td>";
            echo "<td>" . htmlspecialchars($row['player_sex']) . "</td>";
            echo "<td>" . htmlspecialchars($row['player_age']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No data available.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
