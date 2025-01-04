<?php
session_start();
require 'conn.php';

if (!isset($_GET['id'])) {
    header('Location: notepad.php');
    exit();
}

$note_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the note to edit
$stmt = $pdo->prepare("SELECT * FROM user_notes WHERE id = ? AND user_id = ?");
$stmt->execute([$note_id, $user_id]);
$note = $stmt->fetch();

if (!$note) {
    die("Note not found!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $pdo->prepare("UPDATE user_notes SET title = ?, content = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $content, $note_id, $user_id]);

    header('Location: notepad.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
    <script src="https://cdn.ckeditor.com/4.20.2/standard/ckeditor.js"></script>
</head>
<body>
    <h1>Edit Note</h1>
    <form method="POST">
        <input type="text" name="title" value="<?= htmlspecialchars($note['title']) ?>" required>
        <textarea name="content" id="editor"><?= $note['content'] ?></textarea>
        <button type="submit">Update Note</button>
    </form>
    <script>
        CKEDITOR.replace('editor');
    </script>
</body>
</html>
