<?php
session_start();
require_once('../database/config.php');
require_once('../payment/payment_config.php');

// At the top of checkout.php
error_log("Session data: " . print_r($_SESSION, true));

// Function to get user details
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
$userDetails = null;

if (isset($_SESSION['user_id'])) {
    $userDetails = getUserDetails($conn, $_SESSION['user_id']);
    $_SESSION['email'] = $userDetails["email"];
}


if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// Get cart items and subtotal
$stmt = $conn->prepare("SELECT c.*, p.name, p.images FROM cart c 
                      LEFT JOIN products p ON c.product_id = p.id 
                      WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
$subtotal = 0;

while ($item = $result->fetch_assoc()) {
    $items[] = $item;
    $subtotal += $item['price'] * $item['quantity'];
}

// Delivery zones and charges
$delivery_zones = [
    'inside_ring' => ['name' => 'Inside Ring Road', 'charge' => 85],
    'outside_ring' => ['name' => 'Outside Ring Road', 'charge' => 100],
    'outside_valley' => ['name' => 'Outside Valley', 'charge' => 150]
];
?>
<!DOCTYPE html>
<html>

<head>
  <title>Checkout</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
  .checkout-container {
    display: flex;
    gap: 30px;
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
  }

  .shipping-details {
    flex: 2;
  }

  .order-summary {
    flex: 1;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    position: sticky;
    top: 20px;
    height: fit-content;
  }

  .item-list {
    margin-bottom: 20px;
  }

  .item {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
  }

  .item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
  }

  .delivery-options {
    margin: 20px 0;
  }

  .delivery-option {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
  }

  .delivery-option.selected {
    border-color: #4CAF50;
    background: #f0f9f0;
  }

  .proceed-btn {
    background: #ff5722;
    color: white;
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
  }

  .proceed-btn:hover {
    background: #f4511e;
  }

  .payment-methods {
    margin: 20px 0;
  }

  .payment-method {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
  }

  .payment-method:hover {
    border-color: #ff5722;
    background: #fff5f2;
  }

  .payment-method.selected {
    border-color: #ff5722;
    background: #fff5f2;
  }

  .payment-method img {
    width: 60px;
    height: auto;
  }

  .payment-method-details {
    flex: 1;
  }

  .payment-method-details h4 {
    margin: 0;
    font-size: 16px;
  }

  .payment-method-details p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
  }
  </style>
</head>

