<?php
session_start();
include 'db_connect.php';

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_manage_products.php");
    exit;
}

$product_id = $_GET['id'];
$success = $error = "";

// Fetch product details
try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch();
    if (!$product) {
        header("Location: admin_manage_products.php");
        exit;
    }
} catch (PDOException $e) {
    $error = "Error fetching product details: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image = $product['image']; // Default to the existing image

    // Handle image upload if a new image is provided
    if (!empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $upload_dir = 'uploads/'; // Ensure this directory exists and is writable
        $image_path = $upload_dir . basename($image_name);

        if (move_uploaded_file($image_tmp, $image_path)) {
            $image = $image_path;
        } else {
            $error = "Error uploading the image.";
        }
    }

    // Update the product in the database
    if (empty($error)) {
        try {
            $stmt = $conn->prepare(
                "UPDATE products SET name = :name, category = :category, price = :price, quantity = :quantity, image = :image WHERE id = :id"
            );
            $stmt->execute([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $image,
                'id' => $product_id,
            ]);
            header("Location: admin_manage_products.php"); // Redirect after update
            exit;
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
        <h1>Edit Product</h1>
    </header>
    <main>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

            <label for="price">Price (₵):</label>
            <input type="number" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" step="0.01" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required>

            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <?php if (!empty($product['image'])): ?>
                <p>Current Image:</p>
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image" style="width: 150px;">
            <?php endif; ?>

            <button type="submit">Update Product</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2025 Mark Farm Fresh Produce. All rights reserved.</p>
    </footer>
</body>
</html>
