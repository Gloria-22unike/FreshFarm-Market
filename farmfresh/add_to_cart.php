<?php
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    try {
        // Fetch product details including name, price, and image
        $stmt = $conn->prepare("SELECT name, price, image FROM products WHERE id = :product_id");
        $stmt->execute(['product_id' => $product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            die("Invalid product.");
        }

        $price = $product['price'];
        $name = $product['name'];
        $image = $product['image'];
        $total_price = $price * $quantity;

        // Check if item is already in the cart
        $stmt = $conn->prepare(
            "SELECT id FROM cart WHERE user_id = :user_id AND product_id = :product_id"
        );
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        $cart_item = $stmt->fetch();

        if ($cart_item) {
            // Update quantity and total price
            $stmt = $conn->prepare(
                "UPDATE cart 
                 SET quantity = quantity + :quantity, 
                     total_price = total_price + :total_price 
                 WHERE id = :cart_id"
            );
            $stmt->execute([
                'quantity' => $quantity,
                'total_price' => $total_price,
                'cart_id' => $cart_item['id']
            ]);
        } else {
            // Insert new item into cart
            $stmt = $conn->prepare(
                "INSERT INTO cart (user_id, product_id, quantity, total_price, product_name, image) 
                 VALUES (:user_id, :product_id, :quantity, :total_price, :product_name, :image)"
            );
            $stmt->execute([
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'total_price' => $total_price,
                'product_name' => $name,
                'image' => $image
            ]);
        }

        // Store product details in session cart
        if (!isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = [
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'quantity' => $quantity,
            ];
        } else {
            // Update session cart quantity
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        }

        header("Location: cart.php");
        exit;
    } catch (PDOException $e) {
        die("Error adding to cart: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>
