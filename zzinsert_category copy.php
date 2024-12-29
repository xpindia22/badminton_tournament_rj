<?php
require 'auth.php';
redirect_if_not_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age_group = $_POST['age_group'];
    $sex = $_POST['sex'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO categories (name, age_group, sex, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $age_group, $sex, $created_by);
    if ($stmt->execute()) {
        echo "Category added successfully.";
    } else {
        echo "Error adding category.";
    }
    $stmt->close();
}

// Fetch categories created by the user
$query = "SELECT * FROM categories WHERE created_by = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
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
    <h1>Insert Category</h1>
    <form method="post">
        <label for="name">Category Name:</label>
        <input type="text" name="name" id="name" required>
        <label for="age_group">Age Group:</label>
        <input type="text" name="age_group" id="age_group" required>
        <label for="sex">Sex:</label>
        <select name="sex" id="sex">
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select>
        <button type="submit">Add Category</button>
    </form>
    <h2>Your Categories</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age Group</th>
            <th>Sex</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['age_group'] ?></td>
            <td><?= $row['sex'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
