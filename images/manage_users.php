<?php
//manage_users.php
include 'header.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

if (!is_admin()) {
    die("Access denied.");
}

// Fetch all users from the database
$query = "SELECT id, username, email, mobile_no, role, notes FROM users";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$message = "";

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    if ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user: {$stmt->error}";
        }
        $stmt->close();
    }

    if ($_POST['action'] === 'update_role') {
        $new_role = $_POST['new_role'];
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        if ($stmt->execute()) {
            $message = "User role updated successfully.";
        } else {
            $message = "Error updating user role: {$stmt->error}";
        }
        $stmt->close();
    }

    if ($_POST['action'] === 'edit_user') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $mobile_no = $_POST['mobile_no'] ?? '';
        $notes = $_POST['notes'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile_no = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $mobile_no, $notes, $user_id);
        if ($stmt->execute()) {
            $message = "User details updated successfully.";
        } else {
            $message = "Error updating user details: {$stmt->error}";
        }
        $stmt->close();
    }

    header("Location: manage_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function openEditModal(userId, username, email, mobileNo, notes) {
            console.log("Opening modal for user:", userId);
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username || '';
            document.getElementById('edit_email').value = email || '';
            document.getElementById('edit_mobile_no').value = mobileNo || '';
            document.getElementById('edit_notes').value = notes || '';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</head>
<body>
 
    <div class="container">
        <h1>Manage Users</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Mobile No</th>
                    <th>Role</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['mobile_no'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td><?= htmlspecialchars($row['notes'] ?? '') ?></td>
                    <td>
                        <button onclick="openEditModal(
                            <?= $row['id'] ?>,
                            '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($row['mobile_no'] ?? '', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($row['notes'] ?? '', ENT_QUOTES) ?>'
                        )">Edit</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No users found.</p>
        <?php endif; ?>
    </div>

    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit User</h2>
            <form method="post">
                <input type="hidden" name="user_id" id="edit_user_id">
                <label>Username:</label>
                <input type="text" name="username" id="edit_username" required>
                <label>Email:</label>
                <input type="email" name="email" id="edit_email" required>
                <label>Mobile No:</label>
                <input type="text" name="mobile_no" id="edit_mobile_no">
                <label>Notes:</label>
                <textarea name="notes" id="edit_notes"></textarea>
                <button type="submit" name="action" value="edit_user">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
