<?php
include 'db_connect.php';
session_start();

// Initialize the cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle remove item from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit;
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']); // Ensure minimum quantity is 1
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
    header("Location: cart.php");
    exit;
}

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .cart-table th {
            background-color: #f4f4f4;
        }
        .cart-actions form {
            display: inline;
        }
        .cart-actions button {
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .cart-actions button:hover {
            background-color: #555;
        }
        .cart-total {
            text-align: right;
            margin-top: 20px;
            font-size: 1.2em;
        }
        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .cart-summary h3 {
            margin: 0;
        }
        .cart-summary a {
            background-color: #333;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
        }
        .cart-summary a:hover {
            background-color: #555;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Your Shopping Cart</h1>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty. <a href="index.php">Continue shopping</a>.</p>
        <?php else: ?>
            <div class="cart-summary">
                <h3>Total: ₵<?php echo number_format($cart_total, 2); ?></h3>
                <a href="checkout.php">Proceed to Checkout</a>
            </div>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price (₵)</th>
                        <th>Quantity</th>
                        <th>Total (₵)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                        <tr>
                            <td>
                                <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'default-image.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="product-image">
									 
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 50px;">
                                    <button type="submit" name="update_quantity">Update</button>
                                </form>
                            </td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td class="cart-actions">
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <button type="submit" name="remove_item">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
