<?php
//conn.php
$servername = "localhost";
$username = "bd"; // Use your MySQL username
$password = "Pagal_khota_44cd"; // Use your MySQL password
$dbname = "badminton_tournament";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
