<?php
session_start();
require_once('../database/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle COD order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['amount']) || empty($_POST['amount'])) {
            throw new Exception("Amount is required");
        }

        $amount = floatval($_POST['amount']);
        if ($amount <= 0) {
            throw new Exception("Invalid amount");
        }

        $order_id = 'ORDER_' . time(); // Generate unique order ID
        
        // Store order details in session
        $_SESSION['order_id'] = $order_id;
        $_SESSION['amount'] = $amount;
        
        // Store order in database
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, total_amount, payment_method, status) VALUES (?, ?, ?, 'cod', 'pending')");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("sid", $order_id, $user_id, $amount);
        if (!$stmt->execute()) {
            throw new Exception("Failed to save order: " . $stmt->error);
        }
        
        // Clear cart from session
        unset($_SESSION['cart']);
        
        // Redirect to success page
        header("Location: success.php?order_id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        error_log("COD Payment Error: " . $e->getMessage());
        header("Location: ../checkout.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../checkout.php?error=invalid_request");
    exit();
}
?>