<?php
include 'db_connect.php'; // Include database connection
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// Initialize error/success messages
$success_message = "";
$error_message = "";

// Fetch user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        try {
            // Update user details
            $update_stmt = $conn->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
            $update_stmt->execute(['username' => $username, 'email' => $email, 'id' => $_SESSION['user_id']]);
            $success_message = "Profile updated successfully.";
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the new password
    if (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation do not match.";
    } else {
        try {
            // Fetch the current password hash from the database
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user_data = $stmt->fetch();

            // Verify the current password
            if (!password_verify($current_password, $user_data['password'])) {
                $error_message = "Current password is incorrect.";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $update_stmt->execute(['password' => $hashed_password, 'id' => $_SESSION['user_id']]);
                $success_message = "Password updated successfully.";
            }
        } catch (PDOException $e) {
            $error_message = "Error updating password: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="user_dashboard.php">Dashboard</a></li>
            <li><a href="user_profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>User Profile</h1>

    <?php if ($success_message): ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="user_profile.php">
        <h2>Update Profile</h2>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <form method="POST" action="user_profile.php">
        <h2>Change Password</h2>
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit" name="update_password">Update Password</button>
    </form>
</main>

	<footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
