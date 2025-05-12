<?php
session_start();
require_once('../database/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['oid']) ? $_GET['oid'] : null;

if (!$order_id) {
    $_SESSION['error'] = "Invalid order reference";
    header('Location: ../userdashboard/order_history.php');
    exit();
}

// Check if the order is still pending
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if ($order && $order['status'] === 'pending') {
    // Log the cancellation
    error_log("Order #$order_id canceled by user");

    $_SESSION['error'] = "Payment was canceled. Your order is still pending.";
    header('Location: ../cart/checkout.php');
    exit();
} else {
    // Update order status to failed if needed
    $stmt = $conn->prepare("UPDATE orders SET status = 'failed' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    $_SESSION['error'] = "Payment failed. Please try again or choose a different payment method.";
    header('Location: ../cart/checkout.php');
    exit();
}
?>
