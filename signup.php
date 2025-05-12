<?php
require_once 'database/config.php';
session_start();

// Initialize the user role variable
$user_role = 'user';  // Fixed as admin

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
    'confirm_password' => '',
    'general' => ''
];

// Initialize isSubmitted flag
$isSubmitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isSubmitted = true;
    
    // Get and sanitize data from form
    $username = test_input($_POST['username'] ?? '');
    $email = test_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Username validation
    if (empty($username)) {
        $errors['name'] = "Username is required.";
        $validation = false;
    } elseif (!preg_match("/^[a-zA-Z0-9_-]{3,50}$/", $username)) {
        $errors['name'] = "Only letters and whitespaces allowed.";
        $validation = false;
    } elseif (strlen($username) > 50) {
        $errors['name'] = "Username cannot exceed 50 characteRs.";
        $validation = false;
    }

    // Email validation with additional checks
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
    } elseif(strlen($password) < 8) {
        $errors['password'] = "Must be 8 char";
        $validation = false;
    } elseif (!preg_match('/[a-zA-Z]/', $password)) {
        $errors['password'] = "Must include one letter";
        $validation = false;
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Must include one number";
        $validation = false;
    } elseif (!preg_match('/[@$!%*?&]/', $password)) {
        $errors['password'] = "Must include one special char";
        $validation = false;
    }

    // Confirm password validation
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm your password.";
        $validation = false;
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
        $validation = false;
    }

    // Database interaction if validation passes
    if ($validation) {
        try {
            // Check for existing user
            $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors['general'] = "Email or username already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, password, user_role) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $user_role);
                
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_role'] = 'user';
                    header("Location: index.php");
                    exit();
                } else {
                    $errors['general'] = "Error: " . $stmt->error;
                }
            }
        } catch (Exception $e) {
            $errors['general'] = "An error occurred. Please try again later.";
            error_log("Signup error: " . $e->getMessage());
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Form | Signup</title>
    <link rel="stylesheet" href="css/signup.css" />
    <link rel="stylesheet" href="css/index.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" />
  </head>
  <body>
    <!-- Keeping your existing navbar -->
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
               <li><a href="login.php">Login</a></li>
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

    <div class="image-background"></div>
    <div class="parent-container">
        <div class="container">
            <h1>Signup</h1>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" required />
                    <?php if ($isSubmitted && !empty($errors['name'])): ?>
                        <p style="color: red"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required />
                    <?php if ($isSubmitted && !empty($errors['email'])): ?>
                        <p style="color: red"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>

                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required />
                    <?php if ($isSubmitted && !empty($errors['password'])): ?>
                        <p style="color: red"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>

                     <div class="password-requirements">
        <p>Password requirements:</p>
        <ul>
            <li>At least 8 characters</li>
            <li>At least one letter</li>
            <li>At least one number</li>
            <li>At least one special character (@$!%*?&)</li>
        </ul>
    </div>
                </div>

                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
                    <?php if ($isSubmitted && !empty($errors['confirm_password'])): ?>
                        <p style="color: red"><?php echo $errors['confirm_password']; ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Signup</button>
                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Login Here!</a></p>
                </div>
            </form>
        </div>
    </div>
  </body>
</html>