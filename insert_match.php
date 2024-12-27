<!-- HTML and PHP: Insert Match -->
<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
</head>
<body>
    <form method="post" action="insert_match.php">
        Tournament ID: <input type="number" name="tournament_id" required><br>
        Category ID: <input type="number" name="category_id" required><br>
        Pool: 
        <select name="pool" required>
            <option value="A">Pool A</option>
            <option value="B">Pool B</option>
        </select><br>
        Player 1 ID: <input type="number" name="player1_id" required><br>
        Player 2 ID: <input type="number" name="player2_id" required><br>
        <button type="submit">Add Match</button>
    </form>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $pool = $_POST['pool'];
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];

    $sql = "INSERT INTO matches (tournament_id, category_id, pool, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisii", $tournament_id, $category_id, $pool, $player1_id, $player2_id);

    if ($stmt->execute()) {
        echo "Match added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>