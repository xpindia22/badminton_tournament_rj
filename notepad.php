<?php
//require_once 'permissions.php';

session_start();
require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all notes for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM user_notes WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notepad</title>
    <script src="https://cdn.ckeditor.com/4.20.2/standard/ckeditor.js"></script>
</head>
<body>
    <h1>Notepad</h1>
    <a href="new_note.php">Create New Note</a>
    <hr>

    <?php if (count($notes) > 0): ?>
        <?php foreach ($notes as $note): ?>
            <div>
                <h2><?= htmlspecialchars($note['title']) ?></h2>
                <p>Last updated: <?= $note['updated_at'] ?></p>
                <a href="edit_note.php?id=<?= $note['id'] ?>">Edit</a>
                <a href="delete_note.php?id=<?= $note['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                <div><?= $note['content'] ?></div>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No notes found. <a href="new_note.php">Create one!</a></p>
    <?php endif; ?>
</body>
</html>
