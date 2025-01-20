<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = :id");
        $stmt->execute(['id' => $cart_id]);
    } catch (PDOException $e) {
        die("Error removing item from cart: " . $e->getMessage());
    }

    header("Location: cart.php");
    exit;
}
