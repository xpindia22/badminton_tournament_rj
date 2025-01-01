<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "xxx", "badminton_tournament");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add Tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tournament'])) {
    $tournament_name = trim($_POST['tournament_name']); // Trim whitespace
    $created_by = 1; // Replace with actual user ID, e.g., $_SESSION['user_id']

    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    $tournament_name = $conn->real_escape_string($tournament_name); // Escape input
    $sql = "INSERT INTO tournaments (name, created_by) VALUES ('$tournament_name', $created_by)";
    if (!$conn->query($sql)) {
        die("Error adding tournament: " . $conn->error); // Output error
    }
}

// Edit Tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_tournament'])) {
    $tournament_id = intval($_POST['tournament_id']);
    $tournament_name = trim($conn->real_escape_string($_POST['tournament_name']));
    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    $sql = "UPDATE tournaments SET name='$tournament_name' WHERE id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error editing tournament: " . $conn->error);
    }
}

// Delete Tournament
if (isset($_GET['delete_tournament'])) {
    $tournament_id = intval($_GET['delete_tournament']);
    $sql = "DELETE FROM tournaments WHERE id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error deleting tournament: " . $conn->error);
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
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        form {
            margin: 20px auto;
            padding: 10px;
            width: 50%;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form input, form select, form button {
            display: block;
            width: calc(100% - 20px);
            margin: 10px auto;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        form button:hover {
            background-color: #218838;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background: #28a745;
            color: white;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(odd) {
            background: #f9f9f9;
        }

        table tbody tr:nth-child(even) {
            background: #ffffff;
        }

        table tbody tr:hover {
            background: #f1f1f1;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Manage Tournaments</h1>

    <!-- Add Tournament Form -->
    <form method="POST">
        <input type="text" name="tournament_name" placeholder="Tournament Name" required>
        <button type="submit" name="add_tournament">Add Tournament</button>
    </form>

    <!-- Tournament Table -->
    <h2>Tournaments</h2>
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
                    <td><?= $row['tournament_id'] ?></td>
                    <td><?= htmlspecialchars($row['tournament_name']) ?></td>
                    <td><?= htmlspecialchars($row['categories'] ?? 'None') ?></td>
                    <td>
                        <!-- Edit Form -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="tournament_id" value="<?= $row['tournament_id'] ?>">
                            <input type="text" name="tournament_name" value="<?= htmlspecialchars($row['tournament_name']) ?>" required>
                            <button type="submit" name="edit_tournament">Edit</button>
                        </form>
                        <!-- Delete Link -->
                        <a href="?delete_tournament=<?= $row['tournament_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add Category to Tournament Form -->
    <h2>Assign Categories to Tournaments</h2>
    <form method="POST">
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
</body>
</html>
