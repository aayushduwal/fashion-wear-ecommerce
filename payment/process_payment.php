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
    error_log("Customer query preparation failed: " . $conn->error . " SQL State: " . $conn->sqlstate);
    $_SESSION['error'] = "System error. Please try again later.";
    header('Location: ../cart/checkout.php');
    exit();
}

$stmt->bind_param("ssssss", $full_name, $email, $phone, $detailed_address, $city, $postal_code);

if (!$stmt->execute()) {
    error_log("Customer creation failed: " . $stmt->error . " SQL State: " . $stmt->sqlstate);
    error_log("Customer Data - Name: $full_name, Email: $email, Phone: $phone, Address: $detailed_address, City: $city, Postal: $postal_code");
    $_SESSION['error'] = "Failed to save customer information. Please try again.";
    header('Location: ../cart/checkout.php');
    exit();
}

// Get the customer ID - either from insert or existing record
if ($stmt->insert_id) {
    $customer_id = $stmt->insert_id;
} else {
    // If no insert_id, customer already exists, so get their ID
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $customer_id = $row['id'];
    } else {
        error_log("Failed to get customer ID for email: " . $email);
        $_SESSION['error'] = "Failed to process customer information. Please try again.";
        header('Location: ../cart/checkout.php');
        exit();
    }
}

error_log("Customer ID after insertion/retrieval: " . $customer_id);

// Verify customer exists before proceeding
$stmt = $conn->prepare("SELECT id FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->fetch_assoc()) {
    error_log("Customer ID verification failed for ID: " . $customer_id);
    $_SESSION['error'] = "Customer verification failed. Please try again.";
    header('Location: ../cart/checkout.php');
    exit();
}

// Prepare shipping address
$shipping_address = "Name: $full_name\n";
$shipping_address .= "Phone: $phone\n";
$shipping_address .= "Address: $detailed_address\n";
$shipping_address .= "City: $city\n";
$shipping_address .= "Postal Code: $postal_code\n";
$shipping_address .= "Delivery Zone: $delivery_zone";

$delivery_date = date('Y-m-d', strtotime('+7 days'));

// Debug logging before order creation
error_log("Attempting to create order with the following data:");
error_log("User ID: " . $user_id);
error_log("Customer ID: " . $customer_id);
error_log("Total Amount: " . $total_amount);
error_log("Payment Method: " . $payment_method);
error_log("Delivery Zone: " . $delivery_zone);
error_log("Delivery Date: " . $delivery_date);

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    $_SESSION['error'] = "Database connection error. Please try again.";
    header('Location: ../cart/checkout.php');
    exit();
}

// Create order
// Generate unique order ID
$order_identifier = 'ORD_' . date('Ymd') . '_' . time() . '_' . $user_id;

$stmt = $conn->prepare("INSERT INTO orders (
    order_id,
    user_id, 
    customer_id, 
    total_amount, 
    status, 
    shipping_address, 
    payment_method, 
    delivery_zone,
    delivery_date
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    error_log("Order query preparation failed: " . $conn->error . " SQL State: " . $conn->sqlstate);
    $_SESSION['error'] = "System error. Please try again later.";
    header('Location: ../cart/checkout.php');
    exit();
}

$status = 'pending';
$stmt->bind_param("siidsssss", 
    $order_identifier,
    $user_id, 
    $customer_id, 
    $total_amount, 
    $status,
    $shipping_address, 
    $payment_method, 
    $delivery_zone,
    $delivery_date
);

if (!$stmt->execute()) {
    error_log("Order creation failed: " . $stmt->error . " SQL State: " . $stmt->sqlstate);
    error_log("SQL Query: INSERT INTO orders (user_id, customer_id, total_amount, status, shipping_address, payment_method, delivery_zone, delivery_date) VALUES ($user_id, $customer_id, $total_amount, '$status', '$shipping_address', '$payment_method', '$delivery_zone', '$delivery_date')");
    $_SESSION['error'] = "Failed to create order. Error: " . $stmt->error;
    header('Location: ../cart/checkout.php');
    exit();
}

$order_id = $stmt->insert_id;
if (!$order_id) {
    error_log("Failed to get order ID after insertion. Last Error: " . $conn->error);
    $_SESSION['error'] = "Failed to create order. Please try again.";
    header('Location: ../cart/checkout.php');
    exit();
}

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
    $success_url = "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear/payment/esewa/verify_payment.php?payment_method=esewa&oid=" . $order_id;
    $failure_url = "http://" . $_SERVER['HTTP_HOST'] . "/fashionwear/payment/esewa/failure.php?oid=" . $order_id;
    
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
        // Store necessary data in session for the new Khalti API
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;
        
        // Clear cart before payment (we'll restore if payment fails)
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Store order ID in session so callback can find it
        $_SESSION['khalti_order_id'] = $order_id;
        
        // Create a form to POST to the new Khalti payment handler
        ?>
<form action="khalti/khalti_payment.php" method="POST" id="khaltiForm">
  <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
  <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
</form>
<script>
console.log('Redirecting to Khalti KPG-2...');
document.getElementById('khaltiForm').submit();
</script>
<?php
        break;

    default:
        $_SESSION['error'] = "Invalid payment method";
        header('Location: ../cart/checkout.php');
        exit();
}
?>