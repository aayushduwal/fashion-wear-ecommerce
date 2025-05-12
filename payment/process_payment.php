<?php
session_start();
require_once('../database/config.php');
require_once('payment_config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart/checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? '';
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$city = $_POST['city'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$delivery_zone = $_POST['delivery_zone'] ?? '';
$detailed_address = $_POST['detailed_address'] ?? '';
$email = $_SESSION['email'] ?? ''; // Get email from session

// Validate required fields
if (!$payment_method || !$full_name || !$phone || !$city || !$delivery_zone) {
    $_SESSION['error'] = "Please fill all required fields";
    header('Location: ../cart/checkout.php');
    exit();
}

// Get cart items and total
$stmt = $conn->prepare("SELECT c.*, p.name, p.price FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    $_SESSION['error'] = "Your cart is empty";
    header('Location: ../cart/checkout.php');
    exit();
}

// Calculate total amount
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Add delivery charge
$delivery_charges = [
    'inside_ring' => 85,
    'outside_ring' => 150,
    'outside_valley' => 250
];
$delivery_charge = $delivery_charges[$delivery_zone] ?? 0;
$total_amount += $delivery_charge;

// Create or update customer
$stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, city, postal_code, country) 
                       VALUES (?, ?, ?, ?, ?, ?, 'Nepal') 
                       ON DUPLICATE KEY UPDATE 
                       name = VALUES(name),
                       phone = VALUES(phone),
                       address = VALUES(address),
                       city = VALUES(city),
                       postal_code = VALUES(postal_code)");

if (!$stmt) {
    error_log("Customer query preparation failed: " . $conn->error);
    $_SESSION['error'] = "System error. Please try again later.";
    header('Location: ../cart/checkout.php');
    exit();
}

$stmt->bind_param("ssssss", $full_name, $email, $phone, $detailed_address, $city, $postal_code);

if (!$stmt->execute()) {
    error_log("Customer creation failed: " . $stmt->error);
    $_SESSION['error'] = "Failed to save customer information. Please try again.";
    header('Location: ../cart/checkout.php');
    exit();
}

$customer_id = $stmt->insert_id ?: $conn->insert_id;

// Prepare shipping address
$shipping_address = "Name: $full_name\n";
$shipping_address .= "Phone: $phone\n";
$shipping_address .= "Address: $detailed_address\n";
$shipping_address .= "City: $city\n";
$shipping_address .= "Postal Code: $postal_code\n";
$shipping_address .= "Delivery Zone: $delivery_zone";

$delivery_date = date('Y-m-d', strtotime('+7 days'));

// Create order
$stmt = $conn->prepare("INSERT INTO orders (user_id, customer_id, total_amount, status, shipping_address, payment_method, delivery_zone) 
                       VALUES (?, ?, ?, 'pending', ?, ?, ?)");

if (!$stmt) {
    error_log("Order query preparation failed: " . $conn->error);
    $_SESSION['error'] = "System error. Please try again later.";
    header('Location: ../cart/checkout.php');
    exit();
}

$stmt->bind_param("iidsss", $user_id, $customer_id, $total_amount, $shipping_address, $payment_method, $delivery_zone);

if (!$stmt->execute()) {
    error_log("Order creation failed: " . $stmt->error);
    $_SESSION['error'] = "Failed to create order. Please try again.";
    header('Location: ../cart/checkout.php');
    exit();
}

$order_id = $stmt->insert_id;

// Create order items
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    error_log("Order items query preparation failed: " . $conn->error);
    $_SESSION['error'] = "System error. Please try again later.";
    header('Location: ../cart/checkout.php');
    exit();
}

foreach ($cart_items as $item) {
    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
    if (!$stmt->execute()) {
        error_log("Order item creation failed: " . $stmt->error);
        // Roll back the order
        $conn->query("DELETE FROM orders WHERE id = " . $order_id);
        $_SESSION['error'] = "Failed to create order items. Please try again.";
        header('Location: ../cart/checkout.php');
        exit();
    }
}

switch ($payment_method) {
    case 'cod':
        // For COD, just redirect to success page
        $_SESSION['success'] = "Order placed successfully! We will contact you soon.";
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        header('Location: ../userdashboard/order_history.php');
        break;

    case 'esewa':
    // eSewa integration
    $esewa_url = getEsewaUrl();
    $success_url = "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear/payment/verify_payment.php?payment_method=esewa&oid=" . $order_id;
    $failure_url = "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear/payment/payment_failure.php?oid=" . $order_id;
    
    // Format amount to 2 decimal places
    $amount = number_format($total_amount, 2, '.', '');

    // Generate unique PID by combining order_id with timestamp
    $unique_pid = 'ESEWA_' . $order_id . '_' . time();

    // Add error logging
    error_log("eSewa Payment Initiated - Order ID: " . $order_id . ", PID: " . $unique_pid . ", Amount: " . $amount);
    ?>
    <form action="<?php echo $esewa_url; ?>" method="POST" id="esewaForm">
        <input value="<?php echo $amount; ?>" name="tAmt" type="hidden">
        <input value="<?php echo $amount; ?>" name="amt" type="hidden">
        <input value="0" name="txAmt" type="hidden">
        <input value="0" name="psc" type="hidden">
        <input value="0" name="pdc" type="hidden">
        <input value="<?php echo ESEWA_MERCHANT_ID; ?>" name="scd" type="hidden">
        <input value="<?php echo $unique_pid; ?>" name="pid" type="hidden">
        <input value="<?php echo $success_url; ?>" type="hidden" name="su">
        <input value="<?php echo $failure_url; ?>" type="hidden" name="fu">
    </form>
    <script>
        console.log('Submitting to eSewa...');
        document.getElementById('esewaForm').submit();
    </script>
    <?php
    break;

    case 'khalti':
        // Khalti integration
        $khalti_url = getKhaltiUrl();
        $success_url = "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear/payment/verify_payment.php?payment_method=khalti&oid=" . $order_id;
        $failure_url = "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear/payment/payment_failure.php?oid=" . $order_id;

        // Prepare the data for Khalti
        $data = [
            "return_url" => $success_url,
            "website_url" => "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear",
            "amount" => intval($total_amount * 100), // Convert to paisa and ensure it's an integer
            "purchase_order_id" => strval($order_id), // Ensure order_id is a string
            "purchase_order_name" => "Order #" . $order_id,
            "customer_info" => [
                "name" => $full_name,
                "email" => $email,
                "phone" => $phone
            ]
        ];

        // Debug log
        error_log("Khalti Request Data: " . json_encode($data));
        error_log("Khalti URL: " . $khalti_url);

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $khalti_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Key ' . KHALTI_SECRET_KEY,
            'Content-Type: application/json'
        ]);

        // Debug log for request headers
        error_log("Khalti Request Headers: Authorization: Key " . substr(KHALTI_SECRET_KEY, 0, 10) . "...");

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Debug log
        error_log("Khalti Response Status: " . $status_code);
        error_log("Khalti Response: " . $response);
        
        // If curl error occurs
        if(curl_errno($ch)) {
            error_log("Curl Error: " . curl_error($ch));
        }
        
        curl_close($ch);

        $response_data = json_decode($response, true);

        if ($status_code === 200 && isset($response_data['payment_url'])) {
            // Redirect to Khalti payment page
            header('Location: ' . $response_data['payment_url']);
            exit();
        } else {
            // Log the error and show detailed message for debugging
            error_log("Khalti Error Response: " . json_encode($response_data));
            $_SESSION['error'] = "Failed to initialize Khalti payment. Error: " . 
                               (isset($response_data['detail']) ? $response_data['detail'] : 'Unknown error');
            header('Location: ../cart/checkout.php');
            exit();
        }
        break;

    default:
        $_SESSION['error'] = "Invalid payment method";
        header('Location: ../cart/checkout.php');
        exit();
}
?>
