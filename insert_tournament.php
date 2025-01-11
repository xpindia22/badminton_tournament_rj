<?php
ob_start(); // Start output buffering
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include 'header.php';
require 'fetch_tournaments.php'; // Include the reusable fetch logic
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

$currentUserId = $_SESSION['user_id']; // Get logged-in user ID
$currentUserRole = $_SESSION['role']; // Get logged-in user role ('admin', 'user')

// Add Tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tournament'])) {
    $tournament_name = trim($_POST['tournament_name']);
    $created_by = $currentUserId; // Set current user as creator

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
        die("Please select a valid tournament and category.");
    }

    // Check if the user has access to assign a category to the selected tournament
    if ($currentUserRole !== 'admin') {
        $result = $conn->query("SELECT created_by FROM tournaments WHERE id = $tournament_id");
        $row = $result->fetch_assoc();
        if ($row['created_by'] != $currentUserId) {
            die("You do not have permission to assign categories to this tournament.");
        }
    }

    $sql = "INSERT INTO tournament_categories (tournament_id, category_id) VALUES ($tournament_id, $category_id)";
    if (!$conn->query($sql)) {
        die("Error assigning category: " . $conn->error);
    }
}

// Update Tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tournament'])) {
    $tournament_id = intval($_POST['tournament_id']);
    $tournament_name = trim($conn->real_escape_string($_POST['tournament_name']));

    if (empty($tournament_name)) {
        die("Tournament name cannot be empty.");
    }

    // Restrict updates to admins or the tournament creator
    if ($currentUserRole !== 'admin') {
        $result = $conn->query("SELECT created_by FROM tournaments WHERE id = $tournament_id");
        $row = $result->fetch_assoc();
        if ($row['created_by'] != $currentUserId) {
            die("You do not have permission to update this tournament.");
        }
    }

    // Update the tournament name
    $sql = "UPDATE tournaments SET name='$tournament_name' WHERE id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error updating tournament: " . $conn->error);
    }

    // Redirect to prevent resubmission
    header("Location: insert_tournament.php?status=updated");
    exit;
}

// Delete Tournament
if (isset($_GET['delete_tournament'])) {
    $tournament_id = intval($_GET['delete_tournament']);

    // Restrict deletion to admins or the tournament creator
    if ($currentUserRole !== 'admin') {
        $result = $conn->query("SELECT created_by FROM tournaments WHERE id = $tournament_id");
        $row = $result->fetch_assoc();
        if ($row['created_by'] != $currentUserId) {
            die("You do not have permission to delete this tournament.");
        }
    }

    $sql = "DELETE FROM tournaments WHERE id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error deleting tournament: " . $conn->error);
    }

    $sql = "DELETE FROM tournament_categories WHERE tournament_id=$tournament_id";
    $conn->query($sql);
}

// Fetch all tournaments with their categories using `fetchTournaments`
$tournaments = fetchTournaments($conn, $currentUserId, $currentUserRole);

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

    <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
        <p style="color: green; text-align: center;">Tournament updated successfully!</p>
    <?php endif; ?>

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
