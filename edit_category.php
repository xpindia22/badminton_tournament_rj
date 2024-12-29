<?php
require 'auth.php';
redirect_if_not_logged_in();
if (!is_admin()) die("Access denied.");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $age_group = trim($_POST['age_group']);
    $sex = $_POST['sex'];

    $stmt = $conn->prepare("UPDATE categories SET name = ?, age_group = ?, sex = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $age_group, $sex, $id);
    if ($stmt->execute()) {
        header("Location: insert_category.php");
        exit;
    } else {
        echo "<p class='error'>Error updating category: {$stmt->error}</p>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Category</h1>
        <form method="post" class="form-styled">
            <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
            <div class="form-group">
                <label for="name">Category Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="age_group">Age Group:</label>
                <input type="text" name="age_group" id="age_group" value="<?= htmlspecialchars($category['age_group']) ?>" required>
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select name="sex" id="sex">
                    <option value="M" <?= $category['sex'] === 'M' ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= $category['sex'] === 'F' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Update Category</button>
        </form>
    </div>
</body>
</html>
