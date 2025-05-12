<?php
session_start();
include('../database/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    // Handle the case where user data couldn't be fetched
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();

// Get order details if viewing specific order
$order_details = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("
        SELECT o.*, oi.*, p.name as product_name, p.images
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
    $stmt->execute();
    $order_details = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order History - FASHIONWEAR</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../userdashboard/css/order_history.css">
  <!-- font of inter -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
</head>

<body>
  <header>
    <div class="logo">
      <a href="../index.php">
        <img src="../images/logo.png" width="80" class="footer-image" height="80" alt="FashionWear Logo" />
      </a>
    </div>
    <nav class="nav-container">
      <ul class="navmenu">
        <li><a href="/fashionwear/index.php">Home</a></li>
        <li>
          <a href="/fashionwear/shop.php">Shop</a>
          <ul class="dropdown-menu">
            <li><a href="/fashionwear/mens_collection.php">Men's Collection</a></li>
            <li><a href="/fashionwear/womens_collection.php">Women's Collection</a></li>
            <li><a href="/fashionwear/kids_collection.php">Kid's Collection</a></li>
          </ul>
        </li>
        <!-- <li><a href="/fashionwear/about.php">About</a></li>
                <li><a href="/fashionwear/contact.php">Contact</a></li> -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="/fashionwear/userdashboard/user_dashboard.php"><?php echo htmlspecialchars($user['username']); ?>'s
            Account</a></li>
        <li><a href="/fashionwear/logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="nav-icon">
      <?php if (isset($_SESSION['user_id'])): ?>
      <p>Welcome, <?php echo htmlspecialchars($user['username']); ?></p>
      <?php endif; ?>
      <a href="/fashionwear/cart/cart.php"><i class='bx bx-cart'></i>
        <!-- <span id="cart-badge" class="cart-badge">0</span> -->
      </a>
      <div id="menu-icon"><i class='bx bx-menu'></i></div>
    </div>
  </header>


  <div class="order-history-container">
    <?php if (isset($_GET['id']) && $order_details && $order_details->num_rows > 0): 
            $first_row = $order_details->fetch_assoc();
            $order_details->data_seek(0);
        ?>
    <a href="order_history.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Orders
    </a>
    <div class="order-details">
      <h2>Order #<?php echo $first_row['id']; ?></h2>
      <div class="order-info">
        <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($first_row['order_date'])); ?></p>
        <p><strong>Status:</strong>
          <span class="status status-<?php echo strtolower($first_row['status']); ?>">
            <?php echo ucfirst($first_row['status']); ?>
          </span>
        </p>
        <p><strong>Total Amount:</strong> NPR. <?php echo number_format($first_row['total_amount'], 2); ?></p>
      </div>
      <h3>Order Items</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php while($item = $order_details->fetch_assoc()): ?>
          <tr>
            <td><?php echo $item['product_name']; ?></td>
            <td>
              <img src="/fashionwear/uploads/products/<?php echo $item['images']; ?>"
                alt="<?php echo $item['product_name']; ?>" class="product-thumbnail">
            </td>
            <td>NPR. <?php echo number_format($item['price'], 2); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>NPR. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="orders-list">
      <h2>Order History</h2>
      <?php if ($orders->num_rows > 0): ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Total Items</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($order = $orders->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo date('F j, Y', strtotime($order['order_date'])); ?></td>
            <td><?php echo $order['total_items']; ?></td>
            <td>NPR. <?php echo number_format($order['total_amount'], 2); ?></td>
            <td>
              <span class="status status-<?php echo strtolower($order['status']); ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </td>
            <td>
              <a href="?id=<?php echo $order['id']; ?>" class="btn-view">
                <i class="fas fa-eye"></i> View Details
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p>No orders found.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</body>

</html>