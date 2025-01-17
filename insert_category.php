<?php
// insert_category.php
include 'header.php';
//require_once 'permissions.php';

require 'auth.php';
require_once 'admin_auth.php';
redirect_if_not_logged_in();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

// Handle sorting
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'name'; // Default to 'name'
$order_dir = isset($_GET['order_dir']) && $_GET['order_dir'] === 'asc' ? 'asc' : 'desc'; // Default to 'desc'
$next_order_dir = $order_dir === 'asc' ? 'desc' : 'asc';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age_condition = $_POST['age_condition'];
    $age_limit1 = isset($_POST['age_limit1']) ? intval($_POST['age_limit1']) : null;
    $age_limit2 = isset($_POST['age_limit2']) ? intval($_POST['age_limit2']) : null;
    $sex = $_POST['sex'];
    $created_by = $_SESSION['user_id'];

    // Validate the sex value
    if (!in_array($sex, ['M', 'F', 'Mixed'])) {
        $message = "Invalid value for sex.";
    } else {
        // Build the age group string
        if ($age_condition === 'Under') {
            $age_group = "Under $age_limit1";
        } elseif ($age_condition === 'Over') {
            $age_group = "Over $age_limit1";
        } elseif ($age_condition === 'Between') {
            $age_group = "Between $age_limit1 - $age_limit2";
        } elseif ($age_condition === 'Open') {
            $age_group = "Open";
        } else {
            $age_group = '';
        }

        // Validate age group
        if ($age_condition === 'Under' && $age_limit1 >= 20) {
            $message = "For 'Under' categories, age limit must be less than 20.";
        } elseif ($age_condition === 'Over' && $age_limit1 < 35) {
            $message = "For 'Over' categories, age must be 35+.";
        } elseif ($age_condition === 'Between' && (!$age_limit2 || $age_limit1 >= $age_limit2)) {
            $message = "For 'Between' categories, specify a valid age range (e.g., 'Between 20 - 35').";
        } else {
            // Insert into the database
            $stmt = $conn->prepare("INSERT INTO categories (name, age_group, sex, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $age_group, $sex, $created_by);
            if ($stmt->execute()) {
                $message = "Category added successfully!";
            } else {
                $message = "Error adding category: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch categories with sorting
$query = "SELECT c.*, u.username AS creator_name FROM categories c LEFT JOIN users u ON c.created_by = u.id ORDER BY $order_by $order_dir";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Category</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function toggleAgeInputs() {
            const ageCondition = document.getElementById('age_condition').value;
            const ageLimit1Group = document.getElementById('age_limit1_group');
            const ageLimit2Group = document.getElementById('age_limit2_group');

            if (ageCondition === 'Between') {
                ageLimit1Group.style.display = 'block';
                ageLimit2Group.style.display = 'block';
            } else if (ageCondition === 'Open') {
                ageLimit1Group.style.display = 'none';
                ageLimit2Group.style.display = 'none';
            } else {
                ageLimit1Group.style.display = 'block';
                ageLimit2Group.style.display = 'none';
            }
        }
    </script>
</head>
<body>
 

    <div class="container">
        <h1>Insert Category</h1>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" class="form-styled">
            <div class="form-group">
                <label for="name">Category Name:</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="age_condition">Age Group Condition:</label>
                <select name="age_condition" id="age_condition" required onchange="toggleAgeInputs()">
                    <option value="Under">Under</option>
                    <option value="Over">Over</option>
                    <option value="Between">Between</option>
                    <option value="Open">Open</option>
                </select>
            </div>
            <div class="form-group" id="age_limit1_group">
                <label for="age_limit1">Age Limit 1:</label>
                <input type="number" name="age_limit1" id="age_limit1">
            </div>
            <div class="form-group" id="age_limit2_group" style="display: none;">
                <label for="age_limit2">Age Limit 2:</label>
                <input type="number" name="age_limit2" id="age_limit2">
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select name="sex" id="sex" required>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="Mixed">Mixed Doubles</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Add Category</button>
        </form>

        <h2>All Categories</h2>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th><a href="?order_by=id&order_dir=<?= $next_order_dir ?>">ID</a></th>
                    <th><a href="?order_by=name&order_dir=<?= $next_order_dir ?>">Name</a></th>
                    <th><a href="?order_by=age_group&order_dir=<?= $next_order_dir ?>">Age Group</a></th>
                    <th><a href="?order_by=sex&order_dir=<?= $next_order_dir ?>">Sex</a></th>
                    <th><a href="?order_by=creator_name&order_dir=<?= $next_order_dir ?>">Created By</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['age_group']) ?></td>
                    <td><?= htmlspecialchars($row['sex']) ?></td>
                    <td><?= htmlspecialchars($row['creator_name']) ?></td>
                    <td>
                        <?php if (is_admin()): ?>
                            <a href="edit_category.php?id=<?= $row['id'] ?>">Edit</a> |
                            <a href="delete_category.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                        <?php else: ?>
                            <span>Not Allowed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No categories found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
