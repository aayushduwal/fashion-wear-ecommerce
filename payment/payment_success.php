<?php
session_start();
require_once('../database/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['oid']) ? $_GET['oid'] : null;
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : null;

if (!$order_id || !$payment_method) {
    $_SESSION['error'] = "Invalid order reference.";
    header('Location: ../userdashboard/order_history.php');
    exit();
}

// Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch the order amount
$stmt = $conn->prepare("SELECT amount FROM orders WHERE id = ? AND user_id = ?");
if (!$stmt) {
    die("SQL prepare error (select order): " . $conn->error);
}
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    error_log("Order not found for ID: $order_id, User ID: $user_id");
    $_SESSION['error'] = "Order not found.";
    header('Location: ../userdashboard/order_history.php');
    exit();
}

$order_amount = $order['amount'];
error_log("Order amount: $order_amount");

// Payment Verification
if ($payment_method === 'esewa') {
    $response = file_get_contents(
        "https://esewa.com.np/epay/transrec" .
        "?amt=$order_amount" .
        "&rid=" . urlencode($_GET['refId']) .
        "&pid=" . urlencode($_GET['pid']) .
        "&scd=" . urlencode(ESEWA_MERCHANT_ID)
    );
    error_log("eSewa Response: " . $response);

    if (strpos($response, "Success") === false) {
        error_log("eSewa payment verification failed.");
        $_SESSION['error'] = "Payment verification failed.";
        header('Location: ../userdashboard/order_history.php');
        exit();
    }
}

if ($payment_method === 'khalti') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://khalti.com/api/v2/payment/verify/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'token' => $_GET['token'],
        'amount' => $_GET['amount']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Key ' . KHALTI_SECRET_KEY]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    error_log("Khalti Response (status code): " . $status_code);
    error_log("Khalti Response (raw): " . $response);

    if ($status_code !== 200 || $response_data['state']['name'] !== 'Completed') {
        error_log("Khalti payment verification failed.");
        $_SESSION['error'] = "Payment verification failed.";
        header('Location: ../userdashboard/order_history.php');
        exit();
    }
}

// Update Order Status
$stmt = $conn->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND user_id = ?");
if (!$stmt) {
    die("SQL prepare error (update status): " . $conn->error);
}
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    error_log("Order status updated successfully.");

    // Clear Cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    if (!$stmt) {
        die("SQL prepare error (delete cart): " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    error_log("Cart cleared for user ID: $user_id");

    // Redirect to success page
    header("Location: ../payment/payment_success_page.php?oid=$order_id");
    exit();
} else {
    error_log("Failed to update order status.");
    $_SESSION['error'] = "Could not confirm order.";
    header('Location: ../userdashboard/order_history.php');
    exit();
}
?>
