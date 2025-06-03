<?php
session_start();
require_once('../database/config.php');

// Khalti API Configuration
$khalti_secret_key = "test_secret_key_dc74b7a6a6b04b8b9e6e2b7e7e7e7e7e";
$khalti_public_key = "test_public_key_dc74b7a6a6b04b8b9e6e2b7e7e7e7e7e";

// Get the payment data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['token']) && isset($data['amount'])) {
    $token = $data['token'];
    $amount = $data['amount'];

    // Initialize cURL
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://khalti.com/api/v2/payment/verify/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'token' => $token,
            'amount' => $amount
        ]),
        CURLOPT_HTTPHEADER => [
            "Authorization: Key " . $khalti_secret_key,
            "Content-Type: application/json",
        ],
    ]);

    // Execute cURL request
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo json_encode(['status' => 'error', 'message' => 'Payment verification failed']);
    } else {
        $result = json_decode($response, true);
        
        if (isset($result['idx'])) {
            // Payment successful
            // Here you can update your database with the order information
            // and clear the cart
            
            $_SESSION['cart'] = []; // Clear the cart
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment successful',
                'transaction_id' => $result['idx']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Payment verification failed']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>