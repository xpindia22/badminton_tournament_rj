<?php
include 'conn.php'; // Adjust to your database connection file

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$category_id = $data['category_id'];

$stmt = $conn->prepare("SELECT age_group, sex FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($age_group, $category_sex);
$stmt->fetch();
$stmt->close();

$age_range = explode('-', $age_group);
$min_age = (int)$age_range[0];
$max_age = (int)$age_range[1];

$stmt = $conn->prepare("
    SELECT id, name, dob, sex 
    FROM players 
    WHERE sex = ? 
    AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN ? AND ?
");
$stmt->bind_param("sii", $category_sex, $min_age, $max_age);
$stmt->execute();
$result = $stmt->get_result();

$players = [];
while ($row = $result->fetch_assoc()) {
    $age = date_diff(date_create($row['dob']), date_create('now'))->y;
    $players[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'age' => $age,
        'sex' => $row['sex']
    ];
}

echo json_encode(['players' => $players]);
?>
