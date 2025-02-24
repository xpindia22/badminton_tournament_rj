<?php
include 'header.php';
require_once 'auth.php';
redirect_if_not_logged_in();
require_non_player();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate tournament ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p class='error'>Invalid tournament ID.</p>");
}

$tournament_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Fetch tournament details
$query = $is_admin 
    ? "SELECT * FROM tournaments WHERE id = ?"
    : "SELECT * FROM tournaments WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("<p class='error'>Database error: " . $conn->error . "</p>");
}

if ($is_admin) {
    $stmt->bind_param("i", $tournament_id);
} else {
    $stmt->bind_param("ii", $tournament_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$tournament = $result->fetch_assoc();
$stmt->close();

if (!$tournament) {
    die("<p class='error'>Tournament not found or access denied.</p>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $year = intval($_POST['year']);

    $query = "UPDATE tournaments SET name = ?, year = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("<p class='error'>Database error: " . $conn->error . "</p>");
    }

    $stmt->bind_param("sii", $name, $year, $tournament_id);

    if ($stmt->execute()) {
        echo "<p class='success'>Tournament updated successfully!</p>";
    } else {
        echo "<p class='error'>Error updating tournament: {$stmt->error}</p>";
    }

    $stmt->close();
    header("Location: insert_tournament.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tournament</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Edit Tournament</h1>
        <form method="post">
            <label for="name">Tournament Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($tournament['name']) ?>" required>
            
            <label for="year">Year:</label>
            <input type="number" name="year" id="year" value="<?= htmlspecialchars($tournament['year']) ?>" required>
            
            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
