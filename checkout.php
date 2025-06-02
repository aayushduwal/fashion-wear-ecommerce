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
        .esewa-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #5E2590;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            width: 100%;
            justify-content: center;
        }
        .esewa-btn:hover {
            background: #4A1D6F;
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

        <form action="payment/esewa_payment.php" method="POST" class="payment-form">
            <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
            <button type="submit" class="esewa-btn">
                <img src="images/esewa-logo.png" alt="eSewa" style="height: 40px;">
                Pay with eSewa
            </button>
        </form>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html> 