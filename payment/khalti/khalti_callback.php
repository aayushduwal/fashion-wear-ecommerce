<?php
session_start();
require_once('../payment_config.php');
require_once('../../database/config.php');

// Check if callback parameters exist
if (!isset($_GET['pidx']) || !isset($_GET['status'])) {
    header("Location: ../../payment_failed.php?error=invalid_callback");
    exit();
}

$pidx = $_GET['pidx'];
$status = $_GET['status'];
$transaction_id = $_GET['transaction_id'] ?? null;
$amount = $_GET['amount'] ?? 0;
$mobile = $_GET['mobile'] ?? null;
$purchase_order_id = $_GET['purchase_order_id'] ?? null;

// Verify that this payment exists in our session
if (!isset($_SESSION['khalti_payment']) || $_SESSION['khalti_payment']['pidx'] !== $pidx) {
    error_log("Khalti Callback: Invalid session or PIDX mismatch");
    header("Location: ../../payment_failed.php?error=session_mismatch");
    exit();
}

$payment_session = $_SESSION['khalti_payment'];

// Log the callback
error_log("Khalti Callback Received: PIDX = $pidx, Status = $status, TxnID = $transaction_id");

// Always verify payment status using lookup API before proceeding
$verification_result = verifyKhaltiPayment($pidx);

if ($verification_result === false) {
    error_log("Khalti Payment Verification Failed: PIDX = $pidx");
    header("Location: ../../payment_failed.php?error=verification_failed");
    exit();
}

// Check verification status
$verification_status = $verification_result['status'] ?? 'Unknown';
$transaction_id = $verification_result['transaction_id'] ?? $pidx; // Use pidx as fallback

if ($verification_status === 'Completed') {
    // Payment successful - update existing order in database
    try {
        // Use the existing MySQLi connection from config.php
        
        // Get the order ID from session
        $order_id = $payment_session['order_id'];
        
        if (!$order_id) {
            throw new Exception("No order ID found in payment session");
        }
        
        // Update the existing order with payment details
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'completed', 
                payment_ref = ?, 
                khalti_idx = ?
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed for order update: " . $conn->error);
        }
        
        $stmt->bind_param("ssii", $pidx, $transaction_id, $order_id, $payment_session['user_id']);
        $stmt->execute();
        
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Check if the update actually affected any rows
        if ($stmt->affected_rows === 0) {
            throw new Exception("No order was updated - order may not exist or already completed");
        }
        
        $stmt->close();
        
        // Clear cart and payment session
        unset($_SESSION['cart']);
        unset($_SESSION['khalti_payment']);
        
        error_log("Khalti Payment Completed Successfully: Order ID = $order_id, PIDX = $pidx, TxnID = $transaction_id");
        
        // Redirect to success page
        header("Location: ../../payment_success.php?order_id=$order_id&txn_id=$transaction_id");
        exit();
        
    } catch (Exception $e) {
        error_log("Database Error during Khalti payment processing: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        header("Location: ../../payment_failed.php?error=database_error");
        exit();
    }
    
} else {
    // Payment not completed
    $error_type = 'payment_not_completed';
    
    switch ($verification_status) {
        case 'Pending':
            $error_type = 'payment_pending';
            break;
        case 'Expired':
            $error_type = 'payment_expired';
            break;
        case 'User canceled':
            $error_type = 'payment_canceled';
            break;
        case 'Refunded':
            $error_type = 'payment_refunded';
            break;
    }
    
    error_log("Khalti Payment Failed: PIDX = $pidx, Status = $verification_status");
    header("Location: ../../payment_failed.php?error=$error_type&status=" . urlencode($verification_status));
    exit();
}

/**
 * Verify payment using Khalti lookup API
 */
function verifyKhaltiPayment($pidx) {
    $payload = ['pidx' => $pidx];
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => getKhaltiApiUrl() . 'epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: key ' . getKhaltiSecretKey(),
            'Content-Type: application/json'
        ],
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    if ($error) {
        error_log("Khalti Verification cURL Error: " . $error);
        return false;
    }
    
    $response_data = json_decode($response, true);
    
    if ($http_code === 200 && isset($response_data['status'])) {
        return $response_data;
    }
    
    error_log("Khalti Verification Failed: HTTP Code = $http_code, Response = $response");
    return false;
}
?>