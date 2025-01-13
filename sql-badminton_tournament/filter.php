<?php
require_once 'conn.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch users and moderators
$userQuery = "SELECT id, name, role FROM users WHERE role IN ('user', 'moderator')";
$userResult = $conn->query($userQuery);
if (!$userResult) {
    die("Error fetching users: " . $conn->error);
}

// Fetch championships
$champQuery = "SELECT id, name FROM championships";
$champResult = $conn->query($champQuery);
if (!$champResult) {
    die("Error fetching championships: " . $conn->error);
}

// Fetch categories and matches dynamically if a championship is selected
$categories = [];
$matches = [];
if (isset($_GET['championship_id'])) {
    $championshipId = (int)$_GET['championship_id'];

    // Fetch categories
    $catQuery = "SELECT id, name FROM categories WHERE championship_id = $championshipId";
    $catResult = $conn->query($catQuery);
    if ($catResult) {
        while ($row = $catResult->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        die("Error fetching categories: " . $conn->error);
    }

    // Fetch matches
    $matchQuery = "SELECT id, name FROM matches WHERE championship_id = $championshipId";
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
    </style>
</head>
<body>

<h2>Filter Championships</h2>
<form method="GET">
    <label for="user">Select User/Moderator:</label>
    <select name="user_id" id="user">
        <option value="">-- Select User/Moderator --</option>
        <?php while ($row = $userResult->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> (<?= ucfirst(htmlspecialchars($row['role'])) ?>)</option>
        <?php endwhile; ?>
    </select>

    <label for="championship">Select Championship:</label>
    <select name="championship_id" id="championship" onchange="this.form.submit()">
        <option value="">-- Select Championship --</option>
        <?php while ($row = $champResult->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" <?= isset($championshipId) && $championshipId == $row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
    </select>
</form>

<?php if (!empty($categories)): ?>
    <h3>Categories</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['id'] ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($matches)): ?>
    <h3>Matches</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?= $match['id'] ?></td>
                    <td><?= htmlspecialchars($match['name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
