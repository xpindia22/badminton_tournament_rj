<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start(); // Start output buffering

 

include 'header.php'; // Ensure no output in header.php
require_once 'conn.php'; // Database connection settings
require_once 'admin_auth.php'; // Include admin authentication
require_once 'auth.php';
require_non_player();


// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("Access denied: You must log in first.");
}

// $username = $_SESSION['username'];

// // Check if the user is listed in the admin authentication file
// if (!array_key_exists($username, $adminAuth)) {
//     die("Access denied: You do not have the required permissions.");
// }

// // Secondary authentication
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_submit'])) {
//     $provided_password = $_POST['auth_password'];
//     $stored_password = $adminAuth[$username];

//     if ($provided_password === $stored_password) {
//         $_SESSION['double_authenticated'] = true;
//     } else {
//         die("<p style='color: red;'>Invalid secondary password. Access denied.</p>");
//     }
// }

// // Display secondary authentication form if not authenticated
// if (!isset($_SESSION['double_authenticated']) || $_SESSION['double_authenticated'] !== true) {
//     echo <<<HTML
//         <form method="POST" action="">
//             <h1>Admin Secondary Authentication</h1>
//             <label for="auth_password">Enter Secondary Password:</label>
//             <input type="password" id="auth_password" name="auth_password" required>
//             <button type="submit" name="auth_submit">Authenticate</button>
//         </form>
//     HTML;
//     ob_end_flush(); // Ensure output is flushed properly
//     exit;
// }

// Authentication successful, proceed with the rest of the page

$message = '';
$username_input = '';
$email = '';
$mobile_no = '';
$role = 'visitor';

// Check if the user has admin rights for admin actions
$is_admin = is_admin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent handling the secondary authentication form here
    if (isset($_POST['auth_submit'])) {
        // Already handled above
    } else {
        if ($is_admin && isset($_POST['user_id']) && isset($_POST['action'])) {
            $user_id = intval($_POST['user_id']);
            if ($_POST['action'] === 'edit') {
                // Save edited user data (Admin only)
                $username_input = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $mobile_no = $_POST['mobile_no'] ?? '';
                $role = $_POST['role'] ?? '';

                if (empty($username_input) || empty($email)) {
                    $message = "Username and email cannot be empty.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $message = "Invalid email format.";
                } elseif (!empty($mobile_no) && !preg_match('/^\d{10}$/', $mobile_no)) {
                    $message = "Mobile number must be 10 digits.";
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile_no = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $username_input, $email, $mobile_no, $role, $user_id);

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
            $username_input = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';
            $mobile_no = $_POST['mobile_no'] ?? '';
            $role = $_POST['role'] ?? 'visitor';

            if ($role !== 'visitor' && $role !== 'user') {
                $role = 'visitor'; // Ensure visitors can only assign "visitor" or "user" roles
            }

            if (empty($username_input) || empty($password) || empty($email)) {
                $message = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email format.";
            } elseif (!empty($mobile_no) && !preg_match('/^\d{10}$/', $mobile_no)) {
                $message = "Mobile number must be 10 digits.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, mobile_no, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $username_input, $hashed_password, $email, $mobile_no, $role);

                if ($stmt->execute()) {
                    $message = "Registration successful!";
                    $username_input = $email = $mobile_no = '';
                    $role = 'visitor';
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }

        // Refresh the page to reflect changes
        header("Refresh:0");
        exit;
    }
}

// Fetch all users for admin
$users = [];
if ($is_admin) {
    $result = $conn->query("SELECT * FROM users");
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $message = "Error fetching users: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register and Admin Access</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional styles for better presentation */
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .btn-primary, .btn-edit, .btn-delete {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-edit {
            background-color: #28a745;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .edit-form input, .edit-form select {
            width: auto;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if ($message): ?>
            <p class="message <?= strpos($message, 'Error') !== false || strpos($message, 'cannot') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($username_input) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" <?= $is_admin ? 'required' : 'required' ?>>
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
                    <?php if ($is_admin): ?>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">Register</button>
        </form>

        <?php if ($is_admin): ?>
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
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <form method="POST" class="edit-form">
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required></td>
                                    <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></td>
                                    <td><input type="text" name="mobile_no" value="<?= htmlspecialchars($user['mobile_no']) ?>"></td>
                                    <td>
                                        <select name="role" required>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
ob_end_flush();
?>
