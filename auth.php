<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure session is only started once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conn.php'; // Database connection
require_once 'permissions.php'; // Include permissions

function redirect_if_not_logged_in() {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['player_uid'])) {
        header("Location: login.php");
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']) || isset($_SESSION['player_uid']);
}

function is_visitor() {
    return !is_admin() && !is_user() && !is_player();
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}
?>
