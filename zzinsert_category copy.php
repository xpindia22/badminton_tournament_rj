<?php
require 'auth.php';
redirect_if_not_logged_in();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age_group = trim($_POST['age_group']);
    $sex = $_POST['sex'];
    $user_id = $_SESSION['user_id'];

    // Check if the category already exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND age_group = ? AND sex = ?");
    $stmt->bind_param("sss", $name, $age_group, $sex);
    $stmt->execute();
    $stmt->bind_result($category_id);
    $exists = $stmt->fetch();
    $stmt->close();

    if ($exists) {
        // Link the existing category to the user
        $stmt = $conn->prepare("SELECT id FROM category_access WHERE category_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $category_id, $user_id);
        $stmt->execute();
        $linked = $stmt->fetch();
        $stmt->close();

        if (!$linked) {
            $stmt = $conn->prepare("INSERT INTO category_access (category_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $category_id, $user_id);
            if ($stmt->execute()) {
                $message = "Category linked to your account.";
            } else {
                $message = "Error linking category: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Category already linked to your account.";
        }
    } else {
        // Add new category and link it to the user
        $stmt = $conn->prepare("INSERT INTO categories (name, age_group, sex, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $age_group, $sex, $user_id);
        if ($stmt->execute()) {
            $category_id = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO category_access (category_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $category_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Category added and linked successfully.";
        } else {
            $message = "Error adding category: " . $stmt->error;
        }
    }
}

// Fetch categories and user access
$query = "
    SELECT c.id, c.name, c.age_group, c.sex, u.username AS creator_name,
           CASE WHEN ca.user_id IS NOT NULL THEN 1 ELSE 0 END AS linked
    FROM categories c
    LEFT JOIN users u ON c.created_by = u.id
    LEFT JOIN category_access ca ON c.id = ca.category_id AND ca.user_id = ?
";
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

        <h2>Existing Categories or Create One Now.</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age Group</th>
                    <th>Sex</th>
                    <th>Created By</th>
                    <th>Linked</th>
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
                        <td><?= $row['linked'] ? "Yes" : "No" ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
