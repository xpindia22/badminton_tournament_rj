<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Check if the logged-in user has moderator privileges.
 *
 * @return bool True if the user is a moderator, false otherwise.
 */
function is_moderator() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'moderator';
}
?>
