<?php
session_start();
include('../database/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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

// Check login status and get user details
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
$userDetails = null;

if (isset($_SESSION['user_id'])) {
    $userDetails = getUserDetails($conn, $_SESSION['user_id']);
}

// Your existing password change logic
$error = '';
$success = '';

function changePassword($conn, $userId, $newPassword) {
    global $error, $success;
    
    // Validate current password
    if (!isset($_POST['current_password']) || empty($_POST['current_password'])) {
        $error = "Current password is required";
        return;
    }

    // Check if new password matches confirmation
    if ($newPassword !== $_POST['confirm_password']) {
        $error = "New passwords do not match";
        return;
    }

    // Validate password length
    if (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters long";
        return;
    }

    // Validate password has uppercase
    if (!preg_match('/[A-Z]/', $newPassword)) {
        $error = "Password must contain at least one uppercase letter";
        return;
    }

    // Validate password has lowercase
    if (!preg_match('/[a-z]/', $newPassword)) {
        $error = "Password must contain at least one lowercase letter";
        return;
    }

    // Validate password has number
    if (!preg_match('/[0-9]/', $newPassword)) {
        $error = "Password must contain at least one number";
        return;
    }

    // Validate password has special character
    if (!preg_match('/[\W]/', $newPassword)) {
        $error = "Password must contain at least one special character";
        return;
    }

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($_POST['current_password'], $user['password'])) {
        $error = "Current password is incorrect";
        return;
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    if ($stmt->execute()) {
        $success = "Password changed successfully!";
    } else {
        $error = "Error changing password. Please try again.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     $userId = $_SESSION['user_id']; 
    $newPassword = $_POST['new_password'];
    changePassword($conn, $userId, $newPassword);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password - FASHIONWEAR</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="css/change_password.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
</head>

<body>
  <header>
    <div class="logo">
      <a href="index.php">
        <img src="../images/logo.png" alt="Logo" />
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
        <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
        <li><a href="user_dashboard.php"><?php echo htmlspecialchars($userDetails['username']); ?>'s Account</a></li>
        <?php endif; ?>
        <li><a href="/fashionwear/logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <div class="nav-icon">
      <?php if ($isLoggedIn): ?>
      <p>Logged in as <u><strong>
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
      <a href="cart.php"><i class="bx bx-cart"></i></a>
    </div>
    <div id="menu-icon">
      <i class="fa fa-bars"></i>
    </div>
  </header>

  <div class="change-password-container">
    <div class="change-password-form">
      <h2>Change Password</h2>

      <?php if ($error): ?>
      <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="success-message"><?php echo $success; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="current_password">Current Password</label>
          <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-group">
          <label for="new_password">New Password</label>
          <input type="password" id="new_password" name="new_password" required>
          <small>Must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="submit-btn">Change Password</button>
      </form>
    </div>
  </div>
</body>

</html>