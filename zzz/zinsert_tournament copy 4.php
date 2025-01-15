<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'header.php';
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];

function fetchTournaments($conn) {
    $query = "
        SELECT 
            t.id AS tournament_id, 
            t.name AS tournament_name, 
            u.username AS owner_name, 
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS categories,
            GROUP_CONCAT(DISTINCT m.username ORDER BY m.username SEPARATOR ', ') AS moderators
        FROM tournaments t
        LEFT JOIN users u ON t.created_by = u.id
        LEFT JOIN tournament_categories tc ON t.id = tc.tournament_id
        LEFT JOIN categories c ON tc.category_id = c.id
        LEFT JOIN tournament_moderators tm ON t.id = tm.tournament_id
        LEFT JOIN users m ON tm.user_id = m.id
        GROUP BY t.id
        ORDER BY t.name;
    ";
    $result = $conn->query($query);
    if (!$result) {
        die("Error fetching tournaments: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tournament'])) {
    $tournament_name = trim($_POST['tournament_name']);
    $created_by = $currentUserId;

    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    $tournament_name = $conn->real_escape_string($tournament_name);
    $sql = "INSERT INTO tournaments (name, created_by) VALUES ('$tournament_name', $created_by)";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>Tournament added successfully.</p>";
    } else {
        die("Error adding tournament: " . $conn->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tournament'])) {
    $tournament_id = intval($_POST['tournament_id']);
    $tournament_name = trim($_POST['tournament_name']);

    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    $stmt = $conn->prepare("UPDATE tournaments SET name = ? WHERE id = ?");
    $stmt->bind_param('si', $tournament_name, $tournament_id);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Tournament updated successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error updating tournament: " . htmlspecialchars($stmt->error) . "</p>";
    }
    $stmt->close();
}

if (isset($_GET['delete_tournament'])) {
    $tournament_id = intval($_GET['delete_tournament']);

    if ($currentUserRole === 'admin') {
        $stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ?");
        $stmt->bind_param('i', $tournament_id);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Tournament deleted successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error deleting tournament: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>You do not have permission to delete tournaments.</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $tournament_id = intval($_POST['tournament_id']);
    $category_id = intval($_POST['category_id']);

    if ($tournament_id === 0 || $category_id === 0) {
        die("Please select a valid tournament and category.");
    }

    $insertQuery = "INSERT INTO tournament_categories (tournament_id, category_id) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE category_id = category_id";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('ii', $tournament_id, $category_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Category assigned successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error assigning category: " . htmlspecialchars($stmt->error) . "</p>";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_moderator'])) {
        $tournament_id = intval($_POST['tournament_id']);
        $moderator_id = intval($_POST['moderator_id']);

        if ($tournament_id === 0 || $moderator_id === 0) {
            die("Please select a valid tournament and moderator.");
        }

        $insertQuery = "INSERT INTO tournament_moderators (tournament_id, user_id) VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE user_id = user_id";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('ii', $tournament_id, $moderator_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Moderator assigned successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error assigning moderator: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['remove_moderator'])) {
        $tournament_id = intval($_POST['tournament_id']);
        $moderator_id = intval($_POST['moderator_id']);

        $deleteQuery = "DELETE FROM tournament_moderators WHERE tournament_id = ? AND user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('ii', $tournament_id, $moderator_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Moderator removed successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error removing moderator: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    }
}

$tournaments = fetchTournaments($conn);
$categories_result = $conn->query("SELECT * FROM categories");
$users_result = $conn->query("SELECT id, username FROM users ORDER BY username");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tournaments</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-container { display: flex; flex-wrap: wrap; gap: 20px; margin: 0 auto; max-width: 1200px; }
        form { flex: 1; min-width: 300px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        input, select, button { width: 100%; padding: 10px; margin-bottom: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; cursor: pointer; transition: all 0.3s ease; border: none; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        button:hover { background: #0056b3; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
        table { width: 100%; margin: 20px 0; border-collapse: collapse; background: #fff; border-radius: 8px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        th.categories-column { width: 20%; }
        .moderator-list { display: flex; gap: 5px; flex-wrap: wrap; }
        .moderator-item { padding: 5px 10px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; }
        .small-button { margin-left: 8px; background: #f5f5f5; color: #333; padding: 2px 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 12px; cursor: pointer; transition: all 0.3s ease; }
        .small-button:hover { background: #ddd; color: #000; border-color: #aaa; }
    </style>
</head>
<body>
    <h1>Manage Tournaments</h1>
    <div class="form-container">
        <form method="POST">
            <h2>Add Tournament</h2>
            <input type="text" name="tournament_name" placeholder="Tournament Name" required>
            <button type="submit" name="add_tournament">Add Tournament</button>
        </form>
        <form method="POST">
            <h2>Assign Categories</h2>
            <select name="tournament_id" required>
                <option value="">Select Tournament</option>
                <?php foreach ($tournaments as $tournament): ?>
                    <option value="<?= $tournament['tournament_id'] ?>"><?= htmlspecialchars($tournament['tournament_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_category">Assign Category</button>
        </form>
        <form method="POST">
            <h2>Assign Moderators</h2>
            <select name="tournament_id" required>
                <option value="">Select Tournament</option>
                <?php foreach ($tournaments as $tournament): ?>
                    <option value="<?= $tournament['tournament_id'] ?>"><?= htmlspecialchars($tournament['tournament_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="moderator_id" required>
                <option value="">Select Moderator</option>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_moderator">Assign Moderator</button>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Owner</th>
                <th class="categories-column">Categories</th>
                <th>Moderators</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tournaments as $row): ?>
                <form method="POST">
                    <tr>
                        <td><?= htmlspecialchars($row['tournament_id']) ?></td>
                        <td><input type="text" name="tournament_name" value="<?= htmlspecialchars($row['tournament_name']) ?>" required></td>
                        <td><?= htmlspecialchars($row['owner_name'] ?? 'Unknown') ?></td>
                        <td class="categories-column"><?= htmlspecialchars($row['categories'] ?? 'None') ?></td>
                        <td>
                            <div class="moderator-list">
                                <?php
                                if (!empty($row['moderators'])):
                                    $moderators = explode(', ', $row['moderators']);
                                    foreach ($moderators as $moderator):
                                        $moderator_id_query = $conn->prepare("SELECT id FROM users WHERE username = ?");
                                        $moderator_id_query->bind_param('s', $moderator);
                                        $moderator_id_query->execute();
                                        $moderator_id_query->bind_result($moderator_id);
                                        $moderator_id_query->fetch();
                                        $moderator_id_query->close();
                                ?>
                                    <div class="moderator-item">
                                        <?= htmlspecialchars($moderator) ?>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="tournament_id" value="<?= $row['tournament_id'] ?>">
                                            <input type="hidden" name="moderator_id" value="<?= $moderator_id ?>">
                                            <button type="submit" name="remove_moderator" class="small-button">×</button>
                                        </form>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </td>
                        <td>
                            <input type="hidden" name="tournament_id" value="<?= $row['tournament_id'] ?>">
                            <button type="submit" name="update_tournament" class="small-button" style="background: #007bff;">Save</button>
                            <a href="?delete_tournament=<?= $row['tournament_id'] ?>" class="small-button" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                </form>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
