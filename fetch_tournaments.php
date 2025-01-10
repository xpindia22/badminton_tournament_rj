<?php
/**
 * Fetch tournaments based on user role and ownership.
 * Admins can view all tournaments, while regular users can only view their own.
 *
 * @param mysqli $conn Database connection object
 * @param int $currentUserId The ID of the currently logged-in user
 * @param string $currentUserRole The role of the currently logged-in user ('admin', 'user', etc.)
 * @return array An array of tournaments with their associated categories
 */
function fetchTournaments($conn, $currentUserId, $currentUserRole) {
    // Initialize query
    if ($currentUserRole === 'admin') {
        // Admin can view all tournaments
        $query = "
            SELECT t.id AS tournament_id, t.name AS tournament_name, 
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories,
                   t.created_by
            FROM tournaments t
            LEFT JOIN tournament_categories tc ON t.id = tc.tournament_id
            LEFT JOIN categories c ON tc.category_id = c.id
            GROUP BY t.id";
        $stmt = $conn->prepare($query);
    } else {
        // Regular users can only view tournaments they created
        $query = "
            SELECT t.id AS tournament_id, t.name AS tournament_name, 
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories,
                   t.created_by
            FROM tournaments t
            LEFT JOIN tournament_categories tc ON t.id = tc.tournament_id
            LEFT JOIN categories c ON tc.category_id = c.id
            WHERE t.created_by = ?
            GROUP BY t.id";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $currentUserId);
    }

    // Execute query and fetch results
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all rows as an associative array
    $tournaments = $result->fetch_all(MYSQLI_ASSOC);

    // Close the statement
    $stmt->close();

    // Return the tournaments array
    return $tournaments;
}
?>
