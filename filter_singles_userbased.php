<?php
require_once 'conn.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "header.php";

// Fetch users and moderators
$userQuery = "SELECT id, username AS name, role FROM users WHERE role IN ('user', 'moderator')";
$userResult = $conn->query($userQuery);
if (!$userResult) {
    die("Error fetching users: " . $conn->error);
}

// Initialize arrays
$championships = [];
$categories = [];
$matches = [];

if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
    $userId = (int)$_GET['user_id'];

    // Fetch tournaments for which the user is a moderator or has access
    $champQuery = "
        SELECT t.id, t.name
        FROM tournaments t
        LEFT JOIN tournament_moderators tm ON t.id = tm.tournament_id
        WHERE tm.user_id = $userId";
    $champResult = $conn->query($champQuery);

    if ($champResult) {
        while ($row = $champResult->fetch_assoc()) {
            $championships[] = $row;
        }
    } else {
        die("Error fetching championships: " . $conn->error);
    }

    // Fetch categories and matches if a championship is selected
    if (isset($_GET['championship_id']) && $_GET['championship_id'] !== '') {
        $championshipId = (int)$_GET['championship_id'];

        // Fetch categories
        $catQuery = "SELECT id, name FROM categories WHERE tournament_id = $championshipId";
        $catResult = $conn->query($catQuery);
        if ($catResult) {
            while ($row = $catResult->fetch_assoc()) {
                $categories[] = $row;
            }
        } else {
            die("Error fetching categories: " . $conn->error);
        }

        // Fetch matches (only for BS and GS categories)
        $matchQuery = "
            SELECT m.*, c.name AS category_name 
            FROM matches m
            JOIN categories c ON m.category_id = c.id
            WHERE m.tournament_id = $championshipId
            AND (c.name LIKE '%BS%' OR c.name LIKE '%GS%')";
        $matchResult = $conn->query($matchQuery);
        if ($matchResult) {
            while ($row = $matchResult->fetch_assoc()) {
                $matches[] = $row;
            }
        } else {
            die("Error fetching matches: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Singles Championships</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        select {
            padding: 5px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h2>Filter Singles Matches</h2>
<form method="GET">
    <label for="user">Select User/Moderator:</label>
    <select name="user_id" id="user" onchange="this.form.submit()">
        <option value="">-- Select User/Moderator --</option>
        <?php while ($row = $userResult->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" <?= isset($_GET['user_id']) && $_GET['user_id'] == $row['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['name']) ?> (<?= ucfirst(htmlspecialchars($row['role'])) ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <?php if (!empty($championships)): ?>
        <label for="championship">Select Championship:</label>
        <select name="championship_id" id="championship" onchange="this.form.submit()">
            <option value="">-- Select Championship --</option>
            <?php foreach ($championships as $champ): ?>
                <option value="<?= $champ['id'] ?>" <?= isset($_GET['championship_id']) && $_GET['championship_id'] == $champ['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($champ['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</form>

<?php if (!empty($matches)): ?>
    <h3>Singles Matches</h3>
    <table>
        <thead>
            <tr>
                <th>Match ID</th>
                <th>Match (Category)</th>
                <th>Player 1</th>
                <th>Player 2</th>
                <th>Stage</th>
                <th>Date</th>
                <th>Time</th>
                <th>Set 1</th>
                <th>Set 2</th>
                <th>Set 3</th>
                <th>Winner</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matches as $match): ?>
                <?php
                $player1Name = 'Unknown';
                if (!empty($match['player1_id'])) {
                    $player1Id = (int)$match['player1_id'];
                    $player1Query = "SELECT name FROM players WHERE id = $player1Id";
                    $player1Result = $conn->query($player1Query);
                    if ($player1Result && $player1Result->num_rows > 0) {
                        $player1Name = $player1Result->fetch_assoc()['name'];
                    }
                }

                $player2Name = 'Unknown';
                if (!empty($match['player2_id'])) {
                    $player2Id = (int)$match['player2_id'];
                    $player2Query = "SELECT name FROM players WHERE id = $player2Id";
                    $player2Result = $conn->query($player2Query);
                    if ($player2Result && $player2Result->num_rows > 0) {
                        $player2Name = $player2Result->fetch_assoc()['name'];
                    }
                }

                $player1Total = $match['set1_player1_points'] + $match['set2_player1_points'] + $match['set3_player1_points'];
                $player2Total = $match['set1_player2_points'] + $match['set2_player2_points'] + $match['set3_player2_points'];
                $winner = $player1Total > $player2Total ? $player1Name : ($player2Total > $player1Total ? $player2Name : 'Draw');

                $formattedTime = '';
                if (!empty($match['match_time'])) {
                    $formattedTime = date('h:i A', strtotime($match['match_time']));
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($match['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($match['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($player1Name ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($player2Name ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($match['stage'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($match['match_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($formattedTime ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $match['set1_player1_points'] ?> - <?= $match['set1_player2_points'] ?></td>
                    <td><?= $match['set2_player1_points'] ?> - <?= $match['set2_player2_points'] ?></td>
                    <td><?= $match['set3_player1_points'] ?> - <?= $match['set3_player2_points'] ?></td>
                    <td><?= htmlspecialchars($winner ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="edit_match.php?id=<?= $match['id'] ?>">Edit</a> |
                        <a href="delete_match.php?id=<?= $match['id'] ?>" onclick="return confirm('Are you sure you want to delete this match?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
