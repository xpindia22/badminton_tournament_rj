<?php
// insert_player.php
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin() && !is_user()) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO players (name, dob, age, sex, uid, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("<p class='error'>Database error: " . $conn->error . "</p>");
    }
    $stmt->bind_param("ssissi", $name, $dob, $age, $sex, $uid, $created_by);
    if ($stmt->execute()) {
        echo "<p class='success'>Player added successfully!</p>";
    } else {
        echo "<p class='error'>Error: {$stmt->error}</p>";
    }
    $stmt->close();
}

// Fetch players
$players_query = is_admin()
    ? "SELECT p.*, u.username AS created_by_username FROM players p LEFT JOIN users u ON p.created_by = u.id"
    : "SELECT * FROM players WHERE created_by = " . intval($_SESSION['user_id']);
$result = $conn->query($players_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Player</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function calculateAge() {
            const dob = document.getElementById('dob').value;
            const ageField = document.getElementById('age');
            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageField.value = age;
            }
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 95%;
            margin: auto;
        }
        .form-styled {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .btn-delete, .btn-edit {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .btn-edit {
            background-color: #4caf50;
        }
        .btn-delete:hover, .btn-edit:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Insert Player</h1>
        <form method="post" class="form-styled">
            <input type="hidden" name="action" value="add">
            <label for="name">Player Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" onchange="calculateAge()" required>

            <label for="age">Age:</label>
            <input type="number" name="age" id="age" required readonly>

            <label for="sex">Sex:</label>
            <select name="sex" id="sex" required>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>

            <label for="uid">Unique ID:</label>
            <input type="text" name="uid" id="uid" required>

            <button type="submit" class="btn-primary">Add Player</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <h2><?= is_admin() ? "All Players" : "Your Players" ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>UID</th>
                        <?php if (is_admin()): ?>
                            <th>Created By</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['dob']) ?></td>
                            <td><?= htmlspecialchars($row['age']) ?></td>
                            <td><?= htmlspecialchars($row['sex']) ?></td>
                            <td><?= htmlspecialchars($row['uid']) ?></td>
                            <?php if (is_admin()): ?>
                                <td><?= htmlspecialchars($row['created_by_username'] ?? 'N/A') ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="edit_player.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                <?php if ($row['created_by'] === $_SESSION['user_id'] || is_admin()): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="player_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this player?')">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No players found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
