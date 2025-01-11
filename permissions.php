<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the logged-in user has admin privileges.
 *
 * @return bool True if the user is an admin, false otherwise.
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the logged-in user has regular user privileges.
 *
 * @return bool True if the user is a regular user, false otherwise.
 */
function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}
?>
