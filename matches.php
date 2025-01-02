<?php
// matches.php
include 'header.php';
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (is_logged_in()) {
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
}

// Handle form submission for adding a new match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];
    $stage = $_POST['stage'];
    $match_date = $_POST['match_date'];
    $match_time = $_POST['match_time'];
    $created_by = $user_id;

    $insert_query = "
        INSERT INTO matches (tournament_id, category_id, player1_id, player2_id, stage, match_date, match_time, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param('iiiisssi', $tournament_id, $category_id, $player1_id, $player2_id, $stage, $match_date, $match_time, $created_by);

    if ($stmt->execute()) {
        $success_message = "Match added successfully.";
    } else {
        $error_message = "Error adding match: " . $stmt->error;
    }
}

// Fetch matches
$query = "
    SELECT m.id, t.name AS tournament_name, c.name AS category_name, 
           p1.name AS player1_name, p2.name AS player2_name, m.stage,
           m.set1_player1_points, m.set1_player2_points,
           m.set2_player1_points, m.set2_player2_points,
           m.set3_player1_points, m.set3_player2_points,
           m.match_date, m.match_time
    FROM matches m
    LEFT JOIN tournaments t ON m.tournament_id = t.id
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN players p1 ON m.player1_id = p1.id
    LEFT JOIN players p2 ON m.player2_id = p2.id
";

$result = $conn->query($query);

if (!$result) {
    die("<p class='error'>Error fetching matches: " . $conn->error . "</p>");
}

// Fetch tournaments and categories for the form
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches</title>
    <link rel="stylesheet" href="style-table.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
 
    <div class="container">
        <h1>Matches</h1>

        <?php if (isset($success_message)): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <!-- Add Match Form -->
        <form action="matches.php" method="post" class="form-add">
            <h2>Add New Match</h2>
            <label for="tournament_id">Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <?php while ($row = $tournaments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select a Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="player1_id">Player 1:</label>
            <select name="player1_id" id="player1_id" required>
                <option value="">Select a Player</option>
            </select>

            <label for="player2_id">Player 2:</label>
            <select name="player2_id" id="player2_id" required>
                <option value="">Select a Player</option>
            </select>

            <label for="stage">Stage:</label>
            <select name="stage" id="stage" required>
                <option value="Quarterfinals">Quarterfinals</option>
                <option value="Semifinals">Semifinals</option>
                <option value="Final">Final</option>
            </select>

            <label for="match_date">Date:</label>
            <input type="date" name="match_date" id="match_date" required>

            <label for="match_time">Time:</label>
            <input type="time" name="match_time" id="match_time" required>

            <button type="submit" class="btn-submit">Add Match</button>
        </form>

        <!-- Match List -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tournament</th>
                    <th>Category</th>
                    <th>Player 1</th>
                    <th>Player 2</th>
                    <th>Stage</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Set 1</th>
                    <th>Set 2</th>
                    <th>Set 3</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['tournament_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['player1_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['player2_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['stage'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($row['match_date'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars(date('g:i A', strtotime($row['match_time'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars($row['set1_player1_points'] ?? '0') ?> - <?= htmlspecialchars($row['set1_player2_points'] ?? '0') ?></td>
                        <td><?= htmlspecialchars($row['set2_player1_points'] ?? '0') ?> - <?= htmlspecialchars($row['set2_player2_points'] ?? '0') ?></td>
                        <td><?= htmlspecialchars($row['set3_player1_points'] ?? '0') ?> - <?= htmlspecialchars($row['set3_player2_points'] ?? '0') ?></td>
                        <td>
                            <a href="edit_match.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-edit">Edit</a>
                            <a href="delete_match.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this match?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            $('#category_id').on('change', function () {
                const categoryId = $(this).val();

                $('#player1_id, #player2_id').empty().append('<option value="">Select a Player</option>');

                if (categoryId) {
                    $.ajax({
                        url: 'fetch_players.php',
                        type: 'GET',
                        data: { category_id: categoryId },
                        success: function (response) {
                            try {
                                const players = JSON.parse(response);
                                players.forEach(player => {
                                    const option = `<option value="${player.id}">${player.name}</option>`;
                                    $('#player1_id, #player2_id').append(option);
                                });
                            } catch (error) {
                                console.error('Error parsing JSON:', error);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', xhr.responseText, status, error);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
