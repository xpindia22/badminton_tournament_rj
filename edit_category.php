<?php
//edit_category.php
include 'header.php';
require 'auth.php';

require_non_player();
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

    // Extract age range details for pre-filling the form
    if (preg_match('/^(Under|Over|Between)\s(\d+)(?:\s?-\s?(\d+))?/', $category['age_group'], $matches)) {
        $age_condition = $matches[1];
        $age_limit1 = intval($matches[2]);
        $age_limit2 = isset($matches[3]) ? intval($matches[3]) : null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $age_condition = $_POST['age_condition'];
    $age_limit1 = intval($_POST['age_limit1']);
    $age_limit2 = isset($_POST['age_limit2']) ? intval($_POST['age_limit2']) : null;
    $sex = $_POST['sex'];

    // Build the age group string based on the inputs
    if ($age_condition === 'Under') {
        $age_group = "Under $age_limit1";
    } elseif ($age_condition === 'Over') {
        $age_group = "Over $age_limit1";
    } elseif ($age_condition === 'Between') {
        $age_group = "Between $age_limit1 - $age_limit2";
    } else {
        $age_group = '';
    }

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
                <label for="age_condition">Age Group Condition:</label>
                <select name="age_condition" id="age_condition" required onchange="toggleAgeInputs()">
                    <option value="Under" <?= $age_condition === 'Under' ? 'selected' : '' ?>>Under</option>
                    <option value="Over" <?= $age_condition === 'Over' ? 'selected' : '' ?>>Over</option>
                    <option value="Between" <?= $age_condition === 'Between' ? 'selected' : '' ?>>Between</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age_limit1">Age Limit 1:</label>
                <input type="number" name="age_limit1" id="age_limit1" value="<?= htmlspecialchars($age_limit1) ?>" required>
            </div>
            <div class="form-group" id="age_limit2_group" style="display: <?= $age_condition === 'Between' ? 'block' : 'none' ?>">
                <label for="age_limit2">Age Limit 2:</label>
                <input type="number" name="age_limit2" id="age_limit2" value="<?= htmlspecialchars($age_limit2) ?>">
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select name="sex" id="sex" required>
                    <option value="M" <?= $category['sex'] === 'M' ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= $category['sex'] === 'F' ? 'selected' : '' ?>>Female</option>
                    <option value="Mixed" <?= $category['sex'] === 'Mixed' ? 'selected' : '' ?>>Mixed Doubles</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Update Category</button>
        </form>
    </div>
    <script>
        function toggleAgeInputs() {
            const ageCondition = document.getElementById('age_condition').value;
            const ageLimit2Group = document.getElementById('age_limit2_group');
            if (ageCondition === 'Between') {
                ageLimit2Group.style.display = 'block';
            } else {
                ageLimit2Group.style.display = 'none';
            }
        }
    </script>
</body>
</html>
