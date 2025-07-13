<?php
session_start();
require_once('../../database/config.php');
require_once('../payment_config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if payment data is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['amount']) || !isset($_POST['order_id'])) {
    header("Location: ../../checkout.php?error=invalid_request");
    exit();
}

$amount = floatval($_POST['amount']);
$order_id = intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];

// Validate amount
if ($amount < 10) {
    header("Location: ../../checkout.php?error=minimum_amount");
    exit();
}

// Convert amount to paisa (multiply by 100)
$amount_paisa = intval($amount * 100);

// Generate unique purchase order ID
$purchase_order_id = 'ORDER_' . time() . '_' . $user_id;

// Get user details (you might want to fetch from database)
$customer_name = $_SESSION['user_name'] ?? 'Fashion Customer';
$customer_email = $_SESSION['user_email'] ?? 'customer@fashionwear.com';
$customer_phone = $_SESSION['user_phone'] ?? '9800000000';

// Prepare the payment request payload
$payload = [
    'return_url' => getReturnUrl(),
    'website_url' => getWebsiteUrl(),
    'amount' => $amount_paisa,
    'purchase_order_id' => $purchase_order_id,
    'purchase_order_name' => 'FashionWear Order',
    'customer_info' => [
        'name' => $customer_name,
        'email' => $customer_email,
        'phone' => $customer_phone
    ],
    'amount_breakdown' => [
        [
            'label' => 'Product Total',
            'amount' => $amount_paisa
        ]
    ],
    'product_details' => [
        [
            'identity' => $purchase_order_id,
            'name' => 'FashionWear Products',
            'total_price' => $amount_paisa,
            'quantity' => 1,
            'unit_price' => $amount_paisa
        ]
    ],
    'merchant_username' => 'fashionwear',
    'merchant_extra' => 'ecommerce_order'
];

// Initialize cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => getKhaltiApiUrl() . 'epayment/initiate/',
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

// Handle cURL errors
if ($error) {
    error_log("Khalti cURL Error: " . $error);
    header("Location: ../../payment_failed.php?error=network_error");
    exit();
}

// Parse response
$response_data = json_decode($response, true);

if ($http_code === 200 && isset($response_data['pidx'])) {
    // Payment initiation successful
    
    // Store payment details in session for callback verification
    $_SESSION['khalti_payment'] = [
        'pidx' => $response_data['pidx'],
        'purchase_order_id' => $purchase_order_id,
        'amount' => $amount_paisa,
        'user_id' => $user_id,
        'order_id' => $order_id,
        'initiated_at' => date('Y-m-d H:i:s')
    ];
    
    // Log the payment initiation
    error_log("Khalti Payment Initiated: PIDX = " . $response_data['pidx'] . ", Order ID = " . $purchase_order_id);
    
    // Redirect to Khalti payment page
    header("Location: " . $response_data['payment_url']);
    exit();
    
} else {
    // Payment initiation failed
    error_log("Khalti Payment Initiation Failed: " . $response);
    
    // Check for specific error messages
    $error_message = "payment_initiation_failed";
    if (isset($response_data['error_key'])) {
        $error_message = $response_data['error_key'];
    }
    
    header("Location: ../../payment_failed.php?error=" . $error_message);
    exit();
}
?>