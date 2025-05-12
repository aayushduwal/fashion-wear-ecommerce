<?php
session_start();
include('../database/config.php');

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

// Function to get user details
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$userDetails = getUserDetails($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - FASHIONWEAR</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="css/user_dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
</head>

<body>
  <header>
    <div class="logo">
      <a href="../index.php">
        <img src="../images/logo.png" alt="Logo" />
      </a>
    </div>
    <nav class="nav-container">
      <ul class="navmenu">
        <li><a href="../index.php">Home</a></li>
        <li>
          <a href="../shop.php">Shop</a>
          <ul class="dropdown-menu">
            <li><a href="../mens_collection.php">Men's Collection</a></li>
            <li><a href="../womens_collection.php">Women's Collection</a></li>
            <li><a href="../kids_collection.php">Kid's Collection</a></li>
          </ul>
        </li>
        <!-- <li><a href="../About.php">About</a></li>
                <li><a href="../contact.php">Contact</a></li> -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="user_dashboard.php"><?php echo htmlspecialchars($userDetails['username']); ?>'s Account</a></li>
        <li><a href="../logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="../login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="nav-icon">
      <?php if (isset($_SESSION['user_id'])): ?>
      <p>Welcome, <?php echo htmlspecialchars($userDetails['username']); ?></p>
      <?php endif; ?>
      <a href="/fashionwear/cart/cart.php"><i class="bx bx-cart"></i>
        <span id="cart-badge" class="cart-badge">0</span>
      </a>
      <div class="bx bx-menu" id="menu-icon"></div>
    </div>
  </header>

  <div class="dashboard-container">
    <div class="user-info">
      <h2>Welcome, <?php echo htmlspecialchars($userDetails['username']); ?>!</h2>

      <div class="info-grid">
        <div class="info-item">
          <strong>Username</strong>
          <span><?php echo htmlspecialchars($userDetails['username']); ?></span>
        </div>
        <div class="info-item">
          <strong>Email</strong>
          <span><?php echo htmlspecialchars($userDetails['email']); ?></span>
        </div>
        <!-- <div class="info-item">
                    <strong>Account Type</strong>
                    <span><?php echo ucfirst(htmlspecialchars($userDetails['user_role'])); ?></span>
                </div> -->
        <div class="info-item">
          <strong>Member Since</strong>
          <span><?php echo date('F j, Y', strtotime($userDetails['created_at'])); ?></span>
        </div>
      </div>

      <div class="dashboard-actions">
        <a href="edit_profile.php">Edit Profile</a>
        <a href="change_password.php">Change Password</a>
        <a href="order_history.php">Order History</a>
      </div>
    </div>
  </div>

</body>

</html>