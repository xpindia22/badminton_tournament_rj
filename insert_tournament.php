<?php
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
    $categories = trim($conn->real_escape_string($_POST['categories']));

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

    // Update the categories
    $sql = "DELETE FROM tournament_categories WHERE tournament_id=$tournament_id";
    if (!$conn->query($sql)) {
        die("Error clearing categories: " . $conn->error);
    }

    $categories_array = explode(',', $categories);
    foreach ($categories_array as $category_name) {
        $category_name = trim($category_name);
        if (!empty($category_name)) {
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
    <!-- Add your existing CSS here -->
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
            <?php foreach ($tournaments as $row): ?>
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
                            <?php if ($currentUserRole === 'admin' || $row['created_by'] == $currentUserId): ?>
                                <button type="submit" name="update_tournament">Save</button>
                                <a href="?delete_tournament=<?= $row['tournament_id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php else: ?>
                                <span>No Actions Available</span>
                            <?php endif; ?>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
