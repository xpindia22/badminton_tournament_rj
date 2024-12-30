<?php
//insert_match.php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin() && !is_user()) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];
    $stage = $_POST['stage'];
    $set1_p1 = $_POST['set1_player1_points'];
    $set1_p2 = $_POST['set1_player2_points'];
    $set2_p1 = $_POST['set2_player1_points'];
    $set2_p2 = $_POST['set2_player2_points'];
    $set3_p1 = $_POST['set3_player1_points'] ?? 0;
    $set3_p2 = $_POST['set3_player2_points'] ?? 0;
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        INSERT INTO matches (
            tournament_id, category_id, player1_id, player2_id, stage, 
            set1_player1_points, set1_player2_points, set2_player1_points, 
            set2_player2_points, set3_player1_points, set3_player2_points, created_by
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiiiiiiiiiii", 
        $tournament_id, $category_id, $player1_id, $player2_id, $stage,
        $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2, $created_by
    );
    if ($stmt->execute()) {
        echo "<p class='success'>Match added successfully!</p>";
    } else {
        echo "<p class='error'>Error: {$stmt->error}</p>";
    }
    $stmt->close();
}

// Fetch tournaments, categories, and players
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Match</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Fetch players dynamically based on the selected category
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

                    if (data.length === 0) {
                        alert('No players found for the selected category.');
                        return;
                    }

                    data.forEach(player => {
                        const option = `<option value="${player.id}">${player.name}</option>`;
                        player1Select.innerHTML += option;
                        player2Select.innerHTML += option;
                    });
                } catch (error) {
                    console.error('Error fetching players:', error);
                }
            }
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Insert Match</h1>
        <form method="post" class="form-styled">
            <label for="tournament_id">Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <option value="">Select Tournament</option>
                <?php while ($row = $tournaments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required onchange="fetchPlayers()">
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="player1_id">Player 1:</label>
            <select name="player1_id" id="player1_id" required>
                <option value="">Select Player</option>
            </select>

            <label for="player2_id">Player 2:</label>
            <select name="player2_id" id="player2_id" required>
                <option value="">Select Player</option>
            </select>

            <label for="stage">Match Stage:</label>
            <select name="stage" id="stage" required>
                <option value="Pre Quarter Finals">Pre Quarter Finals</option>
                <option value="Quarter Finals">Quarter Finals</option>
                <option value="Semi Finals">Semi Finals</option>
                <option value="Finals">Finals</option>
            </select>

            <label for="set1_player1_points">Set 1 Player 1 Points:</label>
            <input type="number" name="set1_player1_points" value="0" required>

            <label for="set1_player2_points">Set 1 Player 2 Points:</label>
            <input type="number" name="set1_player2_points" value="0" required>

            <label for="set2_player1_points">Set 2 Player 1 Points:</label>
            <input type="number" name="set2_player1_points" value="0" required>

            <label for="set2_player2_points">Set 2 Player 2 Points:</label>
            <input type="number" name="set2_player2_points" value="0" required>

            <label for="set3_player1_points">Set 3 Player 1 Points:</label>
            <input type="number" name="set3_player1_points"value="0" required>

            <label for="set3_player2_points">Set 3 Player 2 Points:</label>
            <input type="number" name="set3_player2_points"value="0" required>

            <button type="submit" class="btn-primary">Add Match</button>
        </form>

        <h2>Existing Matches</h2>
        <?php
        $query = is_admin() 
            ? "SELECT m.*, p1.name AS player1_name, p2.name AS player2_name FROM matches m
               LEFT JOIN players p1 ON m.player1_id = p1.id
               LEFT JOIN players p2 ON m.player2_id = p2.id"
            : "SELECT m.*, p1.name AS player1_name, p2.name AS player2_name FROM matches m
               LEFT JOIN players p1 ON m.player1_id = p1.id
               LEFT JOIN players p2 ON m.player2_id = p2.id
               WHERE m.created_by = {$_SESSION['user_id']}";
        $matches = $conn->query($query);

        if ($matches->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Player 1</th>
                        <th>Player 2</th>
                        <th>Set 1</th>
                        <th>Set 2</th>
                        <th>Set 3</th>
                        <th>Winner</th>
                        <th>Stage</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $matches->fetch_assoc()): ?>
                        <?php
                        $p1_total = $row['set1_player1_points'] + $row['set2_player1_points'] + $row['set3_player1_points'];
                        $p2_total = $row['set1_player2_points'] + $row['set2_player2_points'] + $row['set3_player2_points'];
                        $winner = $p1_total > $p2_total ? $row['player1_name'] : ($p1_total < $p2_total ? $row['player2_name'] : 'Draw');
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['player1_name']) ?></td>
                            <td><?= htmlspecialchars($row['player2_name']) ?></td>
                            <td><?= $row['set1_player1_points'] ?> - <?= $row['set1_player2_points'] ?></td>
                            <td><?= $row['set2_player1_points'] ?> - <?= $row['set2_player2_points'] ?></td>
                            <td><?= $row['set3_player1_points'] ?> - <?= $row['set3_player2_points'] ?></td>
                            <td><?= htmlspecialchars($winner) ?></td>
                            <td><?= htmlspecialchars($row['stage']) ?></td>
                            <td><a href="edit_match.php?id=<?= $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No matches recorded yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
