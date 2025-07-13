<?php
session_start();
require_once('database/config.php');

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['order_id'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Successful - FashionWear</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
  .success-container {
    max-width: 600px;
    margin: 100px auto;
    text-align: center;
    padding: 40px;
    background: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  }

  .success-icon {
    color: #4CAF50;
    font-size: 64px;
    margin-bottom: 20px;
  }

  .success-message {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
  }

  .order-details {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: left;
  }

  .detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
  }

  .continue-shopping {
    display: inline-block;
    padding: 12px 30px;
    background: #5C2D91;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
    margin: 10px;
  }

  .continue-shopping:hover {
    background: #4A1D6F;
  }

  .view-orders {
    display: inline-block;
    padding: 12px 30px;
    background: #ff5722;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
    margin: 10px;
  }

  .view-orders:hover {
    background: #e64a19;
  }
  </style>
</head>

<body>
  <?php include('includes/header.php'); ?>

  <div class="success-container">
    <div class="success-icon">✓</div>
    <h1 class="success-message">Payment Successful!</h1>

    <div class="order-details">
      <h3>Order Details</h3>
      <div class="detail-row">
        <span><strong>Order ID:</strong></span>
        <span><?php echo htmlspecialchars($order['order_id']); ?></span>
      </div>
      <div class="detail-row">
        <span><strong>Amount:</strong></span>
        <span>NPR. <?php echo number_format($order['total_amount'], 2); ?></span>
      </div>
      <div class="detail-row">
        <span><strong>Payment Method:</strong></span>
        <span><?php echo ucfirst($order['payment_method']); ?></span>
      </div>
      <div class="detail-row">
        <span><strong>Status:</strong></span>
        <span><?php echo ucfirst($order['status']); ?></span>
      </div>
      <div class="detail-row">
        <span><strong>Date:</strong></span>
        <span><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span>
      </div>
    </div>

    <p>Thank you for your purchase. Your order has been placed successfully.</p>

    <a href="shop.php" class="continue-shopping">Continue Shopping</a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="userdashboard/order_history.php" class="view-orders">View Orders</a>
    <?php endif; ?>
  </div>

  <?php include('includes/footer.php'); ?>
</body>

</html>