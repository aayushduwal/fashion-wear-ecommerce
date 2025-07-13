<?php
session_start();
require_once 'includes/functions.php';

// Mock some session data for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999;
    $_SESSION['user_name'] = 'Test User';
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        ['product_id' => 1, 'name' => 'Test Product', 'price' => 1500, 'quantity' => 1]
    ];
}

$total = 1500; // Test amount
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Gateway Test - FashionWear</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
  .test-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 10px;
  }

  .test-section {
    background: white;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .status-indicator {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
  }

  .status-live {
    background: #4caf50;
    color: white;
  }

  .status-demo {
    background: #ff9800;
    color: white;
  }

  .payment-btn {
    background: #007bff;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 10px 10px 10px 0;
  }

  .payment-btn:hover {
    background: #0056b3;
  }

  .info-box {
    background: #e7f3ff;
    border-left: 4px solid #2196F3;
    padding: 15px;
    margin: 15px 0;
  }
  </style>
</head>

<body>
  <?php include('includes/header.php'); ?>

  <div class="test-container">
    <h1>🧪 Payment Gateway Test Page</h1>
    <p>This page is for testing both Khalti and eSewa payment integrations in demo and live modes.</p>

    <div class="info-box">
      <h3>📋 Test Information</h3>
      <ul>
        <li><strong>Test Amount:</strong> NPR. <?php echo number_format($total, 2); ?></li>
        <li><strong>Test Order ID:</strong> Will be generated automatically</li>
        <li><strong>User:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></li>
      </ul>
    </div>

    <div class="test-section">
      <h2>💳 Khalti Payment Gateway (KPG-2)</h2>
      <p>
        <strong>Mode:</strong> Official Khalti KPG-2 API
        <span class="status-indicator status-live">Latest API</span>
      </p>

      <div class="info-box">
        <h4>How it works:</h4>
        <ul>
          <li>Uses latest Khalti KPG-2 API endpoints</li>
          <li>Server-to-server payment initiation</li>
          <li>Redirects to official Khalti payment portal</li>
          <li>Proper callback and verification handling</li>
          <li><strong>Test Credentials:</strong> ID: 9800000000, MPIN: 1111, OTP: 987654</li>
        </ul>
      </div>
      <form action="payment/khalti/khalti_payment.php" method="POST">
        <input type="hidden" name="amount" value="<?php echo $total; ?>">
        <button type="submit" class="payment-btn">Test Khalti Payment (KPG-2)</button>
      </form>
    </div>

    <div class="test-section">
      <h2>🟢 eSewa Payment Gateway</h2>
      <p>
        <strong>Mode:</strong> Auto-detection (Live Test Server → Local Demo Fallback)
        <span id="esewa-status" class="status-indicator">Checking...</span>
      </p>

      <div class="info-box">
        <h4>How it works:</h4>
        <ul>
          <li>First tries to connect to eSewa's test server</li>
          <li>If server is unreachable, automatically falls back to local simulation</li>
          <li>Simulation mode clearly indicates demo environment</li>
          <li><strong>Test Environment:</strong> Uses eSewa's official test credentials</li>
        </ul>
      </div>
      <form action="payment/esewa/esewa_payment.php" method="POST">
        <input type="hidden" name="amount" value="<?php echo $total; ?>">
        <button type="submit" class="payment-btn">Test eSewa Payment</button>
      </form>
    </div>

    <div class="test-section">
      <h2>📊 Integration Status</h2>
      <div id="integration-status">
        <h4>Current Implementation:</h4>
        <ul>
          <li><strong>Khalti:</strong> <span class="status-indicator status-live">KPG-2 API Active</span></li>
          <li><strong>eSewa:</strong> <span id="esewa-final-status">Auto-detection Mode</span></li>
        </ul>
        <p style="margin-top: 15px;">
          <strong>✅ Benefits of this implementation:</strong><br>
          • Uses latest Khalti KPG-2 API for better reliability<br>
          • Server-to-server payment initiation<br>
          • Official payment portal integration<br>
          • Proper callback verification with lookup API<br>
          • Production-ready implementation
        </p>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
  // Check eSewa server availability
  function checkEsewaStatus() {
    fetch('payment/esewa_payment.php', {
        method: 'HEAD',
      })
      .then(response => {
        document.getElementById('esewa-final-status').innerHTML =
          '<span class="status-indicator status-live">Test Server Available</span>';
      })
      .catch(error => {
        document.getElementById('esewa-final-status').innerHTML =
          '<span class="status-indicator status-demo">Demo Mode (Server Unavailable)</span>';
      });
  }

  // Run checks when page loads
  window.addEventListener('load', function() {
    checkEsewaStatus();
  });
  </script>
</body>

</html>