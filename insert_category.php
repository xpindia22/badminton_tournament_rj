<?php
// insert_category.php
require 'auth.php';
redirect_if_not_logged_in();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age_group = trim($_POST['age_group']);
    $sex = $_POST['sex'];
    $created_by = $_SESSION['user_id'];

    // Validation for specific categories like Veterans 35+ and above
    if (preg_match('/Senior (\d+)\+/', $age_group, $matches)) {
        $min_age = intval($matches[1]);
        if ($min_age < 35) {
            $message = "For Senior categories, age must be 35+.";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, age_group, sex, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $age_group, $sex, $created_by);
            if ($stmt->execute()) {
                $message = "Category added successfully!";
            } else {
                $message = "Error adding category: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
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

// Fetch categories
$query = "SELECT c.*, u.username AS creator_name FROM categories c LEFT JOIN users u ON c.created_by = u.id";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Category</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

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
                <label for="age_group">Age Group:</label>
                <input type="text" name="age_group" id="age_group" required>
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select name="sex" id="sex">
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Add Category</button>
        </form>

        <h2>All Categories</h2>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age Group</th>
                    <th>Sex</th>
                    <th>Created By</th>
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
