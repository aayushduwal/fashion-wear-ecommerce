<?php
session_start();
require_once('../database/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// eSewa Configuration
$esewa_url = "https://uat.esewa.com.np/epay/main"; // Testing URL
$esewa_merchant_id = "EPAYTEST"; // Replace with your merchant ID
$esewa_success_url = "http://" . $_SERVER['HTTP_HOST'] . "/FashionWear/payment/success.php";
$esewa_failure_url = "http://" . $_SERVER['HTTP_HOST'] . "/FashionWear/payment/failure.php";

function generateEsewaPayment($amount, $order_id) {
    global $esewa_url, $esewa_merchant_id, $esewa_success_url, $esewa_failure_url;
    
    $data = [
        'amt' => $amount,
        'pdc' => 0,
        'psc' => 0,
        'txAmt' => 0,
        'tAmt' => $amount,
        'pid' => $order_id,
        'scd' => $esewa_merchant_id,
        'su' => $esewa_success_url,
        'fu' => $esewa_failure_url
    ];
    
    // Log the payment data
    error_log("eSewa Payment Data: " . print_r($data, true));
    
    return $data;
}

// Handle payment initiation
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
        
        $payment_data = generateEsewaPayment($amount, $order_id);
        
        // Store order in database
        $stmt = $conn->prepare("INSERT INTO orders (order_id, amount, payment_status, created_at) VALUES (?, ?, 'pending', NOW())");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("sd", $order_id, $amount);
        if (!$stmt->execute()) {
            throw new Exception("Failed to save order: " . $stmt->error);
        }
        
        // Build and log the payment URL
        $payment_url = $esewa_url . "?" . http_build_query($payment_data);
        error_log("Redirecting to eSewa URL: " . $payment_url);
        
        // Redirect to eSewa payment page
        header("Location: " . $payment_url);
        exit();
        
    } catch (Exception $e) {
        error_log("eSewa Payment Error: " . $e->getMessage());
        header("Location: ../payment_failed.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../checkout.php?error=invalid_request");
    exit();
}
?> 