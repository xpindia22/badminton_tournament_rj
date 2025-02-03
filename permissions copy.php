<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the logged-in user has admin privileges.
 *
 * @return bool True if the user is an admin, false otherwise.
 */
if (!function_exists('is_admin')) {
    function is_admin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

/**
 * Check if the logged-in user has regular user privileges.
 *
 * @return bool True if the user is a regular user, false otherwise.
 */
if (!function_exists('is_user')) {
    function is_user() {
        return isset($_SESSION['role']) && ($_SESSION['role'] === 'user' || $_SESSION['role'] === 'moderator');
    }
}

/**
 * Check if the logged-in user is a player.
 *
 * @return bool True if the session belongs to a player, false otherwise.
 */
if (!function_exists('is_player')) {
    function is_player() {
        return isset($_SESSION['player_uid']); // Check if player session exists
    }
}

/**
 * Check if the logged-in user is a visitor (players & guests).
 *
 * @return bool True if the user is a visitor, false otherwise.
 */
// if (!function_exists('is_visitor')) {
//     function is_visitor() {
//         return !is_admin() && !is_user() && is_player(); // Players are treated as visitors
//     }
// }
?>
