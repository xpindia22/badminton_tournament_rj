<?php
require 'conn.php'; // Include the database connection file
////require_once 'permissions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']); // Sanitize the tournament ID

    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM tournament_categories tc
        INNER JOIN categories c ON tc.category_id = c.id
        WHERE tc.tournament_id = ?
    ");
    $stmt->bind_param("i", $tournament_id); // Bind the tournament ID to the query
    $stmt->execute();

    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC); // Fetch all categories as an associative array

    echo json_encode($categories); // Return the categories as JSON
}
?>
