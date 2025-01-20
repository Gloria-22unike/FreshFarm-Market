<?php
include 'db_connect.php';
session_start();

$error = "";
$success = "";

// Ensure the product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Fetch existing product details
try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: admin_dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    $error = "Error fetching product details: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];

    $stock = $quantity > 0 ? "In Stock" : "Out of Stock";

    // Image upload handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $error = "Failed to upload image.";
        }
    } else {
        $imagePath = $product['image'];
    }

    if (empty($error)) {
        try {
            $stmt = $conn->prepare("
                UPDATE products 
                SET name = :name, category = :category, price = :price, quantity = :quantity, image = :image, stock = :stock 
                WHERE id = :id
            ");
            $stmt->execute([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $imagePath,
                'stock' => $stock,
                'id' => $product_id,
            ]);
            $success = "Product updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating product: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_add_product.php">Add Product</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="form-container">
        <h1>Edit Product</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="admin_edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Dairy" <?php echo $product['category'] === 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                <option value="Vegetables" <?php echo $product['category'] === 'Vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                <option value="Fruits" <?php echo $product['category'] === 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
                <option value="Grains" <?php echo $product['category'] === 'Grains' ? 'selected' : ''; ?>>Grains</option>
                <option value="Protein" <?php echo $product['category'] === 'Protein' ? 'selected' : ''; ?>>Protein</option>
            </select>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="0" value="<?php echo $product['quantity']; ?>" required>

            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image" accept="image/*">
            <p>Current Image: <img src="<?php echo $product['image']; ?>" alt="Product Image" width="100"></p>

            <button type="submit">Update Product</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Mark's Farm Fresh Produce. All rights reserved.</p>
    </footer>
</body>
</html>
