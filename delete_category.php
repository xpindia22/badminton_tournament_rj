<?php
require 'auth.php';
redirect_if_not_logged_in();
if (!is_admin()) die("Access denied.");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: insert_category.php");
        exit;
    } else {
        echo "<p class='error'>Error deleting category: {$stmt->error}</p>";
    }
    $stmt->close();
}
?>
