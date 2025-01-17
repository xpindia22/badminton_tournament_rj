<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'auth.php'; // Include session and authentication utilities
redirect_if_not_logged_in(); // Ensure user is logged in

require_once 'conn.php';

// Handle Set Base Price for All Players
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_all_base_prices'])) {
    $base_price = floatval($_POST['base_price']);
    try {
        $stmt = $conn->prepare("UPDATE players SET base_price = ?");
        $stmt->bind_param("d", $base_price);
        $stmt->execute();
        $success = "Base price updated for all players!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Set Base Price for Single Player
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_single_base_price'])) {
    $player_id = intval($_POST['player_id']);
    $base_price = floatval($_POST['base_price']);
    try {
        $stmt = $conn->prepare("UPDATE players SET base_price = ? WHERE id = ?");
        $stmt->bind_param("di", $base_price, $player_id);
        $stmt->execute();
        $success = "Base price updated for player!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Set Max Bid Price for All Players
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_all_max_bid_prices'])) {
    $max_bid_price = floatval($_POST['max_bid_price']);
    try {
        $stmt = $conn->prepare("UPDATE players SET max_bid_price = ?");
        $stmt->bind_param("d", $max_bid_price);
        $stmt->execute();
        $success = "Max bid price updated for all players!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Set Max Bid Price for Single Player
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_single_max_bid_price'])) {
    $player_id = intval($_POST['player_id']);
    $max_bid_price = floatval($_POST['max_bid_price']);
    try {
        $stmt = $conn->prepare("UPDATE players SET max_bid_price = ? WHERE id = ?");
        $stmt->bind_param("di", $max_bid_price, $player_id);
        $stmt->execute();
        $success = "Max bid price updated for player!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Bid Placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_bid'])) {
    $player_id = intval($_POST['player_id']);
    $team_id = $_SESSION['user_id']; // Use logged-in user's ID as the team ID
    $bid_amount = floatval($_POST['bid_amount']);

    try {
        // Get the current highest bid and max bid price for the player
        $stmt = $conn->prepare("SELECT MAX(bid_amount) AS highest_bid, max_bid_price FROM players 
                                LEFT JOIN bids ON players.id = bids.player_id 
                                WHERE players.id = ?");
        $stmt->bind_param("i", $player_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $highest_bid = $result['highest_bid'] ?? 0;
        $max_bid_price = $result['max_bid_price'];

        // Check if bid is higher than the current highest bid and within the max bid price
        if ($bid_amount > $highest_bid) {
            if ($max_bid_price === null || $bid_amount <= $max_bid_price) {
                // Insert the new bid
                $stmt = $conn->prepare("INSERT INTO bids (player_id, team_id, bid_amount) VALUES (?, ?, ?)");
                $stmt->bind_param("iid", $player_id, $team_id, $bid_amount);
                $stmt->execute();
                $success = "Bid placed successfully!";
            } else {
                $error = "Your bid exceeds the maximum allowed bid price ($max_bid_price).";
            }
        } else {
            $error = "Your bid must be higher than the current highest bid ($highest_bid).";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Players with Highest Bid and Bidder
$players_query = "
    SELECT 
        players.id, players.name, players.age, players.base_price, players.max_bid_price, 
        categories.name AS category, 
        (SELECT MAX(bid_amount) FROM bids WHERE bids.player_id = players.id) AS highest_bid,
        (SELECT u.username FROM bids b JOIN users u ON b.team_id = u.id WHERE b.player_id = players.id ORDER BY b.bid_amount DESC LIMIT 1) AS highest_bidder
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
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .top-right {
            position: fixed;
            top: 10px;
            right: 10px;
            text-align: right;
        }
        .live-clock {
            color: red;
            font-size: 18px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const nowDisplay = document.getElementById("now");

            function updateClock() {
                const now = new Date();
                nowDisplay.textContent = now.toLocaleString();
            }

            setInterval(() => {
                updateClock();
            }, 1000);
        });
    </script>
</head>
<body>
    <div class="top-right">
        <div class="live-clock">Live Clock: <span id="now"></span></div>
    </div>

    <h1>Player Bidding System</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! (<a href="logout.php">Logout</a>)</p>

    <?php if (isset($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <h2>Admin: Set Base Price for All Players</h2>
    <form method="POST">
        <label for="base_price_all">Base Price:</label>
        <input type="number" name="base_price" id="base_price_all" step="0.01" required>
        <button type="submit" name="set_all_base_prices">Set Base Price for All Players</button>
    </form>

    <h2>Admin: Set Base Price for a Single Player</h2>
    <form method="POST">
        <label for="player_id">Player:</label>
        <select name="player_id" id="player_id" required>
            <?php foreach ($players as $player): ?>
                <option value="<?= $player['id'] ?>"><?= $player['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <label for="base_price">Base Price:</label>
        <input type="number" name="base_price" id="base_price" step="0.01" required>
        <button type="submit" name="set_single_base_price">Set Base Price</button>
    </form>

    <h2>Admin: Set Max Bid Price for All Players</h2>
    <form method="POST">
        <label for="max_bid_price_all">Max Bid Price:</label>
        <input type="number" name="max_bid_price" id="max_bid_price_all" step="0.01" required>
        <button type="submit" name="set_all_max_bid_prices">Set Max Bid Price for All Players</button>
    </form>

    <h2>Admin: Set Max Bid Price for a Single Player</h2>
    <form method="POST">
        <label for="player_id">Player:</label>
        <select name="player_id" id="player_id" required>
            <?php foreach ($players as $player): ?>
                <option value="<?= $player['id'] ?>"><?= $player['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <label for="max_bid_price">Max Bid Price:</label>
        <input type="number" name="max_bid_price" id="max_bid_price" step="0.01" required>
        <button type="submit" name="set_single_max_bid_price">Set Max Bid Price</button>
    </form>

    <h2>Available Players</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Category</th>
            <th>Base Price</th>
            <th>Max Bid Price</th>
            <th>Highest Bid</th>
            <th>Bidder</th>
            <th>Bid</th>
        </tr>
        <?php foreach ($players as $player): ?>
        <tr>
            <td><?= $player['id'] ?></td>
            <td><?= $player['name'] ?></td>
            <td><?= $player['age'] ?></td>
            <td><?= $player['category'] ?></td>
            <td><?= $player['base_price'] ?></td>
            <td><?= $player['max_bid_price'] ?? '-' ?></td>
            <td><?= $player['highest_bid'] ?? 'No bids yet' ?></td>
            <td><?= $player['highest_bidder'] ?? '-' ?></td>
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
