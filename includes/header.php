<?php 
require_once(__DIR__ . '/../database/config.php');
require_once(__DIR__ . '/functions.php');

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
    <link rel="stylesheet" href="../css/style.css" />
   <link rel="stylesheet" href="../css/index.css" />
   <link rel="stylesheet" href="../css/collection.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <!-- font of inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
      rel="stylesheet"
    />
    <!-- closing the font of inter -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <style>
      body {
  background-color: #f4f4f4;
  margin: 0;
  font-family: "Inter", sans-serif;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* css of navbar */
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
  transition: transform 0.3s ease;
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
  color: var(--primary);
  transform: translateY(-2px);
}

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
  color: var(--dark-background);
  font-size: 24px;
  transition: color 0.3s ease, transform 0.3s ease;
}

.nav-icon i:hover {
  color: var(--primary);
  transform: scale(1.1);
}

.navmenu li {
  position: relative;
}

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

.navmenu li:hover .dropdown-menu {
  display: block;
}

#menu-icon {
  font-size: 30px;
  color: var(--dark-background);
  cursor: pointer;
  display: none;
}

/* Media Queries */
@media (max-width: 768px) {
  header {
    flex-direction: column;
    align-items: flex-start;
    padding: 10px;
  }

  .logo img {
    height: 30px;
  }

  #menu-icon {
    display: block;
  }

  .navmenu {
    position: absolute;
    top: 100%;
    left: 0;
    background-color: white;
    width: 100%;
    height: 100vh;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transform: translateX(-100%);
  }

  .navmenu.active {
    transform: translateX(0);
  }

  .main-btn {
    font-size: 0.8rem;
    padding: 8px 15px;
  }
}

@media (max-width: 480px) {
  .navmenu a {
    font-size: 16px;
  }

  .main-text h1 {
    font-size: 1.5rem;
  }

  .main-btn {
    font-size: 0.7rem;
  }
}
    </style>

  </head>
  <body>
    <header>
      <div class="logo">
        <a href="/fashionwear/index.php">
          <img src="../images/logo.png" alt="Logo" />
        </a>
      </div>
      <nav class="nav-container">
        <ul class="navmenu">
          <li><a href="/fashionwear/index.php">Home</a></li>
          <li>
            <a href="/fashionwear/shop.php">Shop</a>
            <ul class="dropdown-menu">
              <li><a href="/fashionwear/mens_collections_products/index.php">Men's Collection</a></li>
              <li><a href="/fashionwear/womens_collection.php">Women's Collection</a></li>
              <li><a href="/fashionwear/kids_collection.php">Kid's Collection</a></li>
            </ul>
          </li>
          <!-- <li><a href="/fashionwear/about.php">About</a></li>
          <li><a href="/fashionwear/contact.php">Contact</a></li> -->

              <?php if ($isLoggedIn): ?>
    <?php if (isset($_SESSION['is_admin'])): ?>
        <li><a href="/fashionwear/dashboard/admin_dashboard.php" class="dashboard-btn">Dashboard</a></li>
    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
        <li><a style="white-space: nowrap;word-break: keep-all;display:block;" href="/fashionwear/userdashboard/user_dashboard.php"><?php echo htmlspecialchars($userDetails['username']); ?>'s Account</a></li>
    <?php endif; ?>
    <li><a href="/fashionwear/logout.php">Logout</a></li>
<?php else: ?>
    <li><a href="/fashionwear/login.php">Login</a></li>
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


    

  
  </body>
</html>