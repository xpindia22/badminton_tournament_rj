<?php
// Connection script
$servername = "localhost";
$username = "root";
$password = "xxx";
$dbname = "badminton_tournament";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL syntax to create the database and tables
$sqls = [
    "CREATE DATABASE IF NOT EXISTS badminton_tournament;",

    "CREATE TABLE IF NOT EXISTS tournaments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        year YEAR NOT NULL
    );",

    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age_group VARCHAR(255) NOT NULL,
        sex ENUM('M', 'F') NOT NULL
    );",

    "CREATE TABLE IF NOT EXISTS players (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age INT NOT NULL,
        sex ENUM('M', 'F') NOT NULL,
        uid VARCHAR(100) UNIQUE NOT NULL
    );",

    "CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tournament_id INT NOT NULL,
        category_id INT NOT NULL,
        pool ENUM('A', 'B') DEFAULT NULL,
        player1_id INT NOT NULL,
        player2_id INT NOT NULL,
        pre_quarter BOOLEAN DEFAULT FALSE,
        quarter BOOLEAN DEFAULT FALSE,
        semi BOOLEAN DEFAULT FALSE,
        final BOOLEAN DEFAULT FALSE,
        set1_player1_points INT DEFAULT 0,
        set1_player2_points INT DEFAULT 0,
        set2_player1_points INT DEFAULT 0,
        set2_player2_points INT DEFAULT 0,
        set3_player1_points INT DEFAULT 0,
        set3_player2_points INT DEFAULT 0,
        FOREIGN KEY (tournament_id) REFERENCES tournaments(id),
        FOREIGN KEY (category_id) REFERENCES categories(id),
        FOREIGN KEY (player1_id) REFERENCES players(id),
        FOREIGN KEY (player2_id) REFERENCES players(id)
    );"
];

// Execute the SQLs
foreach ($sqls as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully: $sql\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

$conn->close();
?>

<!-- PHP Script: insert_tournament.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $year = $_POST['year'];

    $sql = "INSERT INTO tournaments (name, year) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $year);

    if ($stmt->execute()) {
        echo "Tournament added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- PHP Script: insert_category.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age_group = $_POST['age_group'];
    $sex = $_POST['sex'];

    $sql = "INSERT INTO categories (name, age_group, sex) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $age_group, $sex);

    if ($stmt->execute()) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- PHP Script: insert_player.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $uid = $_POST['uid'];

    $sql = "INSERT INTO players (name, age, sex, uid) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $name, $age, $sex, $uid);

    if ($stmt->execute()) {
        echo "Player added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- PHP Script: insert_match.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $category_id = $_POST['category_id'];
    $pool = $_POST['pool'];
    $player1_id = $_POST['player1_id'];
    $player2_id = $_POST['player2_id'];

    $sql = "INSERT INTO matches (tournament_id, category_id, pool, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisii", $tournament_id, $category_id, $pool, $player1_id, $player2_id);

    if ($stmt->execute()) {
        echo "Match added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
