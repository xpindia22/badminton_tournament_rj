<?php
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    $stmt = $conn->prepare("SELECT age_group, sex FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($age_group, $sex);
    $stmt->fetch();
    $stmt->close();

    if (!$age_group || !$sex) {
        echo json_encode([]);
        exit;
    }

    $min_dob = "1900-01-01"; 
    $max_dob = date("Y-m-d");

    if (strpos($age_group, 'Under') !== false) {
        preg_match('/Under\s+(\d+)/i', $age_group, $matches);
        if (isset($matches[1])) {
            $max_age = intval($matches[1]);
            $max_dob = date("Y-m-d", strtotime("-{$max_age} years"));
        }
    } elseif (strpos($age_group, 'Over') !== false) {
        preg_match('/Over\s+(\d+)/i', $age_group, $matches);
        if (isset($matches[1])) {
            $min_age = intval($matches[1]);
            $min_dob = date("Y-m-d", strtotime("-{$min_age} years"));
        }
    } elseif (strpos($age_group, 'Between') !== false) {
        preg_match('/Between\s+(\d+)\s*-\s*(\d+)/i', $age_group, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $min_age = intval($matches[1]);
            $max_age = intval($matches[2]);
            $max_dob = date("Y-m-d", strtotime("-{$min_age} years"));
            $min_dob = date("Y-m-d", strtotime("-{$max_age} years"));
        }
    }

    $stmt = $conn->prepare("SELECT id, name, dob, sex FROM players WHERE sex = ? AND dob BETWEEN ? AND ?");
    $stmt->bind_param("sss", $sex, $min_dob, $max_dob);
    $stmt->execute();
    $result = $stmt->get_result();

    $players = [];
    while ($row = $result->fetch_assoc()) {
        $age = date("Y") - date("Y", strtotime($row['dob']));
        if (date("md", strtotime($row['dob'])) > date("md")) {
            $age--;
        }
        $row['age'] = $age;
        $players[] = $row;
    }

    echo json_encode($players);
    exit;
}
?>
