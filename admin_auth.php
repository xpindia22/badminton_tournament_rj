<?php
<<<<<<< HEAD
// Start session if not already active
=======
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// Define admin credentials
=======
require_once 'conn.php'; // Database connection
require_once 'permissions.php'; // Permissions handling

// Ensure user is logged in (primary authentication)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Define admin credentials for secondary authentication
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
$adminAuth = [
    'admin1' => 'securepass1',
    'xxx' => 'xxxx',
];

<<<<<<< HEAD
// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    die("Access denied: You must log in first.");
}

$username = $_SESSION['username'];

// Check if the user has admin privileges
=======
// Check if user has admin privileges
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
if (!array_key_exists($username, $adminAuth)) {
    die("Access denied: You do not have the required permissions.");
}

<<<<<<< HEAD
// Manage per-page secondary authentication
=======
// Maintain per-page secondary authentication
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
if (!isset($_SESSION['double_authenticated_pages'])) {
    $_SESSION['double_authenticated_pages'] = [];
}

$currentPage = basename($_SERVER['PHP_SELF']);

<<<<<<< HEAD
// If the current page is not yet authenticated
if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
    // If secondary authentication form is submitted
=======
// If the page is not authenticated, request secondary authentication
if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
    // If form submitted with secondary authentication password
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_password'])) {
        $provided_password = $_POST['auth_password'];
        $stored_password = $adminAuth[$username];

        if ($provided_password === $stored_password) {
            $_SESSION['double_authenticated_pages'][] = $currentPage;
        } else {
            die("Invalid secondary password.");
        }
    }

<<<<<<< HEAD
    // If secondary authentication is still required
    if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
        // Display the secondary authentication form while preserving original `$_POST` data
        echo <<<HTML
            <form method="POST">
                <h1>Secondary Authentication Required</h1>
=======
    // If still not authenticated, display secondary authentication form
    if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
        echo <<<HTML
            <form method="POST">
                <h1>Admin Secondary Authentication Required</h1>
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
                <label for="auth_password">Enter Secondary Password:</label>
                <input type="password" id="auth_password" name="auth_password" required>
HTML;

<<<<<<< HEAD
        // Preserve original `$_POST` data in hidden fields
        foreach ($_POST as $key => $value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            echo "<input type='hidden' name='$key' value='$value'>";
=======
        // Preserve original `$_POST` data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                echo "<input type='hidden' name='$key' value='$value'>";
            }
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
        }

        echo <<<HTML
                <button type="submit">Authenticate</button>
            </form>
        HTML;
<<<<<<< HEAD
        exit; // Stop further execution until secondary authentication is complete
=======
        exit; // Prevent further execution until authenticated
>>>>>>> ae5eee893e2184adc76a7aaadfdc8529dadac1f6
    }
}
?>
