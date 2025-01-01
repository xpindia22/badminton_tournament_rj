<?php
require_once 'conn.php';

header('Content-Type: application/json');

if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    $query = "SELECT id, name FROM players WHERE category_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $category_id);

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();
    $players = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($players);
} else {
    echo json_encode(['error' => 'Invalid or missing category_id']);
}
?>
