<?php
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    // Fetch category details (age group and sex)
    $stmt = $conn->prepare("SELECT age_group, sex, name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($age_group, $sex, $category_name);
    $stmt->fetch();
    $stmt->close();

    if (!$age_group || !$sex || !$category_name) {
        echo json_encode([]);
        exit;
    }

    // Initialize date ranges for filtering
    $min_dob = "1900-01-01"; // Default minimum DOB
    $max_dob = date("Y-m-d"); // Today's date as the default maximum DOB

    // Parse the age group
    if (strpos($age_group, 'Under') !== false) {
        // Handle "Under X" age group
        preg_match('/Under\s+(\d+)/i', $age_group, $matches);
        if (isset($matches[1])) {
            $max_age = intval($matches[1]);
            $min_dob = date("Y-m-d", strtotime("-{$max_age} years -1 day")); // DOB must be before this
        }
    } elseif (strpos($age_group, 'Over') !== false) {
        // Handle "Over X" age group
        preg_match('/Over\s+(\d+)/i', $age_group, $matches);
        if (isset($matches[1])) {
            $min_age = intval($matches[1]);
            $max_dob = date("Y-m-d", strtotime("-{$min_age} years")); // DOB must be after this
        }
    } elseif (strpos($age_group, 'Between') !== false) {
        // Handle "Between X - Y" age group
        preg_match('/Between\s+(\d+)\s*-\s*(\d+)/i', $age_group, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $min_age = intval($matches[1]);
            $max_age = intval($matches[2]);
            $max_dob = date("Y-m-d", strtotime("-{$min_age} years")); // Max age DOB range
            $min_dob = date("Y-m-d", strtotime("-{$max_age} years -1 day")); // Min age DOB range
        }
    }

    // Special handling for categories with "XD" (Mixed Doubles)
    $players = [];
    if (strpos($category_name, 'XD') !== false) {
        // Fetch both male and female players
        $stmt = $conn->prepare("SELECT id, name, dob, sex FROM players WHERE dob BETWEEN ? AND ?");
        $stmt->bind_param("ss", $min_dob, $max_dob);
    } else {
        // Fetch players matching the category's sex
        $stmt = $conn->prepare("SELECT id, name, dob, sex FROM players WHERE sex = ? AND dob BETWEEN ? AND ?");
        $stmt->bind_param("sss", $sex, $min_dob, $max_dob);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Calculate player's age
        $age = date("Y") - date("Y", strtotime($row['dob']));
        if (date("md", strtotime($row['dob'])) > date("md")) {
            $age--;
        }
        $row['age'] = $age; // Add calculated age to the player data
        $players[] = $row;
    }

    echo json_encode($players);
    exit;
}
?>
