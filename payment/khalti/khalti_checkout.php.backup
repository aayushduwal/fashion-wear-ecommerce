<?php
session_start();
require_once('payment_config.php');

// Check if Khalti order data exists
if (!isset($_SESSION['khalti_order'])) {
    header("Location: ../checkout.php?error=invalid_session");
    exit();
}

$khalti_data = $_SESSION['khalti_order'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khalti Payment - FashionWear</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
  .payment-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 30px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #f9f9f9;
  }

  .khalti-logo {
    width: 150px;
    margin-bottom: 20px;
  }

  .payment-details {
    margin: 20px 0;
    padding: 20px;
    background: white;
    border-radius: 8px;
  }

  .pay-button {
    background: #5C2D91;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin: 10px;
  }

  .pay-button:hover {
    background: #4A1D6F;
  }

  .cancel-button {
    background: #666;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin: 10px;
  }

  .cancel-button:hover {
    background: #555;
  }
  </style>
</head>

<body>
  <?php include('../includes/header.php'); ?>

  <div class="payment-container">
    <img src="../images/khalti-logo.png" alt="Khalti" class="khalti-logo">
    <h2>Complete Your Payment</h2>

    <div class="payment-details">
      <h3>Order Details</h3>
      <p><strong>Order ID:</strong> <?php echo htmlspecialchars($khalti_data['order_id']); ?></p>
      <p><strong>Amount:</strong> NPR. <?php echo number_format($khalti_data['amount'] / 100, 2); ?></p>
    </div>

    <button id="payment-button" class="pay-button">Pay with Khalti</button>
    <button onclick="window.location.href='../cart/checkout.php'" class="cancel-button">Cancel</button>
  </div>
  <?php include('../includes/footer.php'); ?>

  <!-- Khalti Checkout Script -->
  <script src="https://checkout.khalti.com/js/khalti-checkout.js"></script>

  <script>
  // Initialize Khalti when page loads
  window.addEventListener('load', function() {
    setTimeout(function() {
      if (typeof KhaltiCheckout === 'undefined') {
        console.error('Khalti CDN failed to load');

        // Show simple error message
        const paymentButton = document.getElementById('payment-button');
        paymentButton.disabled = true;
        paymentButton.style.background = '#ccc';
        paymentButton.style.cursor = 'not-allowed';
        paymentButton.innerHTML = 'Khalti Unavailable';

        alert('Khalti payment system is unavailable. Please try another payment method.');
        return;
      }

      console.log('Khalti loaded successfully');

      var config = {
        "publicKey": "<?php echo getKhaltiPublicKey(); ?>",
        "productIdentity": "<?php echo $khalti_data['order_id']; ?>",
        "productName": "FashionWear Order",
        "productUrl": "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/fashionwear/'; ?>",
        "paymentPreference": [
          "KHALTI",
          "EBANKING",
          "MOBILE_BANKING",
          "CONNECT_IPS",
          "SCT"
        ],
        "eventHandler": {
          onSuccess(payload) {
            console.log('Payment Success:', payload);

            // Verify payment with server
            fetch('khalti_verify.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                  token: payload.token,
                  amount: payload.amount,
                  order_id: "<?php echo $khalti_data['order_id']; ?>"
                })
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  window.location.href = 'success.php?order_id=' + data.order_id;
                } else {
                  window.location.href = '../payment_failed.php?error=' + encodeURIComponent(data.message);
                }
              })
              .catch(error => {
                console.error('Verification Error:', error);
                window.location.href = '../payment_failed.php?error=verification_failed';
              });
          },
          onError(error) {
            console.log('Payment Error:', error);
            window.location.href = '../payment_failed.php?error=' + encodeURIComponent(error.detail ||
              'Payment failed');
          },
          onClose() {
            console.log('Payment widget closed');
          }
        }
      };

      var checkout = new KhaltiCheckout(config);

      // Attach click handler to payment button
      document.getElementById("payment-button").addEventListener("click", function() {
        console.log('Opening Khalti payment widget...');

        if (<?php echo $khalti_data['amount']; ?> < 1000) {
          alert('Minimum payment amount is NPR. 10');
          return;
        }

        // Show the Khalti payment widget
        checkout.show({
          amount: <?php echo $khalti_data['amount']; ?>
        });
      });
    }, 2000);
  });
  </script>
</body>

</html>