<body>
  <?php include('../includes/header.php'); ?>
  <?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger">
    <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']); // Clear the message after displaying
        ?>
  </div>
  <?php endif; ?>

  <div class="checkout-container">
    <div class="shipping-details">
      <h2>Shipping Information</h2>
      <form id="checkoutForm" action="../payment/process_payment.php" method="POST">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Phone</label>
          <input type="tel" name="phone" class="form-control" required pattern="^(98|97)[0-9]{8}$"
            title="Please enter a valid Nepalese phone number starting with 98 or 97">
        </div>


        <div class="form-group">
          <label>City</label>
          <input type="text" name="city" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Postal Code</label>
          <input type="text" name="postal_code" class="form-control" required pattern="[0-9]{5,10}" maxlength="10"
            title="Please enter a valid postal code (5-10 digits)">
        </div>

        <div class="form-group">
          <label>Delivery Zone</label>
          <?php foreach ($delivery_zones as $key => $zone): ?>
          <div class="delivery-option" data-charge="<?php echo $zone['charge']; ?>">
            <label>
              <input type="radio" name="delivery_zone" value="<?php echo $key; ?>" required>
              <?php echo $zone['name']; ?>
            </label>
            <span>NPR. <?php echo number_format($zone['charge'], 0); ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="form-group">
          <label>Detailed Address</label>
          <textarea name="detailed_address" class="form-control" required></textarea>
        </div>

        <div class="payment-methods">
          <h3>Select Payment Method</h3>

          <div class="payment-method" onclick="selectPayment('cod')">
            <img src="../images/cashondelivery.jpg" alt="Cash on Delivery">
            <div class="payment-method-details">
              <h4>Cash on Delivery</h4>
              <p>Pay with cash when your order arrives</p>
            </div>
            <input type="radio" name="payment_method" value="cod" required>
          </div>

          <div class="payment-method" onclick="selectPayment('esewa')">
            <img src="../images/esewa.png" alt="eSewa">
            <div class="payment-method-details">
              <h4>eSewa</h4>
              <p>Pay securely with your eSewa account</p>
            </div>
            <input type="radio" name="payment_method" value="esewa" required>
          </div>

          <!-- <div class="payment-method" onclick="selectPayment('khalti')">
                        <img src="../images/khalti.png" alt="Khalti">
                        <div class="payment-method-details">
                            <h4>Khalti</h4>
                            <p>Pay securely with your Khalti wallet</p>
                        </div>
                        <input type="radio" name="payment_method" value="khalti" required>
                    </div> -->
        </div>

        <button type="submit" class="proceed-btn">Continue</button>
      </form>
    </div>

    <div class="order-summary">
      <h3>Order Summary</h3>
      <div class="item-list">
        <?php foreach ($items as $item): ?>
        <div class="item">
          <img src="/fashionwear/uploads/products/<?php echo $item['images']; ?>" alt="<?php echo $item['name']; ?>">
          <div>
            <h4><?php echo $item['name']; ?></h4>
            <p>Quantity: <?php echo $item['quantity']; ?></p>
            <p>NPR. <?php echo number_format($item['price'], 0); ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="price-details">
        <div class="d-flex justify-content-between mb-2">
          <span>Items Total (<?php echo count($items); ?> Items)</span>
          <span>NPR. <?php echo number_format($subtotal, 0); ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Delivery Fee</span>
          <span id="delivery-fee">NPR. 0</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between mb-2">
          <strong>Total:</strong>
          <strong id="total-amount">NPR. <?php echo number_format($subtotal, 0); ?></strong>
        </div>
      </div>

    </div>
  </div>

  <script>
  function selectPayment(method) {
    document.querySelectorAll('input[name="payment_method"]').forEach(input => {
      if (input.value === method) {
        input.checked = true;
      }
    });
  }

  document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    // Log form data
    console.log('Form submitted');
    console.log('Payment method:', document.querySelector('input[name="payment_method"]:checked')?.value);

    // Make sure all required fields are filled
    const requiredFields = this.querySelectorAll('[required]');
    let allFilled = true;
    requiredFields.forEach(field => {
      if (!field.value) {
        allFilled = false;
        console.log('Missing required field:', field.name);
      }
    });

    if (!allFilled) {
      e.preventDefault();
      alert('Please fill in all required fields');
      return;
    }

    // If all fields are filled, let the form submit
    console.log('All fields filled, submitting form...');
    return true; // Allow form submission
  });

  document.querySelectorAll('.delivery-option').forEach(option => {
    option.addEventListener('click', function() {
      // Update delivery fee
      const charge = this.dataset.charge;
      document.getElementById('delivery-fee').textContent = 'NPR. ' + charge;

      // Update total
      const subtotal = <?php echo $subtotal; ?>;
      const total = subtotal + parseInt(charge);
      document.getElementById('total-amount').textContent = 'NPR. ' + total.toLocaleString();
    });
  });
  </script>

  <script>
  document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const phoneField = document.querySelector('input[name="phone"]');
    const nepalPhoneRegex = /^(98|97)[0-9]{8}$/;

    if (!nepalPhoneRegex.test(phoneField.value)) {
      e.preventDefault();
      alert('Please enter a valid Nepalese phone number starting with 98 or 97.');
      phoneField.focus();
      return false;
    }
  });
  </script>

  <?php include('../includes/footer.php'); ?>
</body>

</html>