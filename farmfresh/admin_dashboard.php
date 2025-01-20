<?php
include 'db_connect.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch admin information
try {
    $stmt = $conn->prepare("SELECT username FROM admins WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        nav ul {
            display: flex;
            list-style: none;
            justify-content: center;
            padding: 0;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        .dashboard {
            padding: 20px;
        }
        .dashboard h1 {
            margin-bottom: 20px;
        }
        .dashboard ul {
            list-style: none;
            padding: 0;
        }
        .dashboard ul li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($admin['username']); ?>!</h1>
        <nav>
            <ul>
                <li><a href="admin_product_list.php">Manage Products</a></li>
                <li><a href="admin_manage_users.php">Manage Users</a></li>
				<li><a href="admin_view_orders.php">View Orders</a></li>				
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard">
        <h1>Admin Dashboard</h1>
        <ul>
            <li><a href="admin_product_list.php">Add/Edit/Delete Products</a></li>
            <li><a href="admin_view_orders.php">View All Orders</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </main>
	
	<footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
	</footer>
	
</body>
</html>
