<?php
// Include database connection
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user details
    $stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }

    // Fetch user orders
    $stmt = $conn->prepare("SELECT id, total_amount, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error fetching user details or orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
				<li><a href="user_profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
        <section>
            <h2>Your Profile</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        </section>

        <section>
            <h2>Your Orders</h2>
            <?php if (count($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Amount (₵)</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td>₵<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td>
                                    <a href="user_order_details.php?order_id=<?php echo $order['id']; ?>">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not placed any orders yet.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
