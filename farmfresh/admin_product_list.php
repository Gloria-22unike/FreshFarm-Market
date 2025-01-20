<?php
include 'db_connect.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch products from the database
$products = [];
try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching products: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin - Products List</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_product_list.php">Products</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Manage Products</h2>
        <a href="admin_add_product.php" class="btn">Add New Product</a>
        <?php if (!empty($products)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Image</th>
                        <th>Stock Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>₵<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" style="width: 50px; height: 50px;">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?></td>
                            <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                            <td>
                                <a href="admin_edit_product.php?id=<?php echo $product['id']; ?>">Edit</a> |
                                <a href="admin_delete_product.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products found. <a href="admin_add_product.php">Add a product</a>.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
	</footer>
</body>
</html>