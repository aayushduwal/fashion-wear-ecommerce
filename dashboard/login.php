<?php
session_start();
require_once('../database/config.php');

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM admin_users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['is_admin'] = true;
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    $error = "Invalid email or password";
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Login - FashionWear</title>
  <link rel="stylesheet" href="css/login.css">
  <!-- Your existing CSS -->
</head>

<body>
  <div class="login-container">
    <form method="POST" class="login-form">
      <h2>Admin Login</h2>

      <?php if(isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <div class="form-group">
        <input type="email" name="email" placeholder="Email" required>
      </div>

      <div class="form-group">
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <button type="submit" name="login">Login</button>

      <div class="form-footer">
        <p>Don't have an admin account? <a href="signup.php">Sign Up</a></p>
      </div>
    </form>
  </div>
</body>

</html>