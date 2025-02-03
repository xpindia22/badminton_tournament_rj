<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('conn.php'); // Ensure this file connects correctly

// Ensure correct session variable name
$users_id = $_SESSION['users_id'] ?? 1; // Check if this session variable exists

// Fetch profile picture from the correct table
$query = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $users_id);
$query->execute();
$result = $query->get_result();
$users = $result->fetch_assoc();
$profile_picture = $users['profile_picture'] ?? 'default.png';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/profiles/"; // Ensure this folder exists and is writable
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_name = basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name; // Rename to avoid duplicates

    // Allowed file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (in_array($file_type, $allowed_types) && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        // Update in database
        $update_query = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $update_query->bind_param("si", $target_file, $users_id);
        $update_query->execute();

        // Refresh page to show updated image
        header("Location: dashboard-pic.php");
        exit();
    } else {
        echo "<p style='color:red;'>Invalid file type or upload error.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        .profile-container {
            position: absolute;
            top: 10px;
            right: 10px;
            text-align: center;
        }
        .profile-picture {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid #ddd;
            object-fit: cover;
        }
        .upload-form {
            margin-top: 5px;
        }
    </style>
</head>
<body>

<!-- Profile Picture Section -->
<div class="profile-container">
    <img src="<?php echo htmlspecialchars($profile_picture); ?>" class="profile-picture" alt="Profile Picture">
    <form class="upload-form" method="post" enctype="multipart/form-data">
        <input type="file" name="profile_picture" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>
</div>

</body>
</html>
