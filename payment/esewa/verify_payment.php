<?php
session_start();
require_once('../database/config.php');
require_once('payment_config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['oid']) ? $_GET['oid'] : null;
$ref_id = isset($_GET['refId']) ? $_GET['refId'] : null;

if (!$order_id || !$ref_id) {
    $_SESSION['error'] = "Invalid payment reference";
    header('Location: ../userdashboard/order_history.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("SELECT total_amount FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = "Invalid order or order already processed";
    header('Location: ../userdashboard/order_history.php');
    exit();
}

// Verify payment with eSewa
$data = [
    'amt' => $order['total_amount'],
    'rid' => $ref_id,
    'pid' => $order_id,
    'scd' => ESEWA_MERCHANT_ID
];

$url = getEsewaVerifyUrl();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Log the verification attempt
error_log("eSewa Verification Response for Order #$order_id: " . $response);

if ($response && strpos($response, 'Success') !== false) {
    // Log success response
    error_log("Payment verified successfully for Order #$order_id with Ref ID: $ref_id");

    // Update order status to confirmed
    $stmt = $conn->prepare("UPDATE orders SET status = 'confirmed', payment_ref = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $ref_id, $order_id, $user_id);
    $stmt->execute();
    
    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $_SESSION['success'] = "Payment successful! Your order has been confirmed.";
    header("Location: ../payment/payment_success_page.php?oid=" . $order_id);
} else {
    // Log failure response
    error_log("Payment verification failed for Order #$order_id with Ref ID: $ref_id. Response: $response");

    // Update order status to failed
    $stmt = $conn->prepare("UPDATE orders SET status = 'failed', payment_ref = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $ref_id, $order_id, $user_id);
    $stmt->execute();

    $_SESSION['error'] = "Payment verification failed. Please try again or contact support.";
    header('Location: ../cart/checkout.php');
}

exit();
?>