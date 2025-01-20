<?php
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// Get user details
$user_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found.");
    }
} catch (Exception $e) {
    die("Error fetching user details: " . $e->getMessage());
}

// Check if the cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

// Initialize variables
$cart_items = $_SESSION['cart'];
$cart_total = 0;

// Calculate total
foreach ($cart_items as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $payment_method = htmlspecialchars($_POST['payment_method']);

    // Validate form inputs
    if ($name && $email && $phone && $address && $payment_method) {
        try {
            // Save order to database
            $conn->beginTransaction();

            // Insert into orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, phone, address, payment_method, total_amount, created_at) 
                                    VALUES (:user_id, :name, :email, :phone, :address, :payment_method, :total_amount, NOW())");
            $stmt->execute([
                ':user_id' => $user_id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':payment_method' => $payment_method,
                ':total_amount' => $cart_total,
            ]);

            // Get the order ID
            $order_id = $conn->lastInsertId();

            // Insert into order_items table
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price, subtotal) 
                                    VALUES (:order_id, :product_name, :quantity, :price, :subtotal)");
            foreach ($cart_items as $item) {
                $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_name' => $item['name'],
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price'],
                    ':subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            $conn->commit();

            // Clear the cart
            $_SESSION['cart'] = [];

            // Redirect to success page
            header("Location: order_success.php?order_id=$order_id");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            die("Order processing failed: " . $e->getMessage());
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Checkout</h1>

        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <h2>Order Summary</h2>
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
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Total: ₵<?php echo number_format($cart_total, 2); ?></h3>

        <h2>Billing Details</h2>
        <form method="POST" action="">
            <label for="name">Full Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <label for="address">Delivery Address:</label>
            <textarea name="address" id="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>

            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="cash_on_delivery">Cash on Delivery</option>
                <option value="online_payment">Online Payment</option>
            </select>

            <button type="submit" name="place_order">Place Order</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
