<?php
include 'db_connect.php';
session_start();

// Initialize the cart session if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch products from the database
try {
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}

// Add items to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    // Fetch product details from the database
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Check if product already exists in the cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1; // Increment quantity by 1
        } else {
            // Add new product to the cart
            $_SESSION['cart'][$product_id] = [
				'product_id' => $product['id'],
				'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1, // Default quantity is 1
            ];
        }
    }

    header("Location: index.php");
    exit;
}

// Calculate cart count
$cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark's Farm Fresh Produce</title>
    <!-- Link to CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product {
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            padding: 10px;
        }
        .product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product h3 {
            margin: 10px 0;
            font-size: 1.2em;
        }
        .product p {
            color: #888;
        }
        .product .btn {
            background-color: #333; /* Footer matching color */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .product .btn:hover {
            background-color: #555;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 30px;
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
                <li><a href="cart.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count"><?php echo $cart_count; ?></span>
                </a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="user_dashboard.php">Dashboard</a></li>
					<li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="user_login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h1>Welcome to FreshFarm Market Online</h1>
            <p>Your one-stop shop for fresh farm produce delivered to your doorstep.</p>
        </section>

        <section>
            <h2>Products</h2>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p>Price: ₵<?php echo number_format($product['price'], 2); ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
