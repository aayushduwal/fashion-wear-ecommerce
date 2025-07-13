<?php
session_start();
require_once('database/config.php');

// Get error message and provide user-friendly messages
$error_code = $_GET['error'] ?? 'payment_failed';
$order_id = $_GET['order_id'] ?? null;

// Convert error codes to user-friendly messages
$error_messages = [
    'order_not_found' => 'Order not found. Please try placing your order again.',
    'verification_failed' => 'Payment verification failed. Please contact support if money was deducted.',
    'payment_failed' => 'Payment could not be processed. Please try again.',
    'invalid_session' => 'Your session has expired. Please start over.',
    'insufficient_balance' => 'Insufficient balance in your account.',
    'cancelled' => 'Payment was cancelled by user.',
    'timeout' => 'Payment request timed out. Please try again.',
    'network_error' => 'Network connection failed. Please check your internet and try again.'
];

$error_message = $error_messages[$error_code] ?? $error_messages['payment_failed'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Failed - FashionWear</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
  .failure-container {
    max-width: 600px;
    margin: 50px auto;
    text-align: center;
    padding: 40px;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
  }

  .failure-icon {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    margin: 0 auto 25px;
    box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
  }

  .failure-message {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
  }

  .error-details {
    color: #6c757d;
    margin-bottom: 25px;
    font-size: 16px;
    line-height: 1.5;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #ff6b6b;
  }

  .order-info {
    background: #e3f2fd;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid #2196f3;
  }

  .help-text {
    color: #666;
    margin: 20px 0;
    font-size: 14px;
  }

  .retry-btn {
    display: inline-block;
    padding: 14px 28px;
    background: linear-gradient(135deg, #5C2D91, #7b1fa2);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
    margin: 8px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(92, 45, 145, 0.3);
  }

  .retry-btn:hover {
    background: linear-gradient(135deg, #4A1D6F, #6a1b9a);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(92, 45, 145, 0.4);
  }

  .back-btn {
    display: inline-block;
    padding: 14px 28px;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
    margin: 8px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  }

  .back-btn:hover {
    background: linear-gradient(135deg, #5a6268, #343a40);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(108, 117, 125, 0.4);
  }

  .contact-support {
    margin-top: 30px;
    padding: 20px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
  }

  @media (max-width: 768px) {
    .failure-container {
      margin: 20px;
      padding: 30px 20px;
    }

    .failure-message {
      font-size: 24px;
    }

    .retry-btn,
    .back-btn {
      display: block;
      margin: 10px auto;
      width: 200px;
    }
  }
  </style>
</head>

<body>
  <?php include('includes/header.php'); ?>

  <div class="failure-container">
    <div class="failure-icon">✗</div>
    <h1 class="failure-message">Payment Failed!</h1>

    <div class="error-details">
      <?php echo htmlspecialchars($error_message); ?>
    </div>

    <?php if ($order_id): ?>
    <div class="order-info">
      <strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?><br>
      <small>Save this ID for reference when contacting support</small>
    </div>
    <?php endif; ?>

    <p>Don't worry! Your order hasn't been placed yet. You can try again safely.</p>

    <div class="help-text">
      <strong>What can you do?</strong><br>
      • Check your internet connection<br>
      • Verify your payment details<br>
      • Try a different payment method<br>
      • Contact support if the problem persists
    </div>

    <a href="cart/checkout.php" class="retry-btn">🔄 Try Payment Again</a>
    <a href="shop.php" class="back-btn">🛍️ Continue Shopping</a>

    <div class="contact-support">
      <strong>Need Help?</strong><br>
      If you continue facing issues, please contact our support team.<br>
      <small>📧 Email: support@fashionwear.com | 📞 Phone: +977-1-XXXXXXX</small>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>
</body>

</html>