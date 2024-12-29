<?php
require 'auth.php';
redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $query = "SELECT * FROM tournaments";
} else {
    $query = "SELECT * FROM tournaments WHERE created_by = $user_id";
}

$result = $conn->query($query);

echo "<h1>Your Tournaments</h1>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Year</th><th>Actions</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['year']}</td>";
    echo "<td>";
    echo "<a href='edit_tournament.php?id={$row['id']}'>Edit</a> | ";
    echo "<a href='delete_tournament.php?id={$row['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
?>
