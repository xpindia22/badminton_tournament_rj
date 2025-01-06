<?php
include 'header.php';
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

// Add Tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tournament'])) {
    $tournament_name = trim($_POST['tournament_name']);
    $created_by = 1;

    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    $tournament_name = $conn->real_escape_string($tournament_name);
    $sql = "INSERT INTO tournaments (name, created_by) VALUES ('$tournament_name', $created_by)";
    if (!$conn->query($sql)) {
        die("Error adding tournament: " . $conn->error);
    }
}

// Add Category to Tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $tournament_id = intval($_POST['tournament_id']);
    $category_id = intval($_POST['category_id']);
    if ($tournament_id === 0 || $category_id === 0) {
        die("Please select valid tournament and category.");
    }

    $sql = "INSERT INTO tournament_categories (tournament_id, category_id) VALUES ($tournament_id, $category_id)";
    if (!$conn->query($sql)) {
        die("Error assigning category: " . $conn->error);
    }
}

// Update Tournament Row
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tournament'])) {
    $tournament_id = intval($_POST['tournament_id']);
    $tournament_name = trim($conn->real_escape_string($_POST['tournament_name']));
    $categories = trim($conn->real_escape_string($_POST['categories']));

    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    // Update the tournament name
    $sql = "UPDATE tournaments SET name='$tournament_name' WHERE id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error updating tournament: " . $conn->error);
    }

    // Update the categories
    $sql = "DELETE FROM tournament_categories WHERE tournament_id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error clearing categories: " . $conn->error);
    }

    $categories_array = explode(',', $categories);
    foreach ($categories_array as $category_name) {
        $category_name = trim($category_name);
        if (!empty($category_name)) {
            // Insert the category
            $category_id_result = $conn->query("SELECT id FROM categories WHERE name='$category_name'");
            if ($category_id_result && $category_id_result->num_rows > 0) {
                $category_id = $category_id_result->fetch_assoc()['id'];
                $conn->query("INSERT INTO tournament_categories (tournament_id, category_id) VALUES ($tournament_id, $category_id)");
            }
        }
    }
}

// Delete Tournament
if (isset($_GET['delete_tournament'])) {
    $tournament_id = intval($_GET['delete_tournament']);
    $sql = "DELETE FROM tournaments WHERE id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error deleting tournament: " . $conn->error);
    }
    $sql = "DELETE FROM tournament_categories WHERE tournament_id=$tournament_id";
    $conn->query($sql);
}

// Fetch all tournaments with their categories
$sql = "
    SELECT t.id AS tournament_id, t.name AS tournament_name, 
           GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
    FROM tournaments t
    LEFT JOIN tournament_categories tc ON t.id = tc.tournament_id
    LEFT JOIN categories c ON tc.category_id = c.id
    GROUP BY t.id";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching tournaments: " . $conn->error);
}

// Fetch all categories for the dropdown
$categories_result = $conn->query("SELECT * FROM categories");
if (!$categories_result) {
    die("Error fetching categories: " . $conn->error);
}
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
            background-color: #f9f9f9;
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
            margin: 20px auto;
            width: 100%;
            max-width: 1200px;
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
            padding: 6px 8px; /* Reduced row height */
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
            border: none;
            border-radius: 4px;
            color: white;
            background-color: #007bff;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .actions a.delete {
            background-color: #dc3545;
        }

        .actions button:hover, .actions a:hover {
            opacity: 0.9;
        }

        .actions a.delete:hover {
            background-color: #c82333;
        }

        input[type="text"] {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
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
                <?php
                $tournaments = $conn->query("SELECT * FROM tournaments");
                while ($tournament = $tournaments->fetch_assoc()):
                ?>
                    <option value="<?= $tournament['id'] ?>"><?= htmlspecialchars($tournament['name']) ?></option>
                <?php endwhile; ?>
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

    <!-- Tournament Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Categories</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <form method="POST">
                        <td><?= $row['tournament_id'] ?></td>
                        <td>
                            <input type="text" name="tournament_name" value="<?= htmlspecialchars($row['tournament_name']) ?>">
                        </td>
                        <td>
                            <input type="text" name="categories" value="<?= htmlspecialchars($row['categories'] ?? '') ?>">
                        </td>
                        <td class="actions">
                            <input type="hidden" name="tournament_id" value="<?= $row['tournament_id'] ?>">
                            <button type="submit" name="update_tournament">Save</button>
                            <a href="?delete_tournament=<?= $row['tournament_id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
