<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Debug: Session started.<br>";
} else {
    echo "Debug: Session already active.<br>";
}

// Define admin credentials
$adminAuth = [
    'admin1' => 'securepass1',
    'xxx' => 'xxxx',
];

// Debug: Check session variables
echo "<pre>Debug: Current session variables: ";
print_r($_SESSION);
echo "</pre>";

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("Access denied: You must log in first. Debug: Username not found in session.");
}

$username = $_SESSION['username'];
echo "Debug: Logged in as: $username<br>";

// Check if the user is listed in the authentication array
if (!array_key_exists($username, $adminAuth)) {
    die("Access denied: You do not have the required permissions. Debug: User '$username' not found in adminAuth.");
}

// Debug: Verify secondary authentication array
if (!isset($_SESSION['double_authenticated_pages'])) {
    $_SESSION['double_authenticated_pages'] = [];
    echo "Debug: Initialized double_authenticated_pages.<br>";
}

$currentPage = basename($_SERVER['PHP_SELF']);
echo "Debug: Current page: $currentPage<br>";

// Check if the current page is already authenticated
if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
    echo "Debug: Page not authenticated, requiring secondary login.<br>";
    
    // Handle secondary login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_password'])) {
        $provided_password = $_POST['auth_password'];
        $stored_password = $adminAuth[$username];

        if ($provided_password === $stored_password) {
            $_SESSION['double_authenticated_pages'][] = $currentPage;
            echo "Debug: Secondary authentication successful for page $currentPage.<br>";
        } else {
            die("Invalid secondary password. Debug: Incorrect password entered.");
        }
    }

    // Display secondary authentication form if not authenticated
    if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
        echo <<<HTML
            <form method="POST">
                <h1>Secondary Authentication Required</h1>
                <label for="auth_password">Enter Secondary Password:</label>
                <input type="password" id="auth_password" name="auth_password" required>
                <button type="submit">Authenticate</button>
            </form>
        HTML;
        exit;
    }
} else {
    echo "Debug: Page already authenticated.<br>";
}

// If everything is fine, allow the page to continue
echo "Debug: Authentication passed. Proceeding with the page.<br>";
?>
