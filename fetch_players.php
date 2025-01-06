<?php
include 'db_connection.php'; // Replace with your DB connection file
header('Content-Type: application/json');

if (!isset($_GET['category_name'], $_GET['age_group'])) {
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$categoryName = $_GET['category_name'];
$ageGroup = $_GET['age_group'];
$ageBounds = explode('-', $ageGroup);

if (count($ageBounds) !== 2) {
    echo json_encode(['error' => 'Invalid age group format.']);
    exit;
}

$minAge = intval($ageBounds[0]);
$maxAge = intval($ageBounds[1]);

$query = "SELECT id, name, dob, sex FROM players WHERE YEAR(CURDATE()) - YEAR(dob) BETWEEN ? AND ?";
$maleQuery = $query . " AND sex = 'M'";
$femaleQuery = $query . " AND sex = 'F'";

if (strpos($categoryName, 'XD') !== false) {
    $stmtMale = $conn->prepare($maleQuery);
    $stmtMale->bind_param("ii", $minAge, $maxAge);
    $stmtMale->execute();
    $malePlayers = $stmtMale->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtMale->close();

    $stmtFemale = $conn->prepare($femaleQuery);
    $stmtFemale->bind_param("ii", $minAge, $maxAge);
    $stmtFemale->execute();
    $femalePlayers = $stmtFemale->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtFemale->close();

    echo json_encode(['male' => $malePlayers, 'female' => $femalePlayers]);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $minAge, $maxAge);
    if (strpos($categoryName, 'BD') !== false) {
        $query .= " AND sex = 'M'";
    } elseif (strpos($categoryName, 'GD') !== false) {
        $query .= " AND sex = 'F'";
    }
    $stmt->execute();
    $players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['players' => $players]);
}
?>
