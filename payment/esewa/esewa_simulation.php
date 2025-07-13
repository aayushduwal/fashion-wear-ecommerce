<?php
session_start();

if (!isset($_SESSION['esewa_simulation'])) {
    header("Location: ../checkout.php?error=invalid_session");
    exit();
}

$simulation_data = $_SESSION['esewa_simulation'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>eSewa Payment Simulation - FashionWear</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
  .simulation-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 40px;
    text-align: center;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    position: relative;
  }

  .demo-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #ff9800;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    transform: rotate(15deg);
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
  }

  .esewa-logo {
    width: 150px;
    margin-bottom: 20px;
    background: white;
    padding: 10px;
    border-radius: 8px;
  }

  .simulation-message {
    background: rgba(255, 165, 0, 0.9);
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 5px solid #ff9800;
  }

  .payment-details {
    background: rgba(255, 255, 255, 0.2);
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
  }

  .btn {
    padding: 12px 25px;
    margin: 10px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
  }

  .success-btn {
    background: #fff;
    color: #4CAF50;
  }

  .success-btn:hover {
    background: #f0f0f0;
  }

  .fail-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
  }

  .fail-btn:hover {
    background: rgba(255, 255, 255, 0.3);
  }
  </style>
</head>

<body>
  <?php include('../includes/header.php'); ?>

  <div class="simulation-container">
    <div class="demo-badge">🎓 DEMO MODE</div>
    
    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
      <h2 style="color: #4CAF50; margin: 0;">eSewa Payment Gateway</h2>
      <p style="color: #666; margin: 5px 0 0 0;">Simulation Mode for Class Project</p>
    </div>

    <div class="simulation-message">
      <h3>🎓 Class Project Simulation</h3>
      <p><strong>⚠️ Demo Environment:</strong> <?php echo htmlspecialchars($simulation_data['message']); ?></p>
      <p style="font-size: 14px; margin-top: 10px;">
        ℹ️ This simulation is used when eSewa's test server is not reachable. In a real environment, this would connect to eSewa's live servers.
      </p>
    </div>

    <div class="payment-details">
      <h3>Payment Details</h3>
      <p><strong>Order ID:</strong> <?php echo htmlspecialchars($simulation_data['order_id']); ?></p>
      <p><strong>Amount:</strong> NPR. <?php echo number_format($simulation_data['amount'], 2); ?></p>
    </div>

    <p>Choose the payment result for demonstration:</p>

    <button onclick="simulateSuccess()" class="btn success-btn">
      ✅ Simulate Successful Payment
    </button>

    <button onclick="simulateFailure()" class="btn fail-btn">
      ❌ Simulate Payment Failure
    </button>
  </div>

  <?php include('../includes/footer.php'); ?>

  <script>
  function simulateSuccess() {
    // Simulate successful eSewa payment
    window.location.href =
      'success.php?order_id=<?php echo $simulation_data['order_id']; ?>&amount=<?php echo $simulation_data['amount']; ?>&simulation=esewa';
  }

  function simulateFailure() {
    // Simulate failed eSewa payment
    window.location.href = '../payment_failed.php?error=cancelled&order_id=<?php echo $simulation_data['order_id']; ?>';
  }
  </script>
</body>

</html>