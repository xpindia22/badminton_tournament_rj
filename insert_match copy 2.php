<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Match</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        form { margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        select, input, button { padding: 10px; margin-bottom: 10px; width: 100%; max-width: 300px; }
        h1, h2 { text-align: center; }
    </style>
    <script>
        // Function to fetch players dynamically based on category
        async function fetchPlayers() {
            const categoryId = document.getElementById('category_id').value;
            const player1Select = document.getElementById('player1_id');
            const player2Select = document.getElementById('player2_id');

            // Clear previous options
            player1Select.innerHTML = '<option value="">Select Player</option>';
            player2Select.innerHTML = '<option value="">Select Player</option>';

            if (categoryId) {
                try {
                    const response = await fetch(`fetch_players.php?category_id=${categoryId}`);
                    const data = await response.json();

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Populate Player 1 and Player 2 dropdowns
                    data.forEach(player => {
                        const option1 = `<option value="${player.id}">${player.name}</option>`;
                        const option2 = `<option value="${player.id}">${player.name}</option>`;
                        player1Select.innerHTML += option1;
                        player2Select.innerHTML += option2;
                    });
                } catch (error) {
                    console.error('Error fetching players:', error);
                }
            }
        }
    </script>
</head>
<body>
    <h1>Insert Match</h1>
    <form method="post">
        <label for="tournament_id">Tournament:</label>
        <select name="tournament_id" id="tournament_id" required>
            <option value="">Select Tournament</option>
            <?php
            $conn = new mysqli("localhost", "root", "xxx", "badminton_tournament");
            $result = $conn->query("SELECT id, name FROM tournaments");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required onchange="fetchPlayers()">
            <option value="">Select Category</option>
            <?php
            $result = $conn->query("SELECT id, name FROM categories");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="player1_id">Player 1:</label>
        <select name="player1_id" id="player1_id" required>
            <option value="">Select Player</option>
        </select>

        <label for="player2_id">Player 2:</label>
        <select name="player2_id" id="player2_id" required>
            <option value="">Select Player</option>
        </select>

        <label for="pool">Pool:</label>
        <select name="pool" id="pool">
            <option value="">None</option>
            <option value="A">A</option>
            <option value="B">B</option>
        </select>

        <label for="stage">Match Stage:</label>
        <select name="stage" id="stage" required>
            <option value="Pre Quarter Finals">Pre Quarter Finals</option>
            <option value="Quarter Finals">Quarter Finals</option>
            <option value="Semi Finals">Semi Finals</option>
            <option value="Finals">Finals</option>
        </select>

        <label for="set1_player1_points">Set 1 Player 1 Points:</label>
        <input type="number" name="set1_player1_points" required>

        <label for="set1_player2_points">Set 1 Player 2 Points:</label>
        <input type="number" name="set1_player2_points" required>

        <label for="set2_player1_points">Set 2 Player 1 Points:</label>
        <input type="number" name="set2_player1_points" required>

        <label for="set2_player2_points">Set 2 Player 2 Points:</label>
        <input type="number" name="set2_player2_points" required>

        <label for="set3_player1_points">Set 3 Player 1 Points:</label>
        <input type="number" name="set3_player1_points">

        <label for="set3_player2_points">Set 3 Player 2 Points:</label>
        <input type="number" name="set3_player2_points">

        <button type="submit">Add Match</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tournament_id = $_POST['tournament_id'];
        $category_id = $_POST['category_id'];
        $player1_id = $_POST['player1_id'];
        $player2_id = $_POST['player2_id'];
        $pool = $_POST['pool'];
        $stage = $_POST['stage'];

        $set1_p1 = $_POST['set1_player1_points'];
        $set1_p2 = $_POST['set1_player2_points'];
        $set2_p1 = $_POST['set2_player1_points'];
        $set2_p2 = $_POST['set2_player2_points'];
        $set3_p1 = $_POST['set3_player1_points'] ?? 0;
        $set3_p2 = $_POST['set3_player2_points'] ?? 0;

        $stmt = $conn->prepare("
            INSERT INTO matches (tournament_id, category_id, player1_id, player2_id, pool, stage, 
            set1_player1_points, set1_player2_points, set2_player1_points, set2_player2_points, 
            set3_player1_points, set3_player2_points)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiissiiiiii",
            $tournament_id, $category_id, $player1_id, $player2_id, $pool, $stage,
            $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2
        );

        if ($stmt->execute()) {
            echo "<p>Match added successfully!</p>";
        } else {
            echo "<p>Error: {$stmt->error}</p>";
        }
        $stmt->close();
    }

    // Fetch and display results
    $result = $conn->query("SELECT m.*, p1.name AS player1_name, p2.name AS player2_name 
                            FROM matches m
                            LEFT JOIN players p1 ON m.player1_id = p1.id
                            LEFT JOIN players p2 ON m.player2_id = p2.id");

    if ($result->num_rows > 0) {
        echo "<h2>Match Results</h2>";
        echo "<table><tr>
                <th>ID</th>
                <th>Player 1</th>
                <th>Player 2</th>
                <th>Set 1</th>
                <th>Set 2</th>
                <th>Set 3</th>
                <th>Winner</th>
                <th>Stage</th>
                <th>Edit</th>
              </tr>";
        while ($row = $result->fetch_assoc()) {
            $p1_total = $row['set1_player1_points'] + $row['set2_player1_points'] + $row['set3_player1_points'];
            $p2_total = $row['set1_player2_points'] + $row['set2_player2_points'] + $row['set3_player2_points'];
            $winner = ($p1_total > $p2_total) ? $row['player1_name'] : ($p1_total < $p2_total ? $row['player2_name'] : 'Draw');
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['player1_name']}</td>
                    <td>{$row['player2_name']}</td>
                    <td>{$row['set1_player1_points']} - {$row['set1_player2_points']}</td>
                    <td>{$row['set2_player1_points']} - {$row['set2_player2_points']}</td>
                    <td>{$row['set3_player1_points']} - {$row['set3_player2_points']}</td>
                    <td>{$winner}</td>
                    <td>{$row['stage']}</td>
                    <td><a href='edit_match.php?id={$row['id']}'>Edit</a></td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No matches recorded yet.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
