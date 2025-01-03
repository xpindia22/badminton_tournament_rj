<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'header.php';
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
        $player1_partner_id = $_POST['player1_partner_id'] ?? null;
        $player2_id = $_POST['player2_id'];
        $player2_partner_id = $_POST['player2_partner_id'] ?? null;
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
                tournament_id, category_id, player1_id, player1_partner_id, 
                player2_id, player2_partner_id, stage, date, match_time, 
                set1_player1_points, set1_player2_points, set2_player1_points, 
                set2_player2_points, set3_player1_points, set3_player2_points
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiiisssiiiiiii",
            $tournament_id, $category_id, $player1_id, $player1_partner_id,
            $player2_id, $player2_partner_id, $stage, $date, $match_time,
            $set1_p1, $set1_p2, $set2_p1, $set2_p2, $set3_p1, $set3_p2
        );

        if ($stmt->execute()) {
            $message = "Match added successfully!";
        } else {
            $message = "Error adding match: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch tournaments
$tournaments = $conn->query("SELECT id, name FROM tournaments");

// Fetch categories for the locked tournament
if ($lockedTournament) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.age_group, c.sex, c.type 
        FROM categories c
        INNER JOIN tournament_categories tc ON c.id = tc.category_id
        WHERE tc.tournament_id = ?
    ");
    $stmt->bind_param("i", $lockedTournament);
    $stmt->execute();
    $categories = $stmt->get_result();
    $stmt->close();
} else {
    $categories = $conn->query("SELECT id, name, age_group, sex, type FROM categories");
}

// Fetch players
$players = $conn->query("SELECT id, name, dob, sex FROM players");
$playersData = $players ? $players->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Match</title>
    <style>
        .player-group {
            display: none;
        }
    </style>
    <script>
        const players = <?= json_encode($playersData) ?>;

        function calculateAge(dob) {
            if (!dob) return "N/A";
            const birthDate = new Date(dob);
            if (isNaN(birthDate)) return "N/A";

            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        function isPlayerEligible(player, age, ageGroup, sex) {
            const ageMatch = ageGroup.match(/\d+/g);
            if (!ageMatch) return true;

            if (ageGroup.includes("Under")) {
                const maxAge = parseInt(ageMatch[0], 10);
                if (age > maxAge) return false;
            } else if (ageGroup.includes("Plus")) {
                const minAge = parseInt(ageMatch[0], 10);
                if (age < minAge) return false;
            }

            if (sex === 'M' && player.sex !== 'M') return false;
            if (sex === 'F' && player.sex !== 'F') return false;
            if (sex === 'Mixed' && (player.sex !== 'M' && player.sex !== 'F')) return false;

            return true;
        }

        function updatePlayerDropdown() {
            const categoryId = document.getElementById('category_id').value;
            const playerDropdowns = document.querySelectorAll('.player-dropdown');
            const playerGroup = document.querySelectorAll('.player-group');

            playerDropdowns.forEach(dropdown => {
                dropdown.innerHTML = '<option value="">Select Player</option>';
            });

            if (categoryId) {
                const category = document.querySelector(`#category_id option[value="${categoryId}"]`);
                const ageGroup = category.dataset.ageGroup;
                const sex = category.dataset.sex;
                const isDoubles = category.dataset.type === "doubles";

                playerGroup.forEach(group => {
                    group.style.display = isDoubles ? 'block' : 'none';
                });

                players.forEach(player => {
                    const age = calculateAge(player.dob);
                    if (isPlayerEligible(player, age, ageGroup, sex)) {
                        const option = `<option value="${player.id}">${player.name} (${age}, ${player.sex})</option>`;
                        playerDropdowns.forEach(dropdown => dropdown.innerHTML += option);
                    }
                });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Insert Match</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" onchange="updatePlayerDropdown()" required>
                <option value="">Select Category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" 
                            data-age-group="<?= $row['age_group'] ?>" 
                            data-sex="<?= $row['sex'] ?>" 
                            data-type="<?= htmlspecialchars($row['type']) ?>"> 
                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['age_group']) ?>, <?= htmlspecialchars($row['sex']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="player-group">
                <label for="player1_id">Player 1:</label>
                <select name="player1_id" id="player1_id" class="player-dropdown" required></select>

                <label for="player1_partner_id">Player 1 Partner:</label>
                <select name="player1_partner_id" id="player1_partner_id" class="player-dropdown" required></select>
            </div>

            <div class="player-group">
                <label for="player2_id">Player 2:</label>
                <select name="player2_id" id="player2_id" class="player-dropdown" required></select>

                <label for="player2_partner_id">Player 2 Partner:</label>
                <select name="player2_partner_id" id="player2_partner_id" class="player-dropdown" required></select>
            </div>

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

            <button type="submit">Add Match</button>
        </form>
    </div>
</body>
</html>
