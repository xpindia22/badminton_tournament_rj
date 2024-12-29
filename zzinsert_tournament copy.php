<?php
require 'auth.php';
redirect_if_not_logged_in();

if (is_logged_in()) {
    $username = $_SESSION['username']; // Assuming username is stored in the session
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $year = $_POST['year'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tournaments (name, year, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $year, $created_by);
    if ($stmt->execute()) {
        echo "<p class='success'>Tournament added successfully!</p>";
    } else {
        echo "<p class='error'>Error: {$stmt->error}</p>";
    }
    $stmt->close();
}

$result = is_admin()
    ? $conn->query("SELECT * FROM tournaments")
    : $conn->query("SELECT * FROM tournaments WHERE created_by = {$_SESSION['user_id']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Tournament</title>
    <link rel="stylesheet" href="styles.css">
    <script src="session.js"></script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Insert Tournament</h1>
        <form method="post">
            <label for="name">Tournament Name:</label>
            <input type="text" name="name" id="name" placeholder="Enter tournament name" required>
            
            <label for="year">Year:</label>
            <input type="number" name="year" id="year" placeholder="Enter year (e.g., 2024)" required>
            
            <button type="submit">Add Tournament</button>
        </form>

        <h2>Your Tournaments</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['year'] ?></td>
                            <td>
                                <a href="edit_tournament.php?id=<?= $row['id'] ?>" class="action-link">Edit</a> | 
                                <a href="delete_tournament.php?id=<?= $row['id'] ?>" class="action-link" onclick="return confirm('Are you sure you want to delete this tournament?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tournaments found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
