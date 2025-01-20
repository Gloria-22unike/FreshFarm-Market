<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

include 'db_connect.php';

// Validate and fetch order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid order ID");
}

$order_id = $_GET['id'];

try {
    // Delete related order items
    $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = :order_id");
    $stmt_items->execute(['order_id' => $order_id]);

    // Delete the order itself
    $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = :id");
    $stmt_order->execute(['id' => $order_id]);

    header("Location: admin_manage_orders.php?success=Order+deleted+successfully");
    exit;
} catch (PDOException $e) {
    die("Error deleting order: " . $e->getMessage());
}
