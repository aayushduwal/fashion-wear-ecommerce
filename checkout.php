<?php
session_start();
require_once('database/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get cart items
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_amount = 0;

// Calculate total
foreach ($cart as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - FashionWear</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
  .checkout-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
  }

  .payment-form {
    margin: 20px 0;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
  }

  .payment-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
    width: 100%;
    justify-content: center;
    margin-bottom: 10px;
  }

  .esewa-btn {
    background: #5E2590;
  }

  .esewa-btn:hover {
    background: #4A1D6F;
  }

  .khalti-btn {
    background: #5C2D91;
  }

  .khalti-btn:hover {
    background: #4A1D6F;
  }

  .cod-btn {
    background: #ff5722;
  }

  .cod-btn:hover {
    background: #e64a19;
  }

  .order-summary {
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
  }

  .error-message {
    color: red;
    margin: 10px 0;
  }

  .payment-option {
    display: flex;
    align-items: center;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
  }

  .payment-option input {
    margin-right: 15px;
  }

  .payment-option img {
    height: 40px;
    margin-right: 15px;
  }

  .payment-option div {
    font-size: 14px;
    color: #555;
  }
  </style>
</head>

<body>
  <?php include('includes/header.php'); ?>

  <div class="checkout-container">
    <h1>Checkout</h1>

    <?php if (isset($_GET['error'])): ?>
    <div class="error-message">
      <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
    <?php endif; ?>

    <div class="order-summary">
      <h2>Order Summary</h2>
      <p>Total Amount: NPR. <?php echo number_format($total_amount, 2); ?></p>
    </div>

    <form id="payment-method-form">
      <h2>Select Payment Method</h2>
      <div class="payment-option">
        <input type="radio" id="cod" name="payment_method" value="cod" checked>
        <img src="images/cashondelivery.jpg" alt="Cash on Delivery">
        <div>
          <strong>Cash on Delivery</strong><br>
          <span>Pay with cash when your order arrives</span>
        </div>
      </div>
      <div class="payment-option">
        <input type="radio" id="esewa" name="payment_method" value="esewa">
        <img src="images/esewa.png" alt="eSewa">
        <div>
          <strong>eSewa</strong><br>
          <span>Pay securely with your eSewa account</span>
        </div>
      </div>
      <div class="payment-option">
        <input type="radio" id="khalti" name="payment_method" value="khalti">
        <img src="images/khalti-logo.png" alt="Khalti">
        <div>
          <strong>Khalti</strong><br>
          <span>Pay with Khalti - Digital Wallet</span>
        </div>
      </div>
      <button type="submit" class="payment-btn" style="background: #ff5722; color: white;">Continue</button>
    </form>

    <form id="esewa-form" action="payment/esewa_payment.php" method="POST" style="display:none;">
      <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    </form>
    <form id="khalti-form" action="payment/khalti/khalti_payment.php" method="POST" style="display:none;">
      <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    </form>
    <form id="cod-form" action="payment/cod_payment.php" method="POST" style="display:none;">
      <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    </form>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
  // Payment method selection logic
  const paymentForm = document.getElementById('payment-method-form');
  const esewaForm = document.getElementById('esewa-form');
  const khaltiForm = document.getElementById('khalti-form');
  const codForm = document.getElementById('cod-form');

  paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const selected = document.querySelector('input[name="payment_method"]:checked').value;
    if (selected === 'esewa') {
      esewaForm.submit();
    } else if (selected === 'khalti') {
      khaltiForm.submit();
    } else if (selected === 'cod') {
      codForm.submit();
    }
  });
  </script>
</body>

</html>