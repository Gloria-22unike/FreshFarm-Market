<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Delete the product from the database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Redirect back to the products page with a success message
        $_SESSION['success'] = "Product deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
    }
}

header("Location: admin_manage_products.php");
exit;
?>
