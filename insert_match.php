<?php
require 'auth.php';
redirect_if_not_logged_in();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
$lockedTournament = $_SESSION['locked_tournament'] ?? null;

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
        $tournament_id = $lockedTournament ?? $_POST['tournament_id'];
        $category_id = $_POST['category_id'];
        $player1_id = $_POST['player1_id'];
        $player2_id = $_POST['player2_id'];
        $stage = $_POST['stage'];
        $date = $_POST['date'];
        $match_time = $_POST['time'];
        $set1_p1 = $_POST['set1_player1_points'];
        $set1_p2 = $_POST['set1_player2_points'];
        $set2_p1 = $_POST['set2_player1_points'];
        $set2_p2 = $_POST['set2_player2_points'];
        $set3_p1 = $_POST['set3_player1_points'] ?? 0;
        $set3_p2 = $_POST['set3_player2_points'] ?? 0;

        $stmt = $conn->prepare("
            INSERT INTO matches (
                tournament_id, category_id, player1_id, player2_id, stage, 
                date, match_time, set1_player1_points, set1_player2_points, 
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

$tournaments = $conn->query("SELECT id, name FROM tournaments");
$categories = $conn->query("SELECT id, name, age_group, sex FROM categories");
$players = $conn->query("SELECT id, name, age, sex FROM players");
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
            color: #444;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
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
                const categoryName = category.textContent.trim();

                players.forEach(player => {
                    if (
                        (sex === 'Any' || player.sex === sex) && // Match player's sex
                        isPlayerEligible(player.age, ageGroup, categoryName) // Validate age criteria
                    ) {
                        const option = `<option value="${player.id}">${player.name}</option>`;
                        player1Dropdown.innerHTML += option;
                        player2Dropdown.innerHTML += option;
                    }
                });
            }
        }

        function isPlayerEligible(playerAge, ageGroup, categoryName) {
            // For Senior categories, enforce dynamic minimum age and allow older players in younger categories
            if (categoryName.startsWith('Senior')) {
                const minAgeMatch = ageGroup.match(/\d+/); // Extract minimum age (e.g., "50+" for "Senior 50+")
                if (minAgeMatch) {
                    const minAge = parseInt(minAgeMatch[0], 10);
                    return playerAge >= minAge; // Player is eligible if their age >= minAge
                }
                return false; // Disallow if no valid minimum age is found
            }

            // For other categories, use standard age range validation
            const ageRange = ageGroup.match(/\d+/g); // Extract age range (e.g., "15-19")
            if (!ageRange) return true; // No restriction if no valid range is defined

            const [minAge, maxAge] = ageRange.length === 2 ? ageRange : [0, ageRange[0]];
            return playerAge >= parseInt(minAge, 10) && playerAge <= parseInt(maxAge, 10);
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
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!$lockedTournament): ?>
            <form method="post">
                <label for="tournament_id">Select Tournament:</label>
                <select name="tournament_id" id="tournament_id" required>
                    <option value="">Select Tournament</option>
                    <?php while ($row = $tournaments->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="lock_tournament">Lock Tournament</button>
            </form>
        <?php else: ?>
            <form method="post">
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
    </div>
</body>
</html>
