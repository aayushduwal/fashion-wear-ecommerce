<?php
session_start();
require_once('../database/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['oid']) ? $_GET['oid'] : null;

if (!$order_id) {
    $_SESSION['error'] = "Invalid order reference.";
    header('Location: ../userdashboard/order_history.php');
    exit();
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    // Get delivery date from order or set default
    $delivery_date = isset($order['delivery_date']) ? $order['delivery_date'] : date('Y-m-d', strtotime('+7 days'));
    $formatted_date = date('F j, Y', strtotime($delivery_date));
} else {
    $_SESSION['error'] = "Order not found.";
    header('Location: ../userdashboard/order_history.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        /* Reset default styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
}

body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background-color: #f8f9fa;
    padding: 20px;
}

/* Container for the confirmation message */
.confirmation-container {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 400px;
    width: 100%;
}

h1 {
    color: #00a65a;  /* Green color from the image */
    font-size: 24px;
    margin-bottom: 15px;
    font-weight: 600;
}

p {
    color: #333;
    margin: 10px 0;
    line-height: 1.5;
    font-size: 16px;
}

/* Order details styling */
.order-details {
    margin: 20px 0;
    text-align: left;
}

.order-details p {
    margin: 8px 0;
}

strong {
    font-weight: 600;
    color: #222;
}

/* Links styling */
.action-links {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

a {
    color: #0066cc;  /* Blue color from the image */
    text-decoration: none;
    font-size: 15px;
    margin: 0 10px;
}

a:hover {
    text-decoration: underline;
}

/* Separator between links */
.action-links span {
    color: #ccc;
}
    </style>
</head>
<body>
<div class="confirmation-container">
    <h1>Thank You for Your Purchase!</h1>
    <p>Your order has been successfully confirmed.</p>

    <div class="order-details">
    <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
    <p><strong>Total Amount:</strong> Rs. <?php echo $order['total_amount']; ?></p>
    <p>We will deliver your order by <strong><?php echo $formatted_date; ?></strong></p>
    </div>

    <div class="action-links">
    <a href="../userdashboard/order_history.php">Go to My Orders</a> 
    <span>|</span>
    <a href="../index.php">Continue Shopping</a>
    </div>
</div>
</body>
</html>
