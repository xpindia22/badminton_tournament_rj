<?php
require 'auth.php';
redirect_if_not_logged_in();

$category_id = intval($_GET['category_id']);
$user_id = $_SESSION['user_id'];

$query = "
    SELECT id, name 
    FROM players 
    WHERE category_id = ? AND created_by = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $category_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$players = [];
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

header('Content-Type: application/json');
echo json_encode($players);
$stmt->close();
?>
