<?php
//fetch_player.php
//require_once 'permissions.php';

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "xxx", "badminton_tournament");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $category_query = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $category_query->bind_param("i", $category_id);
    $category_query->execute();
    $category_result = $category_query->get_result();
    $category = $category_result->fetch_assoc();

    if ($category) {
        $category_name = $category['name'];

        // Determine gender condition
        if (strpos($category_name, 'B') !== false) { // Boys/Male only
            $gender_condition = "WHERE sex = 'M'";
        } elseif (strpos($category_name, 'G') !== false) { // Girls/Female only
            $gender_condition = "WHERE sex = 'F'";
        } elseif (strpos($category_name, 'XD') !== false) { // Mixed Doubles
            $gender_condition = ""; // No filter
        } else { // Open or Veterans
            $gender_condition = ""; // No filter
        }

        $query = "SELECT id, name FROM players $gender_condition";
        $result = $conn->query($query);
        $players = [];
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }

        echo json_encode($players);
    } else {
        echo json_encode(['error' => 'Invalid category ID']);
    }
} else {
    echo json_encode(['error' => 'Category ID is required']);
}

$conn->close();
?>