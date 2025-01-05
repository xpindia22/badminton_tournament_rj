<?php
//conn.php
$servername = "localhost";
$username = "bd"; // Use your MySQL username
$password = ".0ZX@4jh/I@M]BS]"; // Use your MySQL password
$dbname = "badminton_tournament";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
