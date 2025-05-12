<?php 
session_start();
include('database/config.php');

// Function to get user details
// Function to get user details
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);  // Changed from "s" to "i"
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
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ecommerce Website</title>
  <!-- CSS-link -->
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/shop/shop.css" />
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
        <!-- <li><a href="/fashionwear/contact.php">Contact</a></li> -->

        <?php if ($isLoggedIn): ?>
        <?php if (isset($_SESSION['is_admin'])): ?>
        <li><a href="dashboard/admin_dashboard.php" class="dashboard-btn">Dashboard</a></li>
        <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
        <li><a style="white-space: nowrap;word-break: keep-all;display:block;"
            href="/fashionwear/userdashboard/user_dashboard.php"><?php echo htmlspecialchars($userDetails['username']); ?>'s
            Account</a></li>
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
        <!-- <span id="cart-badge" class="cart-badge">0</span> -->
      </a>
      <div class="bx bx-menu" id="menu-icon"></div>
    </div>
  </header>

  <section id="hero">
    <div class="overlay"></div>
    <div class="hero-wrapper">
      <h1>Welcome to FASHION WEAR</h1>
      <p style="margin-bottom:3.5rem">Explore the world of luxury fashion</p>
      <a href="#collections">Shop Now</a>
    </div>
  </section>

  <section id="featured-collections">
    <h2>Featured Collections</h2>
    <div class="collection-grid">
      <div class="collection-item">
        <a href="mens_collection.php">
          <img src="images/srk.jpg" alt="Collection 1" />
          <div class="hover-details">
            <h3>For Men's</h3>
            <p>Elegant and stylish outfits for every Men's.</p>
          </div>
        </a>
      </div>
      <div class="collection-item">
        <a href="womens_collection.php">
          <img src="images/shraddhakapoor.webp" alt="Collection 2" />
          <div class="hover-details">
            <h3>For Women's</h3>
            <p>High-end fashion designed for Women's.</p>
          </div>
        </a>
      </div>
      <div class="collection-item">
        <a href="kids_collection.php">
          <img src="images/Kids.png" alt="Collection 3" />
          <div class="hover-details">
            <h3>For Kids</h3>
            <p>Finest materials with modern designs for Kid's</p>
          </div>
        </a>
      </div>
    </div>
  </section>

  <section id="collections">
    <h2>Exclusive Collections</h2>
    <div class="collection-grid">
      <div class="collection-item">
        <img src="images/double-breasted-jacket.webp" alt="Collection 1" />
        <div class="hover-details">
          <h3>Men's Collection</h3>
          <p>Luxurious apparel for men</p>
        </div>
      </div>
      <div class="collection-item">
        <img src="images/womens.png" alt="Collection 2" />
        <div class="hover-details">
          <h3>Women's Collection</h3>
          <p>FashionWear redefined</p>
        </div>
      </div>
      <div class="collection-item">
        <img src="images/watch.jpg" alt="Collection 3" />
        <div class="hover-details">
          <h3>Accessories</h3>
          <p>Complete your look</p>
        </div>
      </div>
    </div>
  </section>

  <!-- <section id="best-sellers">
    <h2>Best Sellers</h2>
    <div class="best-seller-grid">
      <div class="best-seller-item">
        <img src="images/silkdress.jpg" alt="Best Seller 2" />
        <h3>Silk Dress</h3>
        <p>Starting at $299</p>
      </div>
      <div class="best-seller-item">
        <img src="images/classicshoes.jpg" alt="Best Seller 3" />
        <h3>Classic Shoes</h3>
        <p>Starting at $149</p>
      </div>
      <div class="best-seller-item">
        <img src="images/blazer.jpg" alt="Best Seller 1" />
        <h3>Classic Blazer</h3>
        <p>$399</p>
      </div>
      <div class="best-seller-item">
        <img src="images/boyswatch.jpg" alt="Best Seller 2" />
        <h3>Elegant Watch</h3>
        <p>$999</p>
      </div>
      <div class="best-seller-item">
        <img src="images  /stylishbag.jpg" alt="Best Seller 3" />
        <h3>Stylish Bag</h3>
        <p>$199</p>
      </div>
    </div>
  </section> -->

  <section id="partnership">
    <div class="lv-banner">
      <h2>FashionWear Partners with Louis Vuitton</h2>
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
                <a href="#"><p>Contact us</p></a>
              </li>
              <li>
                <a href="#"><p>About page</p></a>
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
                <a href="#"><p>Discount</p></a>
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
  const menuIcon = document.getElementById("menu-icon");
  const navmenu = document.querySelector(".navmenu");
  const searchIcon = document.getElementById("search-icon");
  const searchBar = document.getElementById("search-bar");
  let isMenuOpen = false;
  let isSearchOpen = false;

  menuIcon.addEventListener("click", () => {
    navmenu.classList.toggle("active");
    isMenuOpen = !isMenuOpen;
  });

  searchIcon.addEventListener("click", () => {
    searchBar.classList.toggle("active");
    if (searchBar.style.display === "block") {
      searchBar.style.display = "none";
    } else {
      searchBar.style.display = "block";
      searchBar.focus();
    }

    // Cart badge update
    updateCartBadge();
  });
  </script>
</body>

</html>