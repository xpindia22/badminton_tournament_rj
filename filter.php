<?php
include "header.php";
require_once 'conn.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all users and moderators
$userQuery = "SELECT id, username AS name, role FROM users WHERE role IN ('user', 'moderator')";
$userResult = $conn->query($userQuery);
if (!$userResult) {
    die("Error fetching users: " . $conn->error);
}

// Initialize variables
$championships = [];
$categories = [];
$matches = [];
$selectedCategoryId = null;
$selectedCategoryName = '';

if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];

    // Fetch championships created by or moderated by the selected user
    $champQuery = "SELECT DISTINCT t.id, t.name 
                   FROM tournaments t
                   LEFT JOIN tournament_moderators tm ON t.id = tm.tournament_id
                   WHERE t.created_by = $userId OR tm.user_id = $userId";
    $champResult = $conn->query($champQuery);
    if ($champResult) {
        while ($row = $champResult->fetch_assoc()) {
            $championships[] = $row;
        }
    } else {
        die("Error fetching championships: " . $conn->error);
    }

    // Fetch categories linked to the championships
    if (!empty($championships)) {
        $champIds = implode(',', array_column($championships, 'id'));

        if (!empty($champIds)) {
            $catQuery = "SELECT c.id, c.name, c.tournament_id 
                         FROM categories c
                         INNER JOIN tournament_categories tc ON c.id = tc.category_id
                         WHERE c.tournament_id IN ($champIds)";
            $catResult = $conn->query($catQuery);
            if ($catResult) {
                while ($row = $catResult->fetch_assoc()) {
                    $categories[] = $row;
                }
            } else {
                die("Error fetching categories: " . $conn->error);
            }
        }
    }
}

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $selectedCategoryId = (int)$_GET['category_id'];

    // Fetch the selected category name
    foreach ($categories as $category) {
        if ($category['id'] == $selectedCategoryId) {
            $selectedCategoryName = $category['name'];
            break;
        }
    }

    // Fetch matches for the selected category
    $matchQuery = "SELECT m.id, m.stage, m.match_date, m.match_time, 
                          p1.name AS player1_name, p2.name AS player2_name, 
                          m.set1_player1_points, m.set1_player2_points, 
                          m.set2_player1_points, m.set2_player2_points, 
                          m.set3_player1_points, m.set3_player2_points,
                          CASE 
                              WHEN m.set1_player1_points + m.set2_player1_points + m.set3_player1_points > 
                                   m.set1_player2_points + m.set2_player2_points + m.set3_player2_points THEN p1.name
                              ELSE p2.name
                          END AS winner
                   FROM matches m
                   LEFT JOIN players p1 ON m.player1_id = p1.id
                   LEFT JOIN players p2 ON m.player2_id = p2.id
                   WHERE m.category_id = $selectedCategoryId";
    $matchResult = $conn->query($matchQuery);
    if ($matchResult) {
        while ($row = $matchResult->fetch_assoc()) {
            $matches[] = $row;
        }
    } else {
        die("Error fetching matches: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Championships</title>
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
        a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>

<h2>Filter Championships</h2>
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

    <label for="category">Select Category:</label>
    <select name="category_id" id="category" onchange="this.form.submit()">
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>" <?= isset($selectedCategoryId) && $selectedCategoryId == $category['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (!empty($matches)): ?>
    <h3>Matches (Category: <?= htmlspecialchars($selectedCategoryName) ?>)</h3>
    <table>
        <thead>
            <tr>
                <th>Match (Category)</th>
                <th>Player 1</th>
                <th>Player 2</th>
                <th>Date</th>
                <th>Time</th>
                <th>Stage</th>
                <th>Set 1 (P1 - P2)</th>
                <th>Set 2 (P1 - P2)</th>
                <th>Set 3 (P1 - P2)</th>
                <th>Winner</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?= htmlspecialchars($selectedCategoryName) ?></td>
                    <td><?= htmlspecialchars($match['player1_name']) ?></td>
                    <td><?= htmlspecialchars($match['player2_name']) ?></td>
                    <td><?= htmlspecialchars($match['match_date']) ?></td>
                    <td><?= htmlspecialchars($match['match_time']) ?></td>
                    <td><?= htmlspecialchars($match['stage']) ?></td>
                    <td><?= $match['set1_player1_points'] ?> - <?= $match['set1_player2_points'] ?></td>
                    <td><?= $match['set2_player1_points'] ?> - <?= $match['set2_player2_points'] ?></td>
                    <td><?= $match['set3_player1_points'] ?> - <?= $match['set3_player2_points'] ?></td>
                    <td><?= htmlspecialchars($match['winner']) ?></td>
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
