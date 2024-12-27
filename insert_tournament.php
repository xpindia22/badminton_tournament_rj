<!-- HTML and PHP: Insert Tournament -->
<!DOCTYPE html>
<html>
<head>
    <title>Insert Tournament</title>
</head>
<body>
    <form method="post" action="insert_tournament.php">
        Tournament Name: <input type="text" name="name" required><br>
        Year: <input type="number" name="year" required><br>
        <button type="submit">Add Tournament</button>
    </form>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $year = $_POST['year'];

    $sql = "INSERT INTO tournaments (name, year) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $year);

    if ($stmt->execute()) {
        echo "Tournament added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>