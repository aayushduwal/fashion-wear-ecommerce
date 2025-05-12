<?php
session_start();
require_once('../database/config.php');

// Check if user is logged in
$isloggedIn = isset($_SESSION['user_id']);
$username = $isloggedIn ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <!-- Custom Styles -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- font of inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
  /* closing the font of inter */

  /* -------------------- Navbar Start -------------------- */

  header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 5%;
    background-color: white;
    color: var(--dark-background);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .logo {
    flex: 1;
  }

  .logo img {
    height: 40px;
    width: auto;
  }

  .nav-container {
    flex: 1;
    display: flex;
    justify-content: center;
    position: relative;
  }

  .navmenu {
    display: flex;
    align-items: center;
    list-style: none;
  }

  .navmenu li {
    margin: 0 15px;
    list-style: none;
  }

  .navmenu a {
    text-decoration: none;
    color: var(--dark-background);
    font-size: 22px;
    font-weight: 450;
    transition: color 0.3s ease, transform 0.3s ease;
  }

  .navmenu a:hover {
    color: #ff5733;
    transform: translateY(-2px);
  }

  /* Start of CSS for dropdown in shop */
  .navmenu>li {
    position: relative;
  }

  /* .dropdown {
    display: none;
    position: absolute;
    left: 0;
    top: 100%;
    width: max-content;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1;
  } */

  .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background-color: white;
    display: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 10px 0;
    z-index: 100;
  }


  .navmenu li:hover .dropdown {
    display: block;
  }

  .dropdown li {
    display: block;
  }

  .dropdown a {
    padding: 10px 20px;
    color: black;
    background-color: white;
    white-space: nowrap;
  }

  /* End of CSS for dropdown in shop */

  .nav-icon {
    flex: 1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 20px;
  }

  .nav-icon a {
    display: flex;
    align-items: center;
    text-decoration: none;
  }

  .nav-icon i {
    color: black;
    font-size: 24px;
    transition: color 0.3s ease, transform 0.3s ease;
  }

  .nav-icon i:hover {
    color: #ff5733;
    transform: scale(1.1);
  }

  #menu-icon {
    font-size: 30px;
    color: var(--dark-background);
    cursor: pointer;
    display: none;
  }

  .container {
    margin-top: 60px;
  }


  .cart-badge {
    position: relative;
    top: -10px;
    right: 5px;
    background-color: #ff5733;
    color: white;
    font-size: 12px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    visibility: hidden;
  }

  .cart-summary {
    position: sticky;
    bottom: 0;
    right: 0;
    background: white;
    padding: 20px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .total-amount {
    font-size: 1.2em;
    font-weight: bold;
  }

  .checkout-button .btn {
    padding: 10px 20px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-weight: bold;
  }

  .checkout-button .btn:hover {
    background: #45a049;
  }

  .cart-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .cart-item {
    display: flex;
    border-bottom: 1px solid #eee;
    padding: 20px 0;
    gap: 20px;
  }

  .cart-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
  }

  .item-details {
    flex: 1;
  }

  .remove-btn {
    background: #ff4444;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 4px;
  }

  .empty-cart {
    text-align: center;
    padding: 40px;
    font-size: 1.2em;
    color: #666;
  }
  </style>
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
        <?php if($isloggedIn): ?>
        <?php if (isset($_SESSION['is_admin'])): ?>
        <li><a href="dashboard/admin_dashboard.php" class="dashboard-btn">Dashboard</a></li>
        <?php else: ?>
        <li><a style="white-space: nowrap;word-break: keep-all;display:block;"
            href="/fashionwear/userdashboard/user_dashboard.php">
            <?php 
                // Check if userDetails exists before accessing username
                if (isset($userDetails['username'])) {
                    echo htmlspecialchars($userDetails['username']); 
                } else {
                    echo htmlspecialchars($username); // Fallback to session username
                }
            ?>'s Account</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <div class="nav-icon">
      <?php if($isloggedIn): ?>
      <p>Logged in as <u><strong><?php echo htmlspecialchars($username); ?></strong></u></p>
      <?php else: ?>
      <a href="../login.php"><i class="bx bx-user"></i></a>
      <?php endif; ?>
      <a href="../cart/cart.php"><i class="bx bx-cart"></i>
        <span id="cart-badge" class="cart-badge">0</span>
      </a>
      <div class="bx bx-menu" id="menu-icon"></div>
    </div>
  </header>
  <!-- Cart Container -->
  <div class="container">
    <h1 class="text-center mb-4">Your Cart</h1>
    <div class="cart-container">
      <?php
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $total = 0; // Initialize total
            
            // Fetch cart items
            $stmt = $conn->prepare("SELECT c.*, p.images FROM cart c 
                                  LEFT JOIN products p ON c.product_id = p.id 
                                  WHERE c.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($item = $result->fetch_assoc()) {
                    $subtotal = $item['price'] * $item['quantity']; // Calculate subtotal
                    $total += $subtotal; // Add to total
                    ?>
      <div class="cart-item">
        <img src="/fashionwear/uploads/products/<?php echo $item['images']; ?>" alt="<?php echo $item['product_name']; ?>">
        <div class="item-details">
          <h3><?php echo $item['product_name']; ?></h3>
          <p>Price: NPR. <?php echo number_format($item['price'], 0); ?></p>
          <p>Quantity: <?php echo $item['quantity']; ?></p>
          <p>Subtotal: NPR <?php echo number_format($subtotal, 0); ?></p>
          <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="remove-btn">Remove</button>
        </div>
      </div>
      <?php
                }
                ?>
      <div class="cart-summary">
        <div class="total-amount">
          <h3>Total Amount: NPR <?php echo number_format($total, 0); ?></h3>
        </div>
        <div class="checkout-button">
          <a href="checkout.php" class="checkout-btn"
            style="background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Proceed to Checkout
          </a>
        </div>
      </div>
      <?php
            } else {
                echo "<p class='empty-cart'>Your cart is empty</p>";
            }
        } else {
            echo "<p class='empty-cart'>Please login to view your cart</p>";
        }
        ?>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Cart Script -->
  <script src="addToCart.js"></script>
  <script>
  let cart = [];

  function displayCart() {

    $.post('cart_handler.php', {
      action: 'fetch'
    }, function(response) {
      const cart = JSON.parse(response);
      const cartContainer = $('#cart-container');
      // const cartCount = $('#cart-count');
      // updateCartBadge();

      // cartCount.text(cart.length);
      if (cart.length === 0) {
        cartContainer.html(`<div class="col-12 text-center"><p class="text-muted">Your cart is empty.</p></div>`);
        return;
      }

      const cartItemsHtml = cart.map(item => `
          <div class="col-md-6">
            <div class="cart-item border p-3 mb-3">
              <h5>${item.product_name}</h5>
              <p>Price: <strong>NPR <?php echo number_format($item['price'], 0); ?></strong></p>
              <p>Quantity: <strong>${item.quantity}</strong></p>
              <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">Remove</button>
            </div>
          </div>
        `).join('');
      cartContainer.html(cartItemsHtml);
    });
  }

  // function removeFromCart(id) {
  //   $.post('cart_handler.php', { action: 'remove', id: id }, function (response) {
  //     displayCart();
  //     updateCartBadge();
  //     alert(response);
  //   });
  // }

  $(document).ready(displayCart);

  $.post('cart_handler.php', {
    action: 'count'
  }, function(response) {
    const totalItems = parseInt(response);
    const cartBadge = $("#cart-badge");
    if (totalItems > 0) {
      cartBadge.css("visibility", "visible").text(totalItems);
    } else {
      cartBadge.css("visibility", "hidden");
    }
  });
  </script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  function removeFromCart(cartId) {
    if (confirm('Are you sure you want to remove this item?')) {
      $.post('/fashionwear/cart/cart_handler.php', {
        action: 'remove',
        id: cartId
      }, function(response) {
        // Reload the page after successful removal
        location.reload();
      }).fail(function(xhr, status, error) {
        console.error("Error removing item:", error);
        alert("Error removing item from cart");
      });
    }
  }
  </script>
  <script>
  $(document).ready(function() {
    // Remove any click handlers that might be attached to checkout button
    $('.checkout-btn').off('click');

    // Only attach click handlers to add-to-cart buttons
    $('.add-to-cart-btn').click(function(e) {
      e.preventDefault();
      addToCart();
    });
  });
  </script>
</body>

</html>