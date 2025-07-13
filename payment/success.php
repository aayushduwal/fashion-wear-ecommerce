<?php
session_start();
require_once('../database/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check for simulation mode first
if (isset($_GET['simulation'])) {
    $order_id = $_GET['order_id'] ?? 'SIM_' . time();
    $amount = $_GET['amount'] ?? 0;
    $payment_method = $_GET['simulation'];
    
    // Clear simulation session data
    unset($_SESSION['esewa_simulation']);
    unset($_SESSION['khalti_order']);
    
    $success_message = "Payment simulation completed successfully!";
    $is_simulation = true;
} else {
    // eSewa Configuration
    $esewa_merchant_id = "EPAYTEST"; // Replace with your merchant ID
    $esewa_verification_url = "https://uat.esewa.com.np/epay/transrec"; // Testing URL

    // Check if this is an eSewa callback or direct access
    if (isset($_GET['oid']) && isset($_GET['amt']) && isset($_GET['refId'])) {
        // eSewa payment verification
        // Log the incoming request
        error_log("eSewa Success Response: " . print_r($_GET, true));
        
        try {
            $order_id = $_GET['oid'];
            $amount = $_GET['amt'];
            $ref_id = $_GET['refId'];
            
            // Verify payment with eSewa
            $url = $esewa_verification_url;
            $data = [
                'amt' => $amount,
                'rid' => $ref_id,
                'pid' => $order_id,
                'scd' => $esewa_merchant_id
        ];
        
        error_log("Verifying payment with eSewa: " . print_r($data, true));
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        
        if (curl_errno($curl)) {
            throw new Exception("Curl error: " . curl_error($curl));
        }
        
        curl_close($curl);
        
        error_log("eSewa verification response: " . $response);
        
        if (strpos($response, "Success") !== false) {
            // Update order status in database
            $stmt = $conn->prepare("UPDATE orders SET status = 'completed', payment_ref = ? WHERE order_id = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("ss", $ref_id, $order_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update order: " . $stmt->error);
            }
            
            // Clear cart
            if (isset($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
            
            // Redirect to success page
            header("Location: ../payment_success.php?order_id=" . $order_id);
            exit();
        } else {
            throw new Exception("Payment verification failed: " . $response);
        }
    } catch (Exception $e) {
        error_log("eSewa Success Handler Error: " . $e->getMessage());
        header("Location: ../payment_failed.php?error=" . urlencode($e->getMessage()));
        exit();
    }
    } elseif (isset($_GET['order_id'])) {
        // Direct access for COD or Khalti (already verified)
        $order_id = $_GET['order_id'];
        
        // Verify order exists and get details
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            header("Location: ../payment_success.php?order_id=" . $order_id);
            exit();
        } else {
            header("Location: ../payment_failed.php?error=order_not_found");
            exit();
        }
    } else {
        error_log("Invalid success response: Missing required parameters");
        header("Location: ../payment_failed.php?error=invalid_response");
        exit();
    }
}

// Handle simulation completion
if (isset($is_simulation) && $is_simulation) {
    // For class project simulations
    header("Location: ../payment_success.php?order_id=" . $order_id . "&simulation=" . $payment_method);
    exit();
}
?>