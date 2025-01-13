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
    </style>
</head>
<body>

<h2>Filter Championships</h2>
<form method="GET">
    <label for="user">Select User/Moderator:</label>
    <select name="user_id" id="user" onchange="this.form.submit()">
        <option value="">-- Select User/Moderator --</option>
        <?php while ($row = $userResult->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" <?= isset($userId) && $userId == $row['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['name']) ?> (<?= ucfirst(htmlspecialchars($row['role'])) ?>)
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php if (!empty($championships)): ?>
    <h3>Championships</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($championships as $championship): ?>
                <tr>
                    <td><?= $championship['id'] ?></td>
                    <td><?= htmlspecialchars($championship['name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($categories)): ?>
    <h3>Categories</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Championship ID</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['id'] ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= $category['tournament_id'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
