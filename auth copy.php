<?php

require_once 'conn.php'; // Database connection

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login if the user is not logged in.
 */
function redirect_if_not_logged_in() {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['player_uid'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Check if the user is logged in.
 *
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) || isset($_SESSION['player_uid']);
}

/**
 * Hash a password securely.
 *
 * @param string $password The plain text password.
 * @return string The hashed password.
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a stored hash.
 *
 * @param string $password The plain text password.
 * @param string $hash The stored hash.
 * @return bool True if the password matches the hash, false otherwise.
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
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
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'user' || $_SESSION['role'] === 'moderator');
}

/**
 * Check if the logged-in user is a player.
 *
 * @return bool True if the session belongs to a player, false otherwise.
 */
function is_player() {
    return isset($_SESSION['player_uid']); // Check if player session exists
}

/**
 * Check if the logged-in user is a visitor (players & guests).
 *
 * @return bool True if the user is a visitor, false otherwise.
 */
function is_visitor() {
    return !is_admin() && !is_user() && !is_player();
}

?>
