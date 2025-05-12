<?php
session_start();
$error = '';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'database/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['user_role']; 

        if ($user['user_role'] === 'admin') {
            header("Location: /fashionwear/dashboard/index.php");  // Admin dashboard
        } else {
            header("Location: index.php");  // User dashboard
        }
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
$stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Login - FashionWear</title>
  <link rel="stylesheet" href="css/login.css" />
  <link rel="stylesheet" href="css/index.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" />
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
        <!-- <li><a href="contact.php">Contact</a></li> -->
      </ul>
    </nav>
    <div class="nav-icon">
      <a href="#"><i class="bx bx-search"></i></a>
      <a href="#"><i class="bx bx-user"></i></a>
      <a href="#"><i class="bx bx-cart"></i></a>
    </div>
    <div id="menu-icon">
      <i class="fa fa-bars"></i>
    </div>
  </header>

  <div class="parent-container">
    <div class="container">
      <h1>User Login</h1>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="input-box">
          <input type="text" name="email" placeholder="Email" required />
        </div>
        <div class="input-box">
          <input type="password" name="password" placeholder="Password" required />
          <?php if (isset($error)): ?>
          <p style='color: red;'><?php echo $error; ?></p>
          <?php endif; ?>
          <a href="reset-pwd.php" class="forgot-password">Forgot password?</a>
        </div>
        <button type="submit" class="btn">Login</button>
        <div class="register-link">
          <p>Don't have an account? <a href="signup.php">Signup Here!</a></p>
        </div>
      </form>
    </div>
  </div>
</body>

</html>