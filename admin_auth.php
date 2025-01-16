<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminAuth = [
    'admin1' => 'securepass1',
    'xxx' => 'xxxx',
];

if (!isset($_SESSION['username'])) {
    die("Access denied: You must log in first.");
}

$username = $_SESSION['username'];

if (!array_key_exists($username, $adminAuth)) {
    die("Access denied: You do not have the required permissions.");
}

if (!isset($_SESSION['double_authenticated_pages'])) {
    $_SESSION['double_authenticated_pages'] = [];
}

$currentPage = basename($_SERVER['PHP_SELF']);

if (!in_array($currentPage, $_SESSION['double_authenticated_pages'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_password'])) {
        $provided_password = $_POST['auth_password'];
        $stored_password = $adminAuth[$username];

        if ($provided_password === $stored_password) {
            $_SESSION['double_authenticated_pages'][] = $currentPage;
        } else {
            die("Invalid secondary password.");
        }
    }

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
}
?>
