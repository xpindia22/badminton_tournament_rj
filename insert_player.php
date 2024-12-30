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

        function populateForm(player) {
            document.getElementById('player_id').value = player.id;
            document.getElementById('name').value = player.name;
            document.getElementById('dob').value = player.dob;
            document.getElementById('age').value = player.age;
            document.getElementById('sex').value = player.sex;
            document.getElementById('uid').value = player.uid;

            // Scroll to the form for better user experience
            document.getElementById('player-form').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="container">
        <h1>Insert Player</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Player Form -->
        <form method="post" class="form-styled" id="player-form">
            <input type="hidden" name="player_id" id="player_id">
            <label for="name">Player Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" onchange="calculateAge()" required>

            <label for="age">Age:</label>
            <input type="number" name="age" id="age" readonly required>

            <label for="sex">Sex:</label>
            <select name="sex" id="sex" required>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>

            <label for="uid">Unique ID:</label>
            <input type="text" name="uid" id="uid" required>

            <button type="submit" class="btn-primary">Save Player</button>
        </form>

        <!-- Players Table -->
        <h2>All Players</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>UID</th>
                    <th>Linked Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $players->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['dob']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td><?= htmlspecialchars($row['sex']) ?></td>
                        <td><?= htmlspecialchars($row['uid']) ?></td>
                        <td><?= htmlspecialchars($row['linked_users'] ?: 'No users linked') ?></td>
                        <td>
                            <button type="button" onclick='populateForm(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="player_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this player?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
