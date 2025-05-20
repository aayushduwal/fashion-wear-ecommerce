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

// Fetch products for each category
$stmt_men = $conn->prepare("SELECT * FROM products WHERE category = 'Men' AND show_on_home = 1 LIMIT 4");
$stmt_men->execute();
$menProducts = $stmt_men->get_result();

$stmt_women = $conn->prepare("SELECT * FROM products WHERE category = 'Women' AND show_on_home = 1 LIMIT 4");
$stmt_women->execute();
$womenProducts = $stmt_women->get_result();

$stmt_kids = $conn->prepare("SELECT * FROM products WHERE category = 'Kids' AND show_on_home = 1 LIMIT 4");
$stmt_kids->execute();
$kidsProducts = $stmt_kids->get_result();
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0">
  <title>Ecommerce Website - FashionWear</title>
  <!-- CSS-link -->
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="css/collection.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <!-- font of inter -->
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet" />
  <!-- icon links -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
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
      <div class="bx bx-menu" id="menu-icon"></div>
    </div>
  </header>

  <!-- Hero section -->
  <div class="container">
    <section class="main-home">
      <div class="slider">
        <!--adding multiple images in home page(index.php)-->
        <!-- Add your background images here -->
        <div class="slide active" style="background-image: url('images/hero3.jpg')"></div>
        <div class="slide" style="background-image: url('images/hero4.jpg')"></div>
        <div class="slide" style="background-image: url('images/hero2.jpg')"></div>
        <div class="slide" style="background-image: url('images/hero1.jpg')"></div>
      </div>
      <!--multiple images ends here-->
      <div class="main-home-overlay"></div>
      <div class="main-text">
        <h2>Collection</h2>
        <h1>FashionWear Collection</h1>
        <h3>Up to 50% Off</h3>
        <p>Discover the latest trends and styles.</p>
        <a href="#mens-collection" class="main-btn">Shop Now <i class="fa fa-arrow-right"></i></a>
      </div>
    </section>
  </div>


  <div class="container">
    <div class="denim-section">
      <img src="images/womensdenim.webp" alt="Women's Denim" class="denim-image">
      <h2 class="denim-title">Women's Denim</h2>
      <p class="denim-description">Designed to elevate your day-to-night wardrobe</p>
      <a href="/fashionwear/womens_collections_products/index.php" class="shop-now-btn">SHOP NOW</a>
    </div>

    <div class="denim-section">
      <img src="images/mensdenim.webp" alt="Men's Denim" class="denim-image">
      <h2 class="denim-title">Men's Denim</h2>
      <p class="denim-description">Premium-made denim for every occasion</p>
      <a href="/fashionwear/mens_collections_products/index.php" class="shop-now-btn">SHOP NOW</a>
    </div>


    <!-- Mother's Day Gift Guide Section -->
    <div class="container gift-guide-section">

      <!-- Text Content (Left Side) -->
      <div class="denim-section">
        <h2 class="denim-title">Mother's Day Gift Guide</h2>
        <p class="denim-description">Shop our handpicked selection of gifts just for her</p>
        <a href="/fashionwear/shop.php" class="shop-now-btn">SHOP GIFTS FOR HER</a>
      </div>

      <!-- Image (Right Side) -->
      <div class="denim-section">
        <img src="images/giftimage.jpg" alt="Mother's Day Gifts" class="denim-image">
      </div>

    </div>

    <!-- Full-width banner section -->
    <!-- <div class="dresses-banner">
      <div class="banner-content">
        <h2>ALL DRESSED UP</h2>
        <p class="subtitle">New-season dresses to see you through</p>
        <p class="description">every event on your calendar</p>
        <a href="#" class="shop-now-btn">SHOP NOW</a>
      </div>
    </div> -->

    <div class="container child-collection-section">

      <!-- Left: Image -->
      <!-- <div class="denim-section">
        <img src="images/bachhaa.avif" alt="Child Collection" class="denim-image">
      </div>

      <div class="denim-section">
        <h2 class="denim-title">The Perfect Pair for the Children</h2>
        <p class="denim-description">The clothes you have been waiting for</p>
        <a href="/childrens-collection" class="shop-now-btn">SHOP CLOTHES FOR CHILDRENS</a>
      </div> -->

    </div>

  </div>
  </div>


  <!-- Men's collection -->
  <section id="mens-collection" class="collection">
    <div class="container">
      <h1 class="">Men's Collection</h1>


      <div class="collection-wrapper">
        <?php 
         if ($menProducts->num_rows > 0) {
             while($product = $menProducts->fetch_assoc()) {
                 ?>
        <div class="collection-wrapper-child">
          <a href="details.php?id=<?php echo $product['id']; ?>">
            <img src="uploads/products/<?php echo htmlspecialchars($product['images']); ?>"
              alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>

            <p>NPR. <?php echo number_format($product['price'], 2); ?></p>
          </a>
        </div>
        <?php
             }
         } else {
             echo '<p class="no-products">No products available in Men\'s Collection</p>';
         }
         ?>
      </div>
    </div>
  </section>
  <!-- Women's collection -->
  <section class="collection">
    <div class="container">
      <h1 class="">Women's Collection</h1>

      <div class="collection-wrapper">
        <?php 
             if ($womenProducts->num_rows > 0) {
                 while($product = $womenProducts->fetch_assoc()) {
                     ?>
        <div class="collection-wrapper-child">
          <a href="details.php?id=<?php echo $product['id']; ?>">
            <img src="uploads/products/<?php echo htmlspecialchars($product['images']); ?>"
              alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>

            <p>NPR. <?php echo number_format($product['price'], 2); ?></p>
          </a>
        </div>
        <?php
                 }
             } else {
                 echo '<p class="no-products">No products available in Women\'s Collection</p>';
             }
             ?>
      </div>
    </div>
  </section>
  <!-- Child's collection -->
  <section class="collection">
    <div class="container">
      <h1 class="">Kid's Collection</h1>

      <div class="collection-wrapper">
        <?php 
            if ($kidsProducts->num_rows > 0) {
                while($product = $kidsProducts->fetch_assoc()) {
                    ?>
        <div class="collection-wrapper-child">
          <a href="details.php?id=<?php echo $product['id']; ?>">
            <img src="uploads/products/<?php echo htmlspecialchars($product['images']); ?>"
              alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>

            <p>NPR. <?php echo number_format($product['price'], 2); ?></p>
          </a>
        </div>
        <?php
                }
            } else {
                echo '<p class="no-products">No products available in Kids\' Collection</p>';
            }
            ?>
      </div>
    </div>
  </section>

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
               <a href="/fashionwear/contact.php">
                 <p>Contact us</p>
               </a>
             </li> -->
            <!-- <li>
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
              <a href="/fashionwear/mens_collections_products/index.php">
                <p>Men's Shopping</p>
              </a>
            </li>
            <li>
              <a href="/fashionwear/womens_collections_products/index.php">
                <p>Women's Shopping</p>
              </a>
            </li>
            <li>
              <a href="/fashionwear/kids_collections_products/index.php">
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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="cart/addToCart.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const menuIcon = document.querySelector('#menu-icon');
    const navContainer = document.querySelector('.nav-container');

    menuIcon.addEventListener('click', function(e) {
      e.stopPropagation();
      navContainer.classList.toggle('active');
      this.classList.toggle('bx-x');
    });

    // Handle dropdown menus
    const dropdownLinks = document.querySelectorAll('.navmenu li a');
    dropdownLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        const nextElement = this.nextElementSibling;
        if (nextElement && nextElement.classList.contains('dropdown-menu')) {
          // e.preventDefault();
          nextElement.classList.toggle('show');
        }
      });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
      if (!menuIcon.contains(e.target) && !navContainer.contains(e.target)) {
        navContainer.classList.remove('active');
        menuIcon.classList.remove('bx-x');
      }
    });

    // Prevent menu from closing when clicking inside
    navContainer.addEventListener('click', function(e) {
      e.stopPropagation();
    });

    // Cart badge update
    updateCartBadge();
  });
  </script>

  <script>
  //for image slider in home page(index.php)
  document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;

    function nextSlide() {
      slides[currentSlide].classList.remove('active');
      currentSlide = (currentSlide + 1) % slides.length;
      slides[currentSlide].classList.add('active');
    }

    // Change slide every 3 seconds
    setInterval(nextSlide, 5000);
  });
  </script>
</body>

</html>