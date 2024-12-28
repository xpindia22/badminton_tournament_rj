<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Tournament</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        form { margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        input, button { padding: 10px; margin-bottom: 10px; width: 100%; max-width: 300px; }
    </style>
</head>
<body>
    <h1>Insert Tournament</h1>
    <form method="post">
        <label for="name">Tournament Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="year">Year:</label>
        <input type="number" name="year" id="year" required>

        <button type="submit">Add Tournament</button>
    </form>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "xxx";
    $dbname = "badminton_tournament";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $year = $_POST['year'];

        $stmt = $conn->prepare("INSERT INTO tournaments (name, year) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $year);
        if ($stmt->execute()) {
            echo "<p>Tournament added successfully!</p>";
        } else {
            echo "<p>Error: {$stmt->error}</p>";
        }
        $stmt->close();
    }

    $result = $conn->query("SELECT * FROM tournaments");
    if ($result->num_rows > 0) {
        echo "<h2>Existing Tournaments</h2><table><tr><th>ID</th><th>Name</th><th>Year</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['year']}</td></tr>";
        }
        echo "</table>";
    }
    $conn->close();
    ?>
</body>
</html>
