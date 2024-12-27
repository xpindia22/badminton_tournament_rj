<!-- HTML and PHP: Insert Category -->
<!DOCTYPE html>
<html>
<head>
    <title>Insert Category</title>
</head>
<body>
    <form method="post" action="insert_category.php">
        Category Name: <input type="text" name="name" required><br>
        Age Group: <input type="text" name="age_group" required><br>
        Sex: 
        <select name="sex" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select><br>
        <button type="submit">Add Category</button>
    </form>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age_group = $_POST['age_group'];
    $sex = $_POST['sex'];

    $sql = "INSERT INTO categories (name, age_group, sex) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $age_group, $sex);

    if ($stmt->execute()) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>