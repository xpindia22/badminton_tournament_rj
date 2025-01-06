<?php
// delete_category.php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin()) die("Access denied.");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Verify if the category exists before attempting deletion
    $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: insert_category.php");
            exit;
        } else {
            echo "<p class='error'>Error deleting category: {$stmt->error}</p>";
        }
        $stmt->close();
    } else {
        echo "<p class='error'>Category not found or already deleted.</p>";
    }
    $result->close();
}
?>
