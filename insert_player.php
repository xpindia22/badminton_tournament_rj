<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Player</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        form { margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        input, select, button { padding: 10px; margin-bottom: 10px; width: 100%; max-width: 300px; }
    </style>
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
</head>
<body>
    <h1>Insert Player</h1>
    <form method="post">
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

        <button type="submit">Add Player</button>
    </form>

    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $servername = "localhost";
    $username = "root";
    $password = "xxx";
    $dbname = "badminton_tournament";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $sex = $_POST['sex'];
        $uid = $_POST['uid'];

        // Calculate age dynamically
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;

        $stmt = $conn->prepare("INSERT INTO players (name, dob, age, sex, uid) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssiss", $name, $dob, $age, $sex, $uid);
            if ($stmt->execute()) {
                echo "<p>Player added successfully!</p>";
            } else {
                echo "<p>Error: {$stmt->error}</p>";
            }
            $stmt->close();
        } else {
            echo "<p>Error preparing statement: {$conn->error}</p>";
        }
    }

    $result = $conn->query("SELECT * FROM players");
    if ($result->num_rows > 0) {
        echo "<h2>Existing Players</h2><table><tr><th>ID</th><th>Name</th><th>Date of Birth</th><th>Age</th><th>Sex</th><th>UID</th><th>Actions</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['dob']}</td>
                    <td>{$row['age']}</td>
                    <td>{$row['sex']}</td>
                    <td>{$row['uid']}</td>
                    <td>
                        <a href='edit_player.php?id={$row['id']}'>Edit</a> |
                        <a href='delete_player.php?id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this player?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No players found.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
