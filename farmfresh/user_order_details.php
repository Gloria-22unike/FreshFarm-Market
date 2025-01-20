<?php
// Include database connection
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// Validate and get the order ID
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

try {
    // Fetch order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
    $stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found or access denied.");
    }

    // Fetch order items
    $stmt = $conn->prepare("SELECT product_name, quantity, price, subtotal FROM order_items WHERE order_id = :order_id");
    $stmt->execute(['order_id' => $order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error fetching order details: " . $e->getMessage());
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
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="user_dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Order Details</h1>
        <section>
            <h2>Order Summary</h2>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
            <p><strong>Total Amount:</strong> ₵<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
        </section>

        <section>
            <h2>Items Purchased</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price (₵)</th>
                        <th>Subtotal (₵)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₵<?php echo number_format($item['price'], 2); ?></td>
                            <td>₵<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
