<?php
session_start();
require_once('database/config.php');

if (!isset($_GET['transaction_id'])) {
    header("Location: index.php");
    exit();
}

$transaction_id = $_GET['transaction_id'];
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
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
        .transaction-id {
            color: #666;
            margin-bottom: 30px;
        }
        .continue-shopping {
            display: inline-block;
            padding: 12px 30px;
            background: #5C2D91;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .continue-shopping:hover {
            background: #4A1D6F;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1 class="success-message">Payment Successful!</h1>
        <p class="transaction-id">Transaction ID: <?php echo htmlspecialchars($transaction_id); ?></p>
        <p>Thank you for your purchase. Your order has been placed successfully.</p>
        <a href="shop.php" class="continue-shopping">Continue Shopping</a>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html> 