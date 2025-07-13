<?php
session_start();
require_once('../database/config.php');
require_once('payment_config.php');

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['token']) || !isset($input['amount']) || !isset($input['order_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $token = $input['token'];
    $amount = $input['amount'];
    $order_id = $input['order_id'];
    
    // Verify with Khalti API
    $khalti_api_url = getKhaltiApiUrl() . 'payment/verify/';
    $secret_key = getKhaltiSecretKey();
    
    $postData = json_encode([
        'token' => $token,
        'amount' => $amount
    ]);
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $khalti_api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Authorization: Key ' . $secret_key,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    
    if ($curl_error) {
        throw new Exception('Curl error: ' . $curl_error);
    }
    
    if ($http_code !== 200) {
        throw new Exception('Khalti API error: HTTP ' . $http_code);
    }
    
    $verification_response = json_decode($response, true);
    
    if (!$verification_response) {
        throw new Exception('Invalid response from Khalti');
    }
    
    // Check if payment is verified
    if (isset($verification_response['idx']) && $verification_response['amount'] == $amount) {
        // Payment verified successfully
        $khalti_idx = $verification_response['idx'];
        
        // Update order status in database
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed', khalti_idx = ? WHERE id = ?");
        $stmt->bind_param("si", $khalti_idx, $order_id);
        
        if ($stmt->execute()) {
            // Clear cart from session
            unset($_SESSION['cart']);
            unset($_SESSION['khalti_order']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment verified successfully',
                'order_id' => $order_id,
                'khalti_idx' => $khalti_idx
            ]);
        } else {
            throw new Exception('Failed to update order status');
        }
    } else {
        throw new Exception('Payment verification failed');
    }
    
} catch (Exception $e) {
    error_log('Khalti Verification Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>