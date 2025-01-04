<?php
session_start();
require 'conn.php';

if (isset($_GET['id'])) {
    $note_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM user_notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $user_id]);

    header('Location: notepad.php');
    exit();
}
?>
