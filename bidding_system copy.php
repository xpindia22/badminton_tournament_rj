<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'auth.php'; // Include session and authentication utilities
// require 'permissions.php'; // Include role-based permissions
redirect_if_not_logged_in(); // Ensure user is logged in

require_once 'conn.php';

 

// Handle Bid Placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_bid'])) {
    $player_id = intval($_POST['player_id']);
    $team_id = $_SESSION['user_id']; // Use logged-in user's ID as the team ID
    $bid_amount = floatval($_POST['bid_amount']);

    try {
        // Get the current highest bid for the player
        $stmt = $conn->prepare("SELECT MAX(bid_amount) AS highest_bid FROM bids WHERE player_id = ?");
        $stmt->bind_param("i", $player_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $highest_bid = $result['highest_bid'] ?? 0;

        // Ensure the bid is higher than the current highest bid
        if ($bid_amount > $highest_bid) {
            // Insert the new bid
            $stmt = $conn->prepare("INSERT INTO bids (player_id, team_id, bid_amount) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $player_id, $team_id, $bid_amount);
            $stmt->execute();
            $success = "Bid placed successfully!";
        } else {
            $error = "Your bid must be higher than the current highest bid ($highest_bid).";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Players
$players_query = "SELECT players.id, players.name, players.age, categories.name AS category, players.base_price 
                  FROM players 
                  LEFT JOIN categories ON players.category_id = categories.id";
$players_result = $conn->query($players_query);

$players = $players_result ? $players_result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Bidding System</title>
</head>
<body>
    <h1>Player Bidding System</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! (<a href="logout.php">Logout</a>)</p>

    <?php if (isset($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <h2>Available Players</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Age</th>
        <th>Category</th>
        <th>Base Price</th>
        <th>Highest Bid</th>
        <th>Highest Bidder</th>
        <th>Bid</th>
    </tr>
    <?php foreach ($players as $player): ?>
    <tr>
        <td><?= $player['id'] ?></td>
        <td><?= $player['name'] ?></td>
        <td><?= $player['age'] ?></td>
        <td><?= $player['category'] ?></td>
        <td><?= $player['base_price'] ?></td>
        <td>
            <?php
            $stmt = $conn->prepare("
                SELECT b.bid_amount AS highest_bid, u.username AS highest_bidder 
                FROM bids b 
                JOIN users u ON b.team_id = u.id 
                WHERE b.player_id = ? 
                ORDER BY b.bid_amount DESC 
                LIMIT 1
            ");
            $stmt->bind_param("i", $player['id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result) {
                echo $result['highest_bid'];
            } else {
                echo "No bids yet";
            }
            ?>
        </td>
        <td>
            <?php
            if ($result) {
                echo htmlspecialchars($result['highest_bidder']);
            } else {
                echo "-";
            }
            ?>
        </td>
        <td>
            <form method="POST">
                <input type="hidden" name="player_id" value="<?= $player['id'] ?>">
                <input type="number" name="bid_amount" placeholder="Bid Amount" required>
                <button type="submit" name="place_bid">Place Bid</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
