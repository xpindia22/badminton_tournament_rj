<?php
// insert_tournament.php
require 'auth.php';
redirect_if_not_logged_in();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user_id'];

    // Link category to the tournament
    $stmt = $conn->prepare("INSERT INTO tournament_categories (tournament_id, category_id, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $tournament_id, $category_id, $user_id);

    if ($stmt->execute()) {
        $message = "Category linked successfully!";
    } else {
        $message = "Error linking category: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch tournaments and categories
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Tournament</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Insert Tournament</h1>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="tournament_id">Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <option value="">Select Tournament</option>
                <?php while ($row = $tournaments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>)</option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn-primary">Link Category</button>
        </form>
    </div>
</body>
</html>
