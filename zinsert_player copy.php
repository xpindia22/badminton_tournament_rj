<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Player</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        form { margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        input, select, button { padding: 10px; margin-bottom: 10px; width: 100%; max-width: 300px; }
    </style>
</head>
<body>
    <h1>Insert Player</h1>
    <form method="post">
        <label for="name">Player Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" required>

        <label for="sex">Sex:</label>
        <select name="sex" id="sex" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select>

        <label for="uid">Unique ID:</label>
        <input type="text" name="uid" id="uid" required>

        <button type="submit">Add Player</button>
    </form>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "xxx";
    $dbname = "badminton_tournament";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $age = $_POST['age'];
        $sex = $_POST['sex'];
        $uid = $_POST['uid'];

        $stmt = $conn->prepare("INSERT INTO players (name, age, sex, uid) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $age, $sex, $uid);
        if ($stmt->execute()) {
            echo "<p>Player added successfully!</p>";
        } else {
            echo "<p>Error: {$stmt->error}</p>";
        }
        $stmt->close();
    }

    $result = $conn->query("SELECT * FROM players");
    if ($result->num_rows > 0) {
        echo "<h2>Existing Players</h2><table><tr><th>ID</th><th>Name</th><th>Age</th><th>Sex</th><th>UID</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['age']}</td><td>{$row['sex']}</td><td>{$row['uid']}</td></tr>";
        }
        echo "</table>";
    }
    $conn->close();
    ?>
</body>
</html>
