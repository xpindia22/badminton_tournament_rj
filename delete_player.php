<?php
//delete_player.php
require 'auth.php';
redirect_if_not_logged_in();

if (is_logged_in()) {
    $username = $_SESSION['username']; // Assuming username is stored in the session
}

// Handle deletion
if (isset($_GET['id'])) {
    $player_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $is_admin = is_admin();

    // Verify ownership or admin privilege
    $stmt = $conn->prepare("SELECT created_by FROM players WHERE id = ?");
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $stmt->bind_result($created_by);
    $stmt->fetch();
    $stmt->close();

    if ($created_by !== $user_id && !$is_admin) {
        die("<p class='error'>Access denied: You can only delete your own players.</p>");
    }

    // Delete player
    $stmt = $conn->prepare("DELETE FROM players WHERE id = ?");
    $stmt->bind_param("i", $player_id);
    if ($stmt->execute()) {
        echo "<p class='success'>Player deleted successfully!</p>";
    } else {
        echo "<p class='error'>Error deleting player.</p>";
    }
    $stmt->close();
}

// Fetch players created by the user
$query = is_admin() ? "SELECT * FROM players" : "SELECT * FROM players WHERE created_by = ?";
$stmt = $conn->prepare($query);
if (!is_admin()) {
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
    <title>Delete Player</title>
    <link rel="stylesheet" href="styles.css">
    <script src="session.js"></script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Delete Player</h1>
        <h2>Your Players</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>UID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['dob']) ?></td>
                            <td><?= $row['age'] ?></td>
                            <td><?= $row['sex'] === 'M' ? 'Male' : 'Female' ?></td>
                            <td><?= htmlspecialchars($row['uid']) ?></td>
                            <td>
                                <a href="delete_player.php?id=<?= $row['id'] ?>" class="action-link" onclick="return confirm('Are you sure you want to delete this player?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No players found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
