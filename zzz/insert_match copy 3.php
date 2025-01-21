<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

<<<<<<< HEAD
require_once 'conn.php';
=======
//require_once 'permissions.php';
// require_once 'conn.php';
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
include 'header.php';
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;
<<<<<<< HEAD
$username = $_SESSION['username'];

// Get logged-in user's ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($loggedInUserId);
$stmt->fetch();
$stmt->close();

// Fetch tournaments where the user is either the **creator** or **moderator**
$tournaments = $conn->prepare("
    SELECT id, name FROM tournaments 
    WHERE created_by = ? OR moderated_by = ?
");
$tournaments->bind_param("ii", $loggedInUserId, $loggedInUserId);
$tournaments->execute();
$tournamentResult = $tournaments->get_result();
$tournaments->close();

// Handle tournament locking and match insertions
=======

>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['lock_tournament'])) {
        $lockedTournament = intval($_POST['tournament_id']);
        $_SESSION['locked_tournament'] = $lockedTournament;

        $stmt = $conn->prepare("SELECT name FROM tournaments WHERE id = ?");
        $stmt->bind_param("i", $lockedTournament);
        $stmt->execute();
        $stmt->bind_result($lockedTournamentName);
        $stmt->fetch();
        $_SESSION['locked_tournament_name'] = $lockedTournamentName;
        $stmt->close();
    } elseif (isset($_POST['unlock_tournament'])) {
        unset($_SESSION['locked_tournament'], $_SESSION['locked_tournament_name']);
        $lockedTournament = null;
    } else {
<<<<<<< HEAD
        // **Match Insertion**
=======
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
        $tournament_id = $lockedTournament ?? $_POST['tournament_id'];
        $category_id = $_POST['category_id'];
        $player1_id = $_POST['player1_id'];
        $player2_id = $_POST['player2_id'];
<<<<<<< HEAD
        $stage = $_POST['stage'] ?? 'Pre Quarter Finals';
        $date = $_POST['date'];
        $match_time = $_POST['time'];

        // Prevent Undefined Array Key Warnings
        $set1_p1 = $_POST['set1_player1_points'] ?? 0;
        $set1_p2 = $_POST['set1_player2_points'] ?? 0;
        $set2_p1 = $_POST['set2_player1_points'] ?? 0;
        $set2_p2 = $_POST['set2_player2_points'] ?? 0;
=======
        $stage = $_POST['stage'];
        $date = $_POST['date'];
        $match_time = $_POST['time'];
        $set1_p1 = $_POST['set1_player1_points'];
        $set1_p2 = $_POST['set1_player2_points'];
        $set2_p1 = $_POST['set2_player1_points'];
        $set2_p2 = $_POST['set2_player2_points'];
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
        $set3_p1 = $_POST['set3_player1_points'] ?? 0;
        $set3_p2 = $_POST['set3_player2_points'] ?? 0;

        $stmt = $conn->prepare("
            INSERT INTO matches (
                tournament_id, category_id, player1_id, player2_id, stage, 
<<<<<<< HEAD
                match_date, match_time, set1_player1_points, set1_player2_points, 
=======
                date, match_time, set1_player1_points, set1_player2_points, 
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
                set2_player1_points, set2_player2_points, set3_player1_points, set3_player2_points
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
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

<<<<<<< HEAD
// Fetch categories for the locked tournament
$categories = [];
=======
// Fetch tournaments
$tournaments = $conn->query("SELECT id, name FROM tournaments");

// Fetch categories for the locked tournament
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex 
        FROM categories c
        INNER JOIN tournament_categories tc ON c.id = tc.category_id
        WHERE tc.tournament_id = ?
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
<<<<<<< HEAD
}

// Fetch players
$players = $conn->query("SELECT id, name FROM players");
=======
} else {
    $categories = $conn->query("SELECT id, name, age_group, sex FROM categories");
}

// Fetch players
$players = $conn->query("SELECT id, name, dob, sex FROM players");
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
    <style>
        body {
            font-family: Arial, sans-serif;
<<<<<<< HEAD
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
=======
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
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
        }

        h1 {
            text-align: center;
            color: #444;
        }

        label {
<<<<<<< HEAD
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }

        select, input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
=======
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
<<<<<<< HEAD
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
=======
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
<<<<<<< HEAD
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
=======
            margin-bottom: 20px;
            color: #28a745;
            font-weight: bold;
        }

        .error {
            color: #dc3545;
        }

        .top-bar {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-link:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        const players = <?= json_encode($players->fetch_all(MYSQLI_ASSOC)) ?>;

        function calculateAge(dob) {
            if (!dob) return "N/A"; // Handle missing or invalid DOB
            const birthDate = new Date(dob);
            if (isNaN(birthDate)) return "N/A"; // Handle invalid date format

            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        function updatePlayerDropdown() {
            const categoryId = document.getElementById('category_id').value;
            const player1Dropdown = document.getElementById('player1_id');
            const player2Dropdown = document.getElementById('player2_id');

            player1Dropdown.innerHTML = '<option value="">Select Player</option>';
            player2Dropdown.innerHTML = '<option value="">Select Player</option>';

            if (categoryId) {
                const category = document.querySelector(`#category_id option[value="${categoryId}"]`);
                const ageGroup = category.dataset.ageGroup;
                const sex = category.dataset.sex;

                players.forEach(player => {
                    const age = calculateAge(player.dob); // Use corrected `dob` field
                    if (isPlayerEligible(player, age, ageGroup, sex)) {
                        const option = `<option value="${player.id}">${player.name} (${age}, ${player.sex})</option>`;
                        player1Dropdown.innerHTML += option;
                        player2Dropdown.innerHTML += option;
                    }
                });
            }
        }

        function isPlayerEligible(player, age, ageGroup, sex) {
            const ageMatch = ageGroup.match(/\d+/g);
            if (!ageMatch) return true; // No age restriction, all players are eligible

            if (ageGroup.includes("Under")) {
                const maxAge = parseInt(ageMatch[0], 10);
                if (age > maxAge) return false; // Exclude players older than maxAge
            } else if (ageGroup.includes("Plus")) {
                const minAge = parseInt(ageMatch[0], 10);
                if (age < 40 || age < minAge) return false; // Exclude players under 40 or under minAge
            }

            if (sex === 'M' && player.sex !== 'M') return false; // Exclude non-male players for male-only categories
            if (sex === 'F' && player.sex !== 'F') return false; // Exclude non-female players for female-only categories
            if (sex === 'Mixed' && (player.sex !== 'M' && player.sex !== 'F')) return false; // Exclude players not fitting mixed criteria

            return true; // If all conditions pass, the player is eligible
        }
    </script>
</head>
<body>
 
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
    <div class="container">
        <h1>Insert Match</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!$lockedTournament): ?>
            <form method="post">
                <label for="tournament_id">Select Tournament:</label>
                <select name="tournament_id" id="tournament_id" required>
                    <option value="">Select Tournament</option>
<<<<<<< HEAD
                    <?php while ($row = $tournamentResult->fetch_assoc()): ?>
=======
                    <?php while ($row = $tournaments->fetch_assoc()): ?>
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="lock_tournament">Lock Tournament</button>
            </form>
        <?php else: ?>
            <form method="post">
<<<<<<< HEAD
                <p>Locked Tournament: <strong><?= htmlspecialchars($_SESSION['locked_tournament_name'] ?? '') ?></strong></p>
                <button type="submit" name="unlock_tournament">Unlock Tournament</button>
            </form>

            <form method="post">
                <label for="category_id">Category:</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="player1_id">Player 1:</label>
                <select name="player1_id" required>
                    <?php while ($row = $players->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="player2_id">Player 2:</label>
                <select name="player2_id" required>
                    <?php $players->data_seek(0); while ($row = $players->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="date">Match Date:</label>
                <input type="date" name="date" required>

                <label for="match_time">Match Time:</label>
                <input type="time" name="time" required>

                <button type="submit">Add Match</button>
            </form>
        <?php endif; ?>
=======
                <p>Locked Tournament: <?= htmlspecialchars($_SESSION['locked_tournament_name'] ?? '') ?></p>
                <button type="submit" name="unlock_tournament">Unlock Tournament</button>
            </form>
        <?php endif; ?>

        <form method="post">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" onchange="updatePlayerDropdown()" required>
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" data-age-group="<?= $row['age_group'] ?>" data-sex="<?= $row['sex'] ?>">
                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>, <?= htmlspecialchars($row['sex']) ?>)
                    </option>
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

            <label for="date">Match Date:</label>
            <input type="date" name="date" required>

            <label for="match_time">Match Time:</label>
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

            <button type="submit">Add Match</button>
        </form>
>>>>>>> 4162827b0e5e015dd9b2e37d7a2c485e4c864b0b
    </div>
</body>
</html>
