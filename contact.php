<?php 
session_start();
include('database/config.php');

// Function to get user details
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}


// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userDetails = null;

if ($isLoggedIn) {
    $userDetails = getUserDetails($conn, $_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Contact us</title>
  <link rel="stylesheet" type="text/css" href="css/contact.css" />
  <link rel="stylesheet" href="css/index.css">
  <!-- CSS-link -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <!-- font of inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet" />
  <!-- closing the font of inter -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
  <!-- navbar -->
  <header>
    <div class="logo">
      <a href="index.php">
        <img src="images/logo.png" alt="Logo" />
      </a>
    </div>
    <nav class="nav-container">
      <ul class="navmenu">
        <li><a href="index.php">Home</a></li>
        <li>
          <a href="shop.php">Shop</a>
          <ul class="dropdown-menu">
            <li><a href="mens_collection.php">Men's Collection</a></li>
            <li><a href="womens_collection.php">Women's Collection</a></li>
            <li><a href="kids_collection.php">Kid's Collection</a></li>
          </ul>
        </li>


        <?php if ($isLoggedIn): ?>
        <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <?php endif; ?>

      </ul>
    </nav>
    <div class="nav-icon">
      <!-- <a href="#"><i class="bx bx-search"></i></a>
        <a href="#"><i class="bx bx-user"></i></a> -->
      <a href="/fashionwear/cart/cart.php"><i class="bx bx-cart"></i>
        <span id="cart-badge" class="cart-badge">0</span>
      </a>
      <div class="bx bx-menu" id="menu-icon"></div>
    </div>
    <div id="menu-icon">
      <i class="fa fa-bars"></i>
    </div>
  </header>
  <!-- end of navbar -->
  <div class="container">
    <div class="contact-box">
      <div class="left"></div>
      <div class="right">
        <h2>Contact Us</h2>
        <input type="text" class="field" placeholder="Your Name" />
        <input type="text" class="field" placeholder="Your Email" />
        <input type="text" class="field" placeholder="Phone" />
        <textarea placeholder="Message" class="field"></textarea>
        <button class="btn">Send</button>
        <a href="index.php" class="btn1">Back to Home</a>
      </div>
    </div>
  </div>
</body>

</html>