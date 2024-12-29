<?php
require 'auth.php';
redirect_if_not_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age_group = trim($_POST['age_group']);
    $sex = $_POST['sex'];
    $created_by = $_SESSION['user_id'];

    // Validation for Veterans categories
    $veteran_40_plus = strpos(strtolower($age_group), 'veterans 40 plus') !== false;
    $veteran_55_plus = strpos(strtolower($age_group), 'veterans 55 plus') !== false;

    if ($veteran_40_plus && !preg_match('/40\+/', $age_group)) {
        echo "<p class='error'>For Veterans 40 Plus, the age group must include '40+'.</p>";
    } elseif ($veteran_55_plus && !preg_match('/55\+/', $age_group)) {
        echo "<p class='error'>For Veterans 55 Plus, the age group must include '55+'.</p>";
    } else {
        // Insert category into the database
        $stmt = $conn->prepare("INSERT INTO categories (name, age_group, sex, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $age_group, $sex, $created_by);
        if ($stmt->execute()) {
            echo "<p class='success'>Category added successfully.</p>";
        } else {
            echo "<p class='error'>Error adding category: {$stmt->error}</p>";
        }
        $stmt->close();
    }
}

// Fetch categories
if (is_admin()) {
    $query = "SELECT c.*, u.username AS creator_name FROM categories c LEFT JOIN users u ON c.created_by = u.id";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT * FROM categories WHERE created_by = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$result = $stmt->get_result();
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

        <h2><?= is_admin() ? "All Categories" : "Your Categories" ?></h2>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age Group</th>
                    <th>Sex</th>
                    <?php if (is_admin()): ?>
                    <th>Created By</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['age_group']) ?></td>
                    <td><?= htmlspecialchars($row['sex']) ?></td>
                    <?php if (is_admin()): ?>
                    <td><?= htmlspecialchars($row['creator_name']) ?></td>
                    <?php endif; ?>
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
