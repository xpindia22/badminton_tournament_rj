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

/**
 * Check if the logged-in user is the owner of a specific match.
 *
 * @param int $match_owner_id The ID of the match owner.
 * @return bool True if the logged-in user is the match owner, false otherwise.
 */
function is_match_owner($match_owner_id) {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $match_owner_id;
}

/**
 * Check if the user has moderator rights for a match (admin or match owner).
 *
 * @param int $match_owner_id The ID of the match owner.
 * @return bool True if the user is an admin or the match owner, false otherwise.
 */
function has_moderator_rights($match_owner_id) {
    return is_admin() || is_match_owner($match_owner_id);
}
?>
