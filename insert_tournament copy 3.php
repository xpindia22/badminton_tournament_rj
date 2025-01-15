<?php
ob_start(); // Start output buffering
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'header.php';
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

$currentUserId = $_SESSION['user_id']; // Get logged-in user ID
$currentUserRole = $_SESSION['role']; // Get logged-in user role ('admin', 'user')

// Function to fetch tournaments with categories and moderators
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

// Add Tournament
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

// Update Tournament Name
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

// Soft Delete Tournament
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

// Assign Category to Tournament
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin: 0 auto;
            max-width: 1200px;
        }

        form {
            flex: 1;
            min-width: 300px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        form h2 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #555;
        }

        form input, form select, form button {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        table thead {
            background-color: #007bff;
            color: white;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .actions button, .actions a {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }

        .actions .edit {
            background-color: #28a745;
        }

        .actions .delete {
            background-color: #dc3545;
        }

        .actions .edit:hover {
            background-color: #218838;
        }

        .actions .delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h1>Manage Tournaments</h1>

    <div class="form-container">
        <!-- Add Tournament Form -->
        <form method="POST">
            <h2>Add Tournament</h2>
            <input type="text" name="tournament_name" placeholder="Tournament Name" required>
            <button type="submit" name="add_tournament">Add Tournament</button>
        </form>

        <!-- Assign Categories to Tournaments Form -->
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
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Owner</th>
                <th>Categories</th>
                <th>Moderators</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tournaments as $row): ?>
                <form method="POST">
                    <tr>
                        <td><?= htmlspecialchars($row['tournament_id']) ?></td>
                        <td>
                            <input type="text" name="tournament_name" value="<?= htmlspecialchars($row['tournament_name']) ?>" required>
                        </td>
                        <td><?= htmlspecialchars($row['owner_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($row['categories'] ?? 'None') ?></td>
                        <td><?= htmlspecialchars($row['moderators'] ?? 'None') ?></td>
                        <td class="actions">
                            <input type="hidden" name="tournament_id" value="<?= $row['tournament_id'] ?>">
                            <button type="submit" name="update_tournament" class="edit">Save</button>
                            <a href="?delete_tournament=<?= $row['tournament_id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                </form>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
