<?php
include 'db_connect.php';
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $product_id]);
        $_SESSION['message'] = "Product deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
    }
}

header("Location: admin_product_list.php");
exit;
