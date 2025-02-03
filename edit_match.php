
<?php
// edit_match.php
include 'header.php';
////require_once 'permissions.php';

require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();
require_non_player();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (is_logged_in()) {
    $username = $_SESSION['username'];
}

// Validate match ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p class='error'>Invalid match ID.</p>");
}

$match_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Verify ownership or admin access
$stmt = $conn->prepare("SELECT created_by FROM matches WHERE id = ?");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$stmt->bind_result($created_by);
if (!$stmt->fetch()) {
    die("<p class='error'>Match not found.</p>");
}
$stmt->close();

if ($created_by !== $user_id && !$is_admin) {
    die("<p class='error'>Access denied: You can only edit your own matches.</p>");
}

// Fetch match details
$stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();
$match = $result->fetch_assoc();
$stmt->close();

if (!$match) {
    die("<p class='error'>Match not found.</p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = intval($_POST['tournament_id']);
    $category_id = intval($_POST['category_id']);
    $player1_id = intval($_POST['player1_id']);
    $player2_id = intval($_POST['player2_id']);
    $stage = $_POST['stage'];
    $match_date = $_POST['match_date'];
    $match_time = $_POST['match_time'];
    $set1_p1 = intval($_POST['set1_player1_points']);
    $set1_p2 = intval($_POST['set1_player2_points']);
    $set2_p1 = intval($_POST['set2_player1_points']);
    $set2_p2 = intval($_POST['set2_player2_points']);
    $set3_p1 = isset($_POST['set3_player1_points']) ? intval($_POST['set3_player1_points']) : null;
    $set3_p2 = isset($_POST['set3_player2_points']) ? intval($_POST['set3_player2_points']) : null;

    // Validate inputs
    if (empty($match_date) || empty($match_time)) {
        die("Date and Time are required.");
    }

    // Debug values before saving
    echo "Debug: Match Date - $match_date, Match Time - $match_time<br>";

    // Update match in the database
    $stmt = $conn->prepare("UPDATE matches 
        SET tournament_id = ?, category_id = ?, player1_id = ?, player2_id = ?, stage = ?, 
            match_date = ?, match_time = ?, set1_player1_points = ?, set1_player2_points = ?, 
            set2_player1_points = ?, set2_player2_points = ?, set3_player1_points = IFNULL(?, set3_player1_points), 
            set3_player2_points = IFNULL(?, set3_player2_points)
        WHERE id = ?");
    $stmt->bind_param(
        "iiiisssiiiiiii", 
        $tournament_id, $category_id, $player1_id, $player2_id, $stage,
        $match_date, $match_time, $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2, $match_id
    );

    if ($stmt->execute()) {
        $success_message = "Match updated successfully!";
    } else {
        die("<p class='error'>Error updating match: " . $stmt->error . "</p>");
    }
    $stmt->close();

    // Debug saved data
    $query = $conn->prepare("SELECT match_date, match_time FROM matches WHERE id = ?");
    $query->bind_param("i", $match_id);
    $query->execute();
    $query->bind_result($saved_date, $saved_time);
    $query->fetch();
    echo "Saved Date (from database): $saved_date<br>";
    echo "Saved Time (from database): $saved_time<br>";
    $query->close();
}

// Fetch data for dropdowns
$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name FROM categories");
$players = $conn->query("SELECT id, name FROM players");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Match</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
 

    <div class="container">
        <h1>Edit Match</h1>
        <?php if (isset($success_message)): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <form method="post" class="form-styled">
            <label for="tournament_id">Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <?php while ($row = $tournaments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['tournament_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="player1_id">Player 1:</label>
            <select name="player1_id" id="player1_id" required>
                <?php while ($row = $players->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['player1_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="player2_id">Player 2:</label>
            <select name="player2_id" id="player2_id" required>
                <?php mysqli_data_seek($players, 0); ?>
                <?php while ($row = $players->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $match['player2_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="stage">Match Stage:</label>
            <select name="stage" id="stage" required>
                <option value="Pre Quarter Finals" <?= $match['stage'] == 'Pre Quarter Finals' ? 'selected' : '' ?>>Pre Quarter Finals</option>
                <option value="Quarter Finals" <?= $match['stage'] == 'Quarter Finals' ? 'selected' : '' ?>>Quarter Finals</option>
                <option value="Semi Finals" <?= $match['stage'] == 'Semi Finals' ? 'selected' : '' ?>>Semi Finals</option>
                <option value="Finals" <?= $match['stage'] == 'Finals' ? 'selected' : '' ?>>Finals</option>
            </select>

            <label for="match_date">Date:</label>
            <input type="date" name="match_date" id="match_date" value="<?= htmlspecialchars($match['match_date'] ?? '') ?>" required>

            <label for="match_time">Time:</label>
            <input type="text" name="match_time" id="match_time" placeholder="Enter time (e.g., 10:30 AM)" value="<?= htmlspecialchars($match['match_time'] ?? '') ?>" required>

            <label for="set1_player1_points">Set 1 Player 1 Points:</label>
            <input type="number" name="set1_player1_points" id="set1_player1_points" value="<?= $match['set1_player1_points'] ?>" required>

            <label for="set1_player2_points">Set 1 Player 2 Points:</label>
            <input type="number" name="set1_player2_points" id="set1_player2_points" value="<?= $match['set1_player2_points'] ?>" required>

            <label for="set2_player1_points">Set 2 Player 1 Points:</label>
            <input type="number" name="set2_player1_points" id="set2_player1_points" value="<?= $match['set2_player1_points'] ?>" required>

            <label for="set2_player2_points">Set 2 Player 2 Points:</label>
            <input type="number" name="set2_player2_points" id="set2_player2_points" value="<?= $match['set2_player2_points'] ?>" required>

            <label for="set3_player1_points">Set 3 Player 1 Points:</label>
            <input type="number" name="set3_player1_points" id="set3_player1_points" value="<?= $match['set3_player1_points'] ?>">

            <label for="set3_player2_points">Set 3 Player 2 Points:</label>
            <input type="number" name="set3_player2_points" id="set3_player2_points" value="<?= $match['set3_player2_points'] ?>">

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</body>

</html>
