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
  <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
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
        <img src="images/esewa-logo.png" alt="eSewa">
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
          <span>Pay securely with your Khalti wallet</span>
        </div>
      </div>
      <button type="submit" class="payment-btn" style="background: #ff5722; color: white;">Continue</button>
    </form>

    <button id="payment-button" class="payment-btn khalti-btn" style="display:none; margin-top: 10px;">
      <img src="images/khalti.png" alt="Khalti" style="height: 40px;">
      Pay with Khalti
    </button>

    <form id="esewa-form" action="payment/esewa_payment.php" method="POST" style="display:none;">
      <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    </form>
    <form id="cod-form" action="payment/cod_payment.php" method="POST" style="display:none;">
      <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    </form>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
  var config = {
    "publicKey": "<?php echo KHALTI_PUBLIC_KEY; ?>", // Replace with your actual Khalti public key
    "productIdentity": "<?php echo $_SESSION['user_id']; ?>",
    "productName": "FashionWear Order",
    "productUrl": window.location.href,
    "paymentPreference": [
      "KHALTI",
      "EBANKING",
      "MOBILE_BANKING",
      "CONNECT_IPS",
      "SCT"
    ],
    "eventHandler": {
      onSuccess(payload) {
        // Handle success
        fetch('payment/khalti_payment.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              token: payload.token,
              amount: payload.amount
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              window.location.href = 'payment_success.php?transaction_id=' + data.transaction_id;
            } else {
              alert('Payment verification failed. Please try again.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Payment failed. Please try again.');
          });
      },
      onError(error) {
        console.log(error);
        alert('Payment failed. Please try again.');
      },
      onClose() {
        console.log('Widget is closing');
      }
    }
  };

  var checkout = new KhaltiCheckout(config);
  var paymentBtn = document.getElementById("payment-button");
  paymentBtn.onclick = function() {
    checkout.show({
      amount: <?php echo $total_amount * 100; ?>
    }); // Amount in paisa
  }

  // Payment method selection logic
  const paymentForm = document.getElementById('payment-method-form');
  const esewaForm = document.getElementById('esewa-form');
  const codForm = document.getElementById('cod-form');
  const khaltiBtn = document.getElementById('payment-button');

  paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const selected = document.querySelector('input[name="payment_method"]:checked').value;
    if (selected === 'esewa') {
      esewaForm.submit();
    } else if (selected === 'cod') {
      codForm.submit();
    } else if (selected === 'khalti') {
      khaltiBtn.style.display = 'block';
      khaltiBtn.click();
    }
  });

  // Show/hide Khalti button on radio change
  document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      if (this.value === 'khalti') {
        khaltiBtn.style.display = 'block';
      } else {
        khaltiBtn.style.display = 'none';
      }
    });
  });
  // Hide Khalti button by default
  khaltiBtn.style.display = 'none';
  </script>
</body>

</html>