<?php
// register.php
require_once 'conn.php';
require 'auth.php';
redirect_if_not_logged_in();

$message = '';
$username = '';
$email = '';
$mobile_no = '';
$role = 'visitor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['action'])) {
        $user_id = intval($_POST['user_id']);
        if ($_POST['action'] === 'edit') {
            // Save edited user data (Admin only)
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $mobile_no = $_POST['mobile_no'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($username) || empty($email)) {
                $message = "Username and email cannot be empty.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email format.";
            } elseif (!empty($mobile_no) && !preg_match('/^\d{10}$/', $mobile_no)) {
                $message = "Mobile number must be 10 digits.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile_no = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $mobile_no, $role, $user_id);

                if ($stmt->execute()) {
                    $message = "User updated successfully.";
                } else {
                    $message = "Error updating user: " . $stmt->error;
                }
                $stmt->close();
            }
        } elseif ($_POST['action'] === 'delete') {
            // Delete user (Admin only)
            if ($user_id !== $_SESSION['user_id']) { // Prevent self-deletion
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);

                if ($stmt->execute()) {
                    $message = "User deleted successfully.";
                } else {
                    $message = "Error deleting user: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "You cannot delete your own account.";
            }
        }
    } else {
        // Handle new user registration
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $mobile_no = $_POST['mobile_no'] ?? '';
        $role = $_POST['role'] ?? 'visitor';

        if (empty($username) || empty($password) || empty($email)) {
            $message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } elseif (!empty($mobile_no) && !preg_match('/^\d{10}$/', $mobile_no)) {
            $message = "Mobile number must be 10 digits.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, mobile_no, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $hashed_password, $email, $mobile_no, $role);

            if ($stmt->execute()) {
                $message = "Registration successful!";
                $username = $email = $mobile_no = '';
                $role = 'visitor';
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all users for admin
$users = [];
if (is_admin()) {
    $result = $conn->query("SELECT * FROM users");
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .edit-form {
            display: inline-block;
        }
        .btn-edit, .btn-delete {
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-edit {
            background-color: #007bff;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="mobile_no">Mobile No:</label>
                <input type="text" name="mobile_no" id="mobile_no" value="<?= htmlspecialchars($mobile_no) ?>">
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="visitor" <?= $role === 'visitor' ? 'selected' : '' ?>>Visitor</option>
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Register</button>
        </form>

        <?php if (is_admin()): ?>
            <h2>All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Mobile No</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <form method="POST" class="edit-form">
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"></td>
                                <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"></td>
                                <td><input type="text" name="mobile_no" value="<?= htmlspecialchars($user['mobile_no']) ?>"></td>
                                <td>
                                    <select name="role">
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="visitor" <?= $user['role'] === 'visitor' ? 'selected' : '' ?>>Visitor</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="action" value="edit" class="btn-edit">Save</button>
                                    <button type="submit" name="action" value="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
