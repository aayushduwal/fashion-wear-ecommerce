<?php
session_start();
require_once('../database/config.php');

// Add security check after your existing code
$ADMIN_REGISTRATION_KEY = "FW_Admin_2025_K9MP5VN2XL7HQ4WR8YT1ZB6";
//http://localhost/FASHIONWEAR/dashboard/signup.php?key=FW_Admin_2025_K9MP5VN2XL7HQ4WR8YT1ZB6

if (!isset($_GET['key']) || $_GET['key'] !== $ADMIN_REGISTRATION_KEY) {
    die("Unauthorized access");
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize validation and errors
$validation = true;
$errors = [
    'name' => '',
    'email' => '',
    'password' => '',
    'general' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = test_input($_POST['username'] ?? '');
    $email = test_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_role = 'admin'; // Fixed role as admin for admin signup

    // Username validation
    if (empty($username)) {
        $errors['name'] = "Username is required.";
        $validation = false;
    } elseif (!preg_match("/^[a-zA-Z0-9_-]{3,50}$/", $username)) {
        $errors['name'] = "Only letters, numbers, underscore and dash allowed.";
        $validation = false;
    } elseif (strlen($username) > 50) {
        $errors['name'] = "Username cannot exceed 50 characteRs.";
        $validation = false;
    }

    // Email validation
    if (empty($email)) {
        $errors['email'] = "Email is required.";
        $validation = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
        $validation = false;
    } elseif (strlen($email) > 255) {
        $errors['email'] = "Email cannot exceed 255 characteRs.";
        $validation = false;
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = "Password is required.";
        $validation = false;
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characteRs.";
        $validation = false;
    } elseif (!preg_match('/[a-zA-Z]/', $password)) {
        $errors['password'] = "Password must include at least one letter.";
        $validation = false;
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Password must include at least one number.";
        $validation = false;
    } elseif (!preg_match('/[@$!%*?&]/', $password)) {
        $errors['password'] = "Password must include at least one special character.";
        $validation = false;
    }

    if ($validation) {
        // Check if email already exists
        $sql = "SELECT * FROM admin_users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if($stmt->get_result()->num_rows > 0) {
            $errors['general'] = "Email already exists";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if($stmt->execute()) {
                // Set session variables
                $_SESSION['admin_id'] = $conn->insert_id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['is_admin'] = true;
                
                // Redirect to admin dashboard
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $errors['general'] = "Registration failed";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Signup - fashionwear FashionWear</title>
  <link rel="stylesheet" href="css/signup.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
  <div class="admin-auth-container">
    <div class="auth-box">
      <div class="auth-header">
        <h2>Admin Signup</h2>
        <p>Create your admin account</p>
      </div>

      <?php if(isset($errors['general'])): ?>
      <div class="error-message"><?php echo $errors['general']; ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" required>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>

        <button type="submit" name="signup" class="auth-button">Create Account</button>
      </form>

      <div class="auth-footer">
        <p>Already have an admin account? <a href="login.php">Login</a></p>
      </div>
    </div>
  </div>
</body>

</html>