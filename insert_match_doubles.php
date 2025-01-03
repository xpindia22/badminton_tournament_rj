<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection and authentication files
require_once "conn.php";
require 'auth.php';
redirect_if_not_logged_in();

try {
    // Ensure database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $message = '';
    $lockedTournament = $_SESSION['locked_tournament'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debug: Show POST data
        echo "<pre>POST Data: ";
        print_r($_POST);
        echo "</pre>";

        // Retrieve form inputs
        $match_type = $_POST['match_type'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $stage = $_POST['stage'] ?? 'Group Stage'; // Default stage
        $match_time = $_POST['match_time'] ?? null;

        if (!$match_time) {
            throw new Exception("Match time is required.");
        }

        // Convert match_time to DATETIME format for MySQL
        $match_time = date('Y-m-d H:i:s', strtotime($match_time));

        // Prepare INSERT query based on match type
        if ($match_type === 'singles') {
            $player1_id = $_POST['player1_id'] ?? null;
            $player2_id = $_POST['player2_id'] ?? null;

            if (!$category_id || !$player1_id || !$player2_id) {
                throw new Exception("All fields are required for a singles match.");
            }

            $stmt = $conn->prepare("INSERT INTO matches (
                tournament_id, category_id, player1_id, player2_id, stage, match_time
            ) VALUES (?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $conn->error);
            }

            $stmt->bind_param("iiiiss", $lockedTournament, $category_id, $player1_id, $player2_id, $stage, $match_time);

            if (!$stmt->execute()) {
                throw new Exception("Error executing query: " . $stmt->error);
            }

            $message = "Singles match added successfully!";
        } elseif ($match_type === 'doubles') {
            $team1_player1_id = $_POST['team1_player1_id'] ?? null;
            $team1_player2_id = $_POST['team1_player2_id'] ?? null;
            $team2_player1_id = $_POST['team2_player1_id'] ?? null;
            $team2_player2_id = $_POST['team2_player2_id'] ?? null;

            if (!$category_id || !$team1_player1_id || !$team1_player2_id || !$team2_player1_id || !$team2_player2_id) {
                throw new Exception("All fields are required for a doubles match.");
            }

            $stmt = $conn->prepare("INSERT INTO matches (
                tournament_id, category_id, team1_player1_id, team1_player2_id,
                team2_player1_id, team2_player2_id, stage, match_time
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $conn->error);
            }

            $stmt->bind_param(
                "iiiiiiss",
                $lockedTournament, $category_id, $team1_player1_id, $team1_player2_id,
                $team2_player1_id, $team2_player2_id, $stage, $match_time
            );

            if (!$stmt->execute()) {
                throw new Exception("Error executing query: " . $stmt->error);
            }

            $message = "Doubles match added successfully!";
        } else {
            throw new Exception("Invalid match type selected.");
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Fetch categories and players for dropdowns
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name, age_group, sex FROM categories");
$players = $conn->query("SELECT id, name FROM players");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }

        .singles-field, .doubles-field {
            display: none;
        }
    </style>
    <script>
        function updateForm() {
            const matchType = document.getElementById('match_type').value;
            document.querySelector('.singles-field').style.display = matchType === 'singles' ? 'block' : 'none';
            document.querySelector('.doubles-field').style.display = matchType === 'doubles' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Insert Match</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="match_type">Match Type:</label>
            <select name="match_type" id="match_type" onchange="updateForm()" required>
                <option value="">Select Match Type</option>
                <option value="singles">Singles</option>
                <option value="doubles">Doubles</option>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select Category</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="stage">Stage:</label>
            <select name="stage" id="stage">
                <option value="Group Stage">Group Stage</option>
                <option value="Quarterfinals">Quarterfinals</option>
                <option value="Semifinals">Semifinals</option>
                <option value="Finals">Finals</option>
            </select>

            <label for="match_time">Match Time:</label>
            <input type="datetime-local" name="match_time" id="match_time" required>

            <!-- Singles Fields -->
            <div class="singles-field">
                <label for="player1_id">Player 1:</label>
                <select name="player1_id" id="player1_id">
                    <option value="">Select Player</option>
                    <?php while ($player = $players->fetch_assoc()): ?>
                        <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="player2_id">Player 2:</label>
                <select name="player2_id" id="player2_id">
                    <option value="">Select Player</option>
                    <?php mysqli_data_seek($players, 0); // Reset players result ?>
                    <?php while ($player = $players->fetch_assoc()): ?>
                        <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Doubles Fields -->
            <div class="doubles-field">
                <label for="team1_player1_id">Team 1 Player 1:</label>
                <select name="team1_player1_id" id="team1_player1_id">
                    <option value="">Select Player</option>
                    <?php mysqli_data_seek($players, 0); // Reset players result ?>
                    <?php while ($player = $players->fetch_assoc()): ?>
                        <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="team1_player2_id">Team 1 Player 2:</label>
                <select name="team1_player2_id" id="team1_player2_id">
                    <option value="">Select Player</option>
                    <?php mysqli_data_seek($players, 0); // Reset players result ?>
                    <?php while ($player = $players->fetch_assoc()): ?>
                        <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="team2_player1_id">Team 2 Player 1:</label>
                <select name="team2_player1_id" id="team2_player1_id">
                    <option value="">Select Player</option>
                    <?php mysqli_data_seek($players, 0); // Reset players result ?>
                    <?php while ($player = $players->fetch_assoc()): ?>
                        <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="team2_player2_id">Team 2 Player 2:</label>
                <select name="team2_player2_id" id="team2_player2_id">
                    <option value="">Select Player</option>
                    <?php mysqli_data_seek($players, 0); // Reset players result ?>
                    <?php while ($player = $players->fetch_assoc()): ?>
                        <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit">Add Match</button>
        </form>
    </div>
</body>
</html>
