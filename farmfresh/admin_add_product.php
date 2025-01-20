<?php
include 'db_connect.php';
session_start();

$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = (float) $_POST['price'];
    $quantity = (int) $_POST['quantity'];

    // Determine stock status based on quantity
    $stock = $quantity > 0 ? "In Stock" : "Out of Stock";

    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $error = "Failed to upload image.";
        }
    } else {
        $imagePath = "";
    }

    if (empty($error)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO products (name, category, price, quantity, image, stock, created_at)
                VALUES (:name, :category, :price, :quantity, :image, :stock, NOW())
            ");
            $stmt->execute([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $imagePath,
                'stock' => $stock
            ]);
            $success = "Product added successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
		<h1>Admin - Add Products</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_add_product.php">Add Product</a></li>
				<li><a href="admin_product_list.php">Products</a></li>
				
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="form-container">
        <h1>Add New Product</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="admin_add_product.php" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Dairy">Dairy</option>
                <option value="Vegetables">Vegetables</option>
                <option value="Fruits">Fruits</option>
                <option value="Grains">Grains</option>
                <option value="Protein">Protein</option>
            </select>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="0" required>

            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <button type="submit">Add Product</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
