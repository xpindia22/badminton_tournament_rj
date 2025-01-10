<?php
//require_once 'permissions.php';

session_start();
require '/var/www/html/badminton_tournament/conn.php';

if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    try {
        $stmt = $pdo->prepare("INSERT INTO user_notes (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $content]);

        header('Location: notepad.php');
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
