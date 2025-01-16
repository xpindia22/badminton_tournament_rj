<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conn.php'; // Database connection
require_once 'permissions.php'; // Permissions handling

// Ensure user is logged in (primary authentication)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Define admin credentials for secondary authentication
$adminAuth = [
    'admin1' => 'securepass1',
    'xxx' => 'xxxx',
];

// Check if user has admin privileges
if (!array_key_exists($username, $adminAuth)) {
    die("Access denied: You do not have the required permissions.");
}

// Maintain per-page secondary authentication
if (!isset($_SESSION['double_authenticated_pages'])) {
    $_SESSION['double_authenticated_pages'] = [];
}

$currentPage = basename($_SERVER['PHP_SELF']);

// If the page is not authenticated, request secondary authentication
if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
    // If form submitted with secondary authentication password
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_password'])) {
        $provided_password = $_POST['auth_password'];
        $stored_password = $adminAuth[$username];

        if ($provided_password === $stored_password) {
            $_SESSION['double_authenticated_pages'][] = $currentPage;
        } else {
            die("Invalid secondary password.");
        }
    }

    // If still not authenticated, display secondary authentication form
    if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
        echo <<<HTML
            <form method="POST">
                <h1>Admin Secondary Authentication Required</h1>
                <label for="auth_password">Enter Secondary Password:</label>
                <input type="password" id="auth_password" name="auth_password" required>
HTML;

        // Preserve original `$_POST` data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                echo "<input type='hidden' name='$key' value='$value'>";
            }
        }

        echo <<<HTML
                <button type="submit">Authenticate</button>
            </form>
        HTML;
        exit; // Prevent further execution until authenticated
    }
}
?>
