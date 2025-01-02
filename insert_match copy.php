<?php include 'header.php'; ?>
<!-- Rest of your page content -->

<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'auth.php';
require 'conn.php'; // Database connection
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Initialize variables
$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;

// Handle AJAX request for fetching categories
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']); // Sanitize input

    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM tournament_categories tc
        INNER JOIN categories c ON tc.category_id = c.id
        WHERE tc.tournament_id = ?
    ");
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);

    // Debugging output
    header('Content-Type: application/json');
    if (empty($categories)) {
        echo json_encode(['error' => 'No categories found']);
    } else {
        echo json_encode($categories);
    }
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['lock_tournament'])) {
        // Lock tournament
        $tournament_id = intval($_POST['tournament_id']);
        if ($tournament_id > 0) {
            $lockedTournament = $tournament_id;
            $_SESSION['locked_tournament'] = $lockedTournament;

            $stmt = $conn->prepare("SELECT name FROM tournaments WHERE id = ?");
            $stmt->bind_param("i", $lockedTournament);
            $stmt->execute();
            $stmt->bind_result($lockedTournamentName);
            $stmt->fetch();
            $_SESSION['locked_tournament_name'] = $lockedTournamentName;
            $stmt->close();

            $message = "Tournament locked successfully.";
        } else {
            $message = "Please select a tournament to lock.";
        }
    } elseif (isset($_POST['unlock_tournament'])) {
        // Unlock tournament
        unset($_SESSION['locked_tournament'], $_SESSION['locked_tournament_name']);
        $lockedTournament = null;
        $message = "Tournament unlocked.";
    } elseif (isset($_POST['add_match'])) {
        // Add match logic
        $tournament_id = $lockedTournament ?? ($_POST['tournament_id'] ?? null);

        if (empty($tournament_id)) {
            die("Error: No tournament selected.");
        }

        $category_id = intval($_POST['category_id'] ?? 0);
        $player1_id = intval($_POST['player1_id'] ?? 0);
        $player2_id = intval($_POST['player2_id'] ?? 0);
        $stage = trim($_POST['stage'] ?? '');
        $date = $_POST['date'] ?? null;
        $match_time = $_POST['time'] ?? null;
        $set1_p1 = intval($_POST['set1_player1_points'] ?? 0);
        $set1_p2 = intval($_POST['set1_player2_points'] ?? 0);
        $set2_p1 = intval($_POST['set2_player1_points'] ?? 0);
        $set2_p2 = intval($_POST['set2_player2_points'] ?? 0);
        $set3_p1 = intval($_POST['set3_player1_points'] ?? 0);
        $set3_p2 = intval($_POST['set3_player2_points'] ?? 0);

        if (empty($category_id) || empty($player1_id) || empty($player2_id) || empty($date) || empty($match_time)) {
            die("Error: Please fill in all required fields.");
        }

        $stmt = $conn->prepare("
            INSERT INTO matches (
                tournament_id, category_id, player1_id, player2_id, stage, 
                date, match_time, set1_player1_points, set1_player2_points, 
                set2_player1_points, set2_player2_points, set3_player1_points, set3_player2_points
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            die("Error preparing query: " . $conn->error);
        }

        $stmt->bind_param(
            "iiiisssiiiiii",
            $tournament_id, $category_id, $player1_id, $player2_id, $stage,
            $date, $match_time, $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2
        );

        if ($stmt->execute()) {
            $message = "Match added successfully!";
        } else {
            $message = "Error adding match: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch tournaments and players for dropdowns
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$players = $conn->query("SELECT id, name, age, sex FROM players");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 20px;
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
            font-weight: bold;
            margin-bottom: 20px;
            color: green;
        }

        .error {
            color: red;
        }
    </style>
    <script>
        function updateCategories() {
            const tournamentId = <?= json_encode($lockedTournament ?? null); ?> || document.getElementById('tournament_id').value;
            const categoryDropdown = document.getElementById('category_id');
            categoryDropdown.innerHTML = '<option value="">Select Category</option>';

            if (tournamentId) {
                fetch(`insert_match.php?tournament_id=${tournamentId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            categoryDropdown.innerHTML = '<option value="">No categories found</option>';
                        } else {
                            data.forEach(category => {
                                const option = document.createElement('option');
                                option.value = category.id;
                                option.textContent = `${category.name} (${category.age_group}, ${category.sex})`;
                                categoryDropdown.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching categories:', error);
                        categoryDropdown.innerHTML = '<option value="">Error loading categories</option>';
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateCategories(); // Auto-update categories on page load if tournament is locked
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Insert Match</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Lock/Unlock Tournament -->
        <?php if (!$lockedTournament): ?>
            <form method="post">
                <label for="tournament_id">Select Tournament:</label>
                <select name="tournament_id" id="tournament_id" onchange="updateCategories()" required>
                    <option value="">Select Tournament</option>
                    <?php while ($row = $tournaments->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="lock_tournament">Lock Tournament</button>
            </form>
        <?php else: ?>
            <form method="post">
                <p><strong>Locked Tournament:</strong> <?= htmlspecialchars($_SESSION['locked_tournament_name']) ?></p>
                <button type="submit" name="unlock_tournament">Unlock Tournament</button>
            </form>
        <?php endif; ?>

        <!-- Add Match -->
        <form method="post">
            <input type="hidden" name="tournament_id" value="<?= $lockedTournament ?>">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select Category</option>
            </select>

            <label for="player1_id">Player 1:</label>
            <select name="player1_id" required>
                <option value="">Select Player</option>
                <?php while ($player = $players->fetch_assoc()): ?>
                    <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="player2_id">Player 2:</label>
            <select name="player2_id" required>
                <option value="">Select Player</option>
                <?php $players->data_seek(0); while ($player = $players->fetch_assoc()): ?>
                    <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="stage">Match Stage:</label>
            <select name="stage" required>
                <option value="Pre Quarter Finals">Pre Quarter Finals</option>
                <option value="Quarter Finals">Quarter Finals</option>
                <option value="Semi Finals">Semi Finals</option>
                <option value="Finals">Finals</option>
            </select>

            <label for="date">Match Date:</label>
            <input type="date" name="date" required>

            <label for="time">Match Time:</label>
            <input type="time" name="time" required>

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

            <button type="submit" name="add_match">Add Match</button>
        </form>
    </div>
</body>
</html>
