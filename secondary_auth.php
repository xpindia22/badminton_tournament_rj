<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define admin credentials for secondary authentication
$adminAuth = [
    'admin1' => 'securepass1',
    'xxx' => 'xxxx',
];

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    die("Access denied: You must log in first.");
}

$username = $_SESSION['username'];

// Check if the user has admin privileges
if (!array_key_exists($username, $adminAuth)) {
    die("Access denied: You do not have the required permissions.");
}

// Initialize secondary authentication session
if (!isset($_SESSION['double_authenticated_pages'])) {
    $_SESSION['double_authenticated_pages'] = [];
}

$currentPage = basename($_SERVER['PHP_SELF']);

// If the current page is not yet authenticated
if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
    // Handle secondary authentication
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_password'])) {
        $provided_password = $_POST['auth_password'];
        $stored_password = $adminAuth[$username];

        if ($provided_password === $stored_password) {
            $_SESSION['double_authenticated_pages'][] = $currentPage;
        } else {
            die("Invalid secondary password.");
        }
    }

    // If not authenticated, show secondary authentication form
    if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
        echo <<<HTML
            <form method="POST">
                <h1>Secondary Authentication Required</h1>
                <label for="auth_password">Enter Secondary Password:</label>
                <input type="password" id="auth_password" name="auth_password" required>
HTML;

        // Preserve original `$_POST` data in hidden fields
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
        exit; // Stop further execution until secondary authentication is complete
    }
}
?>
