<?php
session_start();
require_once('../database/config.php');

if (isset($_GET['oid'])) {
    $order_id = $_GET['oid'];
    
    // Update order status in database
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed', updated_at = NOW() WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
}

// Redirect to failure page
header("Location: ../payment_failed.php?error=payment_failed");
exit();
?> 