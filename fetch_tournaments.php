<?php
function fetchTournaments($conn, $currentUserId, $currentUserRole) {
    if ($currentUserRole === 'admin') {
        // Admin can view all tournaments
        $query = "
            SELECT t.id AS tournament_id, t.name AS tournament_name, 
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories,
                   t.created_by, u.username AS owner_name
            FROM tournaments t
            LEFT JOIN tournament_categories tc ON t.id = tc.tournament_id
            LEFT JOIN categories c ON tc.category_id = c.id
            LEFT JOIN users u ON t.created_by = u.id
            GROUP BY t.id";
        $stmt = $conn->prepare($query);
    } else {
        // Regular users can only view tournaments they created
        $query = "
            SELECT t.id AS tournament_id, t.name AS tournament_name, 
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories,
                   t.created_by, u.username AS owner_name
            FROM tournaments t
            LEFT JOIN tournament_categories tc ON t.id = tc.tournament_id
            LEFT JOIN categories c ON tc.category_id = c.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.created_by = ?
            GROUP BY t.id";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $currentUserId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $tournaments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $tournaments;
}
?>
