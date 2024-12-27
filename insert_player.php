<!-- HTML and PHP: Insert Player -->
<!DOCTYPE html>
<html>
<head>
    <title>Insert Player</title>
</head>
<body>
    <form method="post" action="insert_player.php">
        Player Name: <input type="text" name="name" required><br>
        Age: <input type="number" name="age" required><br>
        Sex: 
        <select name="sex" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select><br>
        UID: <input type="text" name="uid" required><br>
        <button type="submit">Add Player</button>
    </form>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];

    $sql = "INSERT INTO players (name, age, sex, uid) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $name, $age, $sex, $uid);

    if ($stmt->execute()) {
        echo "Player added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
