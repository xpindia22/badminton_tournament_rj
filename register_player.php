<?php
session_start();
require 'conn.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $uid = uniqid('UID_');

    if (empty($name) || empty($username) || empty($password) || empty($dob) || empty($sex)) {
        $message = "All fields are required.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM players WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username already exists. Please choose another.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO players (name, username, password, dob, age, sex, uid) VALUES (?, ?, ?, ?, TIMESTAMPDIFF(YEAR, ?, CURDATE()), ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssssss", $name, $username, $hashed_password, $dob, $dob, $sex, $uid);

            if ($stmt->execute()) {
                $message = "Registration successful! You can now log in.";
            } else {
                $message = "Error registering player: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Player Registration</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Date of Birth:</label>
        <input type="date" name="dob" required>

        <label>Sex:</label>
        <select name="sex" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select>

        <button type="submit">Register</button>
    </form>
</body>
</html>
