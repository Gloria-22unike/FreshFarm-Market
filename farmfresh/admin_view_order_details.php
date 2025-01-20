<?php
include 'db_connect.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Validate order ID
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("Order ID is missing.");
}

$order_id = $_GET['order_id'];

// Fetch order details
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :order_id");
    $stmt->execute(['order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found.");
    }

    // Fetch order items
    $items_stmt = $conn->prepare("
        SELECT product_name, quantity, price 
        FROM order_items 
        WHERE order_id = :order_id
    ");
    $items_stmt->execute(['order_id' => $order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = trim($_POST['status']);

    try {
        $update_stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
        $update_stmt->execute(['status' => $new_status, 'order_id' => $order_id]);
        $success_message = "Order status updated successfully.";
        $order['status'] = $new_status; // Update the status in the current context
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error_message = "Failed to update order status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin - Order Details</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_view_orders.php">View Order</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Order #<?php echo htmlspecialchars($order['id']); ?></h2>
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
        <p><strong>Total Amount:</strong> <?php echo htmlspecialchars($order['total_amount']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

        <h3>Order Items</h3>
        <?php if ($order_items): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>

        <h3>Update Order Status</h3>
        <?php if (isset($success_message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="status">Change Status:</label>
            <select name="status" id="status" required>
                <option value="Pending" <?php echo ($order['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Processed" <?php echo ($order['status'] === 'Processed') ? 'selected' : ''; ?>>Processed</option>
                <option value="Shipped" <?php echo ($order['status'] === 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                <option value="Delivered" <?php echo ($order['status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
            </select>
            <button type="submit">Update Status</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
