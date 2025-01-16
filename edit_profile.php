<?php
session_start();
require 'conn.php';

if (!isset($_SESSION['player_id'])) {
    header("Location: login.php");
    exit;
}

$player_id = $_SESSION['player_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $category = $_POST['category_id'];

    $stmt = $conn->prepare("UPDATE players SET name = ?, dob = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $dob, $category, $player_id);
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

$stmt = $conn->prepare("SELECT name, dob, category_id FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$stmt->bind_result($name, $dob, $category_id);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head><title>Edit Profile</title></head>
<body>
    <h2>Edit Profile</h2>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
        <label>DOB:</label>
        <input type="date" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
        <label>Category:</label>
        <input type="number" name="category_id" value="<?= htmlspecialchars($category_id) ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
