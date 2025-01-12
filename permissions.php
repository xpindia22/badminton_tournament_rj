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
?>
