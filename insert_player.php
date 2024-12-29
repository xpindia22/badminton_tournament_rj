<?php
//insert_player.php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin() && !is_user()) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO players (name, dob, age, sex, uid, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissi", $name, $dob, $age, $sex, $uid, $created_by);
    if ($stmt->execute()) {
        echo "<p>Player added successfully!</p>";
    } else {
        echo "<p>Error: {$stmt->error}</p>";
    }
    $stmt->close();
}

$result = $conn->query("SELECT * FROM players WHERE created_by = " . $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Player</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Insert Player</h1>
    <form method="post">
        <label for="name">Player Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" name="dob" id="dob" required>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" required readonly>

        <label for="sex">Sex:</label>
        <select name="sex" id="sex" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select>

        <label for="uid">Unique ID:</label>
        <input type="text" name="uid" id="uid" required>

        <button type="submit">Add Player</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <h2>Your Players</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Date of Birth</th>
                <th>Age</th>
                <th>Sex</th>
                <th>UID</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['dob'] ?></td>
                    <td><?= $row['age'] ?></td>
                    <td><?= $row['sex'] ?></td>
                    <td><?= $row['uid'] ?></td>
                    <td>
                        <a href="edit_player.php?id=<?= $row['id'] ?>">Edit</a> |
                        <a href="delete_player.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this player?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No players found.</p>
    <?php endif; ?>
</body>
</html>
