<?php
include 'db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Validate user ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    try {
        // Update user status to "blocked"
        $stmt = $conn->prepare("UPDATE users SET status = 'blocked' WHERE id = :id");
        $stmt->execute(['id' => $user_id]);

        // Redirect back to manage users
        header("Location: admin_manage_users.php?message=User blocked successfully");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: admin_manage_users.php?error=Invalid user ID");
}
?>
