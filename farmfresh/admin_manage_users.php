<?php
include 'db_connect.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all users
try {
    $stmt = $conn->prepare("SELECT id, username, email, status, created_at FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle status change
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $new_status, 'id' => $user_id]);
        header("Location: admin_manage_users.php");
        exit;
    } catch (PDOException $e) {
        die("Error updating user status: " . $e->getMessage());
    }
}

// Handle delete user
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        header("Location: admin_manage_users.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting user: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin - Manage Users</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_manage_users.php">Manage Users</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard">
        <h1>Manage Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['status']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <form action="admin_manage_users.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="status">
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="blocked" <?php echo $user['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                            <form action="admin_manage_users.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
