<?php
//insert_category.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Category</title>
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
    <h1>Insert Category</h1>
    <form method="post">
        <label for="name">Category Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="age_group">Age Group:</label>
        <input type="text" name="age_group" id="age_group" required>

        <label for="sex">Sex:</label>
        <select name="sex" id="sex" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select>

        <button type="submit">Add Category</button>
    </form>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "xxx";
    $dbname = "badminton_tournament";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $age_group = $_POST['age_group'];
        $sex = $_POST['sex'];

        $stmt = $conn->prepare("INSERT INTO categories (name, age_group, sex) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $age_group, $sex);
        if ($stmt->execute()) {
            echo "<p>Category added successfully!</p>";
        } else {
            echo "<p>Error: {$stmt->error}</p>";
        }
        $stmt->close();
    }

    $result = $conn->query("SELECT * FROM categories");
    if ($result->num_rows > 0) {
        echo "<h2>Existing Categories</h2><table><tr><th>ID</th><th>Name</th><th>Age Group</th><th>Sex</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['age_group']}</td><td>{$row['sex']}</td></tr>";
        }
        echo "</table>";
    }
    $conn->close();
    ?>
</body>
</html>
