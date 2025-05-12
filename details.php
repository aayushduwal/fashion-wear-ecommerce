<?php
session_start();
include('database/config.php');

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
}

// Check if ID is provided and fetch product details
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// If product not found
if (!$product) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($product['name']); ?> | FashionWear</title>
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- font of inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- closing the font of inter -->
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  /* font of inter */
  body {
    margin: 0;

    font-family: "Inter", sans-serif;
    font-weight: 400;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* closing the font of inter */

  /* -------------------- Navbar Start -------------------- */
  header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 5%;
    background-color: white;
    color: black;
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
  }

  .navmenu {
    display: flex;
    align-items: center;
    list-style: none;
  }

  .navmenu li {
    position: relative;
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
    color: var(--primary);
    transform: translateY(-2px);
  }

  /* Start of CSS for dropdown in shop */
  .navmenu>li {
    position: relative;
  }

  .dropdown {
    display: none;
    position: absolute;
    left: 0;
    top: 100%;
    width: max-content;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1;
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
    color: black;
    cursor: pointer;
  }

  /* -------------------- Navbar End -------------------- */

  /* Start of product detail */
  .product-container {
    margin-top: 60px;
  }

  .small-container {
    margin-top: 0;
    max-width: 1200px;
    padding: 20px;
    margin-left: auto;
    margin-right: auto;
  }

  .row {
    display: flex;
    flex-wrap: wrap;
  }

  .col-2 {
    flex: 50%;
    padding: 20px;
  }

  .single-product h4 {
    margin: 20px 0;
    font-size: 22px;
    font-weight: bold;
  }

  .single-product select {
    display: block;
    padding: 10px;
    margin-top: 20px;
    margin-bottom: 20px;
    border: 1px solid #ff523b;
  }

  .single-product .fa {
    color: #ff523b;
    margin-left: 10px;
  }

  .number-input {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .number-input input {
    text-align: center;
    width: 50px;
    height: 40px;
    font-size: 20px;
    border: 1px solid #ff523b;
    border-left: none;
    border-right: none;
    /* appearance: textfield; */
  }

  .number-input button {
    background-color: white;
    color: #ff523b;
    width: 40px;
    height: 40px;
    font-size: 20px;
    border: 1px solid #ff523b;
    cursor: pointer;
  }

  .number-input button:hover {
    background-color: #dadada;
  }

  input:focus {
    outline: none;
  }

  .btn-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
  }

  .btn {
    background-color: #ff523b;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    text-align: center;
    flex: 1;
  }

  .btn.buy-now {
    background-color: #00bfff;
  }

  /* for multiple small images */
  .small-img-row {
    display: flex;
    justify-content: start;
  }

  .small-img-col {
    flex-basis: 24%;
    cursor: pointer;
    margin-right: 10px;
  }

  .small-img-col img {
    aspect-ratio: 1/1;
  }

  .cart-badge {
    position: relative;
    top: -10px;
    right: 5px;
    background-color: #ff5733;
    color: white;
    font-size: 8px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    /* Bold text */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    visibility: hidden;
  }


  /* end of multiple small images */

  @media (max-width: 768px) {
    .col-2 {
      flex: 100%;
      margin-bottom: 20px;
    }
  }

  /* Related Products Section */
  .related-products {
    margin-top: 60px;
    margin-bottom: 50px;
  }

  .related-products h3 {
    margin-bottom: 20px;
  }

  .related-items {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 20px;
  }

  .related-item {
    text-align: center;
    flex: 1;
    margin-right: 20px;
    max-width: 150px;
  }

  .related-item img {
    max-width: 100%;
    border: 1px solid #ccc;
    margin-bottom: 10px;
    cursor: pointer;
    transition: ease-in-out;
  }

  .related-item .item-price {
    color: #f00;
    margin-top: 5px;
  }

  /* End of Related Products Section */

  /* css of footer */
  .infos {
    background-color: var(--dark-background);
    color: #fff;
  }

  .contact {
    padding-top: 100px;
  }

  .contact-info {
    display: flex;
    gap: 3rem;
    color: #fff;
    max-width: 1200px;
    margin: 0 auto;
  }

  .info {
    flex: 1;
  }

  .first-info {
    flex: 2;
  }

  .contact a {
    text-decoration: none;
  }

  .contact-info h4 {
    color: #fff;
    font-size: 20px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 10px;
  }

  .contact-info p {
    color: var(--dark-background-foreground);
    font-size: 14px;
    font-weight: 400;
    line-height: 1.5;
    margin-bottom: 10px;
    cursor: pointer;
  }

  .copyright {
    max-width: 1200px;
    margin: 0 auto;
    color: var(--dark-background-foreground);
  }

  .copyright a {
    text-decoration: underline;
    color: var(--dark-background-foreground);
  }

  .copyright a:hover {
    text-decoration: underline;
  }

  footer {
    width: 100%;
    background-color: var(--dark-background);
    color: #fff;
    padding: 20px;
    text-align: center;
  }

  .info h4 {
    margin-bottom: 14px;
  }

  .info {
    text-align: left;
  }

  .info ul {
    list-style-type: none;
  }

  .footer-image {
    margin-bottom: 14px;
  }

  /* end of css of footer */

  .product-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
  }

  .product-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
  }

  .product-images {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .main-image img {
    width: 100%;
    height: auto;
    object-fit: contain;
  }

  .thumbnail-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }

  .thumbnail-grid img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    cursor: pointer;
  }

  .product-info h1 {
    font-size: 2rem;
    margin-bottom: 20px;
  }

  .price {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 20px;
  }

  .size-select {
    margin-bottom: 20px;
  }

  .quantity-selector {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
  }

  .button-group {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
  }

  .button-group button {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  .buy-now {
    background-color: #007bff;
    color: white;
  }

  .add-to-cart {
    background-color: #ff523b;
    color: white;
  }

  /* Add these CSS rules to match style.css exactly */
  .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background-color: white;
    display: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 10px 0;
    z-index: 1000;
    min-width: 200px;
  }

  .dropdown-menu li {
    margin: 0;
    padding: 0;
    display: block;
    width: 100%;
  }

  .dropdown-menu li a {
    padding: 8px 20px;
    display: block;
    font-size: 18px;
    white-space: nowrap;
    width: 100%;
    color: black;
  }

  .navmenu>li {
    position: relative;
    display: inline-block;
  }

  .navmenu li:hover .dropdown-menu {
    display: block;
    margin-top: 0;
  }

  .navmenu a:hover {
    color: var(--primary);
    transform: translateY(-2px);
  }
  </style>
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
        <li><a href="/fashionwear/index.php">Home</a></li>
        <li>
          <a href="shop.php">Shop</a>
          <ul class="dropdown-menu">
            <li><a href="mens_collection.php">Men's Collection</a></li>
            <li><a href="womens_collection.php">Women's Collection</a></li>
            <li><a href="kids_collection.php">Kid's Collection</a></li>
          </ul>
        </li>
        <!-- <li><a href="/fashionwear/contact.php">Contact</a></li> -->

        <?php if ($isLoggedIn): ?>
        <?php if (isset($_SESSION['is_admin'])): ?>
        <li><a href="dashboard/admin_dashboard.php" class="dashboard-btn">Dashboard</a></li>
        <?php else: ?>
        <li><a style="white-space: nowrap;word-break: keep-all;display:block;"
            href="/fashionwear/userdashboard/user_dashboard.php">
            <?php 
                // Check if userDetails exists before accessing username
                echo isset($userDetails['username']) ? htmlspecialchars($userDetails['username']) : 'My'; 
            ?>'s Account</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <div class="nav-icon">
      <?php if ($isLoggedIn): ?>
      <p>
        Logged in as <u><strong>
            <?php 
            if (isset($_SESSION['is_admin'])) {
              echo htmlspecialchars($_SESSION['admin_username']);
            } elseif ($userDetails && isset($userDetails['username'])) {
              echo htmlspecialchars($userDetails['username']);
            }
          ?></strong></u>
      </p>
      <?php else: ?>
      <a href="login.php"><i class="bx bx-user"></i></a>
      <?php endif; ?>
      <a href="/fashionwear/cart/cart.php"><i class="bx bx-cart"></i>
        <span id="cart-badge" class="cart-badge">0</span>
      </a>
      <!-- <div class="bx bx-menu" id="menu-icon"></div> -->
    </div>
  </header>
  <!-- end of navbar -->

  <!-- start of product detail -->
  <div class="product-container">
    <div class="small-container single-product">
      <div class="row">
        <div class="col-2">
          <!-- Main Product Image -->
          <img src="uploads/products/<?php echo htmlspecialchars($product['images']); ?>" width="100%" id="ProductImg"
            alt="<?php echo htmlspecialchars($product['name']); ?>" />

          <div class="small-img-row">
            <!-- Main image thumbnail -->
            <div class="small-img-col">
              <img src="uploads/products/<?php echo htmlspecialchars($product['images']); ?>" width="100%"
                class="small-img" alt="<?php echo htmlspecialchars($product['name']); ?>" />
            </div>

            <!-- Additional images -->
            <?php 
              if(!empty($product['additional_images'])) {
                  $additional_images = json_decode($product['additional_images'], true);
                  if(is_array($additional_images)) {
                      foreach($additional_images as $image): 
              ?>
            <div class="small-img-col">
              <img src="uploads/products/<?php echo htmlspecialchars($image); ?>" width="100%" class="small-img"
                alt="Additional view" />
            </div>
            <?php 
                      endforeach;
                  }
              }
              ?>
          </div>
        </div>
        <div class="col-2">
          <p>Home / <?php echo htmlspecialchars($product['category']); ?></p>
          <h1><?php echo htmlspecialchars($product['name']); ?></h1>
          <h4>NPR. <?php echo number_format($product['price'], 2); ?></h4>
          <select>
            <option>Select Size</option>
            <?php 
            $sizes = explode(',', $product['sizes']);
            foreach($sizes as $size): 
            ?>
            <option value="<?php echo trim($size); ?>"><?php echo trim($size); ?></option>
            <?php endforeach; ?>
          </select>
          <div class="number-input">
            <button onclick="decrement()">-</button>
            <input id="quantity" type="text" value="1" min="1" />
            <button onclick="increment()">+</button>
          </div>
          <div class="btn-container">
            <button class="btn buy-now">Buy Now </button>
            <button class="btn add-to-cart-btn" style="background-color: #FF523B;">Add To Cart</button>
            <input type="hidden" id="product_id" value="<?php echo $product_id; ?>">
          </div>
          <h3>Product Details <i class="fa fa-indent"></i></h3>
          <br />
          <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
      </div>
    </div>
    <!-- end of product detail -->

  </div>

  <!-- js for quantity increment and decrement -->
  <script>
  function increment() {
    let input = document.getElementById("quantity");
    input.value = parseInt(input.value) + 1;
  }

  function decrement() {
    let input = document.getElementById("quantity");
    if (input.value > 1) {
      input.value = parseInt(input.value) - 1;
    }
  }
  </script>

  <script>
  $(document).ready(function() {
    $('.buy-now').on('click', function(e) {
      e.preventDefault();

      const productId = $("#product_id").val();
      const productName = $("h1").text();
      const price = $("h4").text().replace("NPR.", "").replace(/,/g, "").trim();
      const quantity = parseInt($("#quantity").val());
      const size = $("select").val();

      if (size === 'Select Size') {
        alert('Please select a size');
        return;
      }

      console.log("Hello world")

      $.ajax({
        url: './cart/cart_handler.php',
        type: 'POST',
        data: {
          action: 'add',
          productId: productId,
          productName: productName,
          price: price,
          quantity: quantity
        },
        success: function(response) {
          if (response === "Please login first") {
            alert("Please login to proceed with purchase");
            window.location.href = '/fashionwear/login.php';
          } else {
            window.location.href = '/fashionwear/cart/checkout.php';
          }
        },
        error: function(xhr, status, error) {
          console.error('Error:', error);
          alert('Error processing request');
        }
      });
    });
  });
  </script>

  <!-- js for product gallery -->
  <script>
  var ProductImg = document.getElementById("ProductImg");
  var SmallImg = document.getElementsByClassName("small-img");

  SmallImg[0].onmouseover = function() {
    ProductImg.src = SmallImg[0].src;
  };
  SmallImg[1].onmouseover = function() {
    ProductImg.src = SmallImg[1].src;
  };
  SmallImg[2].onmouseover = function() {
    ProductImg.src = SmallImg[2].src;
  };
  </script>

  <script src="cart/addToCart.js"></script>

  <!-- footer starts -->
  <footer class="infos">
    <section class="contact">
      <div class="contact-info">
        <div class="first-info info">
          <a href="index.php">
            <img src="images/logo.png" width="80" class="footer-image" height="80" alt="FashionWear Logo" />
          </a>
          <ul>
            <li>
              <p>Kathmandu, Nepal</p>
            </li>
            <li>
              <p>0160-5462-8214</p>
            </li>
            <li>
              <p>fashionwear2025@gmail.com</p>
            </li>
          </ul>
        </div>

        <div class="second-info info">
          <h4>Support</h4>
          <ul>
            <!-- <li>
              <a href="#">
                <p>Contact us</p>
              </a>
            </li>
            <li>
              <a href="#">
                <p>About page</p>
              </a>
            </li> -->
            <li>
              <a href="#">
                <p>Shopping & Returns</p>
              </a>
            </li>
            <li>
              <a href="#">
                <p>Privacy</p>
              </a>
            </li>
          </ul>
        </div>

        <div class="third-info info">
          <h4>Shop</h4>
          <ul>
            <li>
              <a href="#">
                <p>Men's Shopping</p>
              </a>
            </li>
            <li>
              <a href="#">
                <p>Women's Shopping</p>
              </a>
            </li>
            <li>
              <a href="#">
                <p>Kid's Shopping</p>
              </a>
            </li>
            <!-- <li>
              <a href="#">
                <p>Discount</p>
              </a>
            </li> -->
          </ul>
        </div>
      </div>
    </section>
    <div class="copyright">
      <hr style="width: 100%; margin: 20px; border-top: 1px solid #000" />
      <p>
        &copy; 2025 FASHIONWEAR. All rights reserved.
        <a href="#">Privacy Policy</a>
      </p>
    </div>
  </footer>
  <!-- footer ends -->

  <script>
  $(document).ready(function() {
    $('.add-to-cart-btn').off('click');

    $('.add-to-cart-btn').on('click', function(e) {
      e.preventDefault();

      const productId = $("#product_id").val();
      const productName = $("h1").text();
      const price = $("h4").text().replace("NPR.", "").replace(/,/g, "").trim();
      const quantity = parseInt($("#quantity").val());
      const size = $("select").val();

      if (size === 'Select Size') {
        alert('Please select a size');
        return;
      }

      $.ajax({
        url: 'cart/cart_handler.php',
        type: 'POST',
        data: {
          action: 'add',
          productId: productId,
          productName: productName,
          price: price,
          quantity: quantity
        },
        success: function(response) {
          if (response === "Please login first") {
            alert("Please login to add items to cart");
            window.location.href = 'login.php';
          } else {
            console.log('Server response:', response);
            alert('Product added to cart!');
            // Update cart count
            $.ajax({
              url: 'cart/cart_handler.php',
              type: 'POST',
              data: {
                action: 'count'
              },
              success: function(count) {
                $('#cart-badge').text(count);
                $('#cart-badge').css('visibility', count > 0 ? 'visible' : 'hidden');
              }
            });
          }
        },
        error: function(xhr, status, error) {
          console.error('Error:', error);
          alert('Error adding product to cart');
        }
      });
    });
  });
  </script>
</body>

</html>