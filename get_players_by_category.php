<?php
require 'auth.php';
////require_once 'permissions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = intval($_POST['category_id']);

    $stmt = $conn->prepare("SELECT age_group, sex FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($age_group, $sex);
    $stmt->fetch();
    $stmt->close();

    $age_limits = explode('-', $age_group);
    $min_age = intval($age_limits[0]);
    $max_age = intval($age_limits[1]);

    $current_year = date("Y");
    $min_dob = ($current_year - $max_age) . "-01-01";
    $max_dob = ($current_year - $min_age) . "-12-31";

    $stmt = $conn->prepare("SELECT id, name, dob FROM players WHERE sex = ? AND dob BETWEEN ? AND ?");
    $stmt->bind_param("sss", $sex, $min_dob, $max_dob);
    $stmt->execute();
    $result = $stmt->get_result();

    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }

    echo json_encode($players);
    exit;
}
?>
