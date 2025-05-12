<?php
session_start();
require_once('../database/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user details from both tables
$query = "SELECT u.*, cd.phone, cd.address, cd.city, cd.postal_code 
          FROM users u 
          LEFT JOIN customer_details cd ON u.id = cd.user_id 
          WHERE u.id = ?";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error preparing query: ' . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    
    // Start transaction
    $conn->begin_transaction();
    try {
        // Check if customer details exist
        $check_stmt = $conn->prepare("SELECT id FROM customer_details WHERE user_id = ?");
        if ($check_stmt === false) {
            throw new Exception('Error preparing check query: ' . $conn->error);
        }
        
        $check_stmt->bind_param("i", $_SESSION['user_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing customer details
            $update_query = "UPDATE customer_details 
                           SET phone = ?, address = ?, city = ?, postal_code = ?
                           WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            if ($stmt === false) {
                throw new Exception('Error preparing update query: ' . $conn->error);
            }
            $stmt->bind_param("ssssi", $phone, $address, $city, $postal_code, $_SESSION['user_id']);
        } else {
            // Insert new customer details
            $insert_query = "INSERT INTO customer_details (user_id, phone, address, city, postal_code) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            if ($stmt === false) {
                throw new Exception('Error preparing insert query: ' . $conn->error);
            }
            $stmt->bind_param("issss", $_SESSION['user_id'], $phone, $address, $city, $postal_code);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Error executing query: ' . $stmt->error);
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Profile updated successfully!"; // Store message in session
        header("Location: edit_profile.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage(); // Store error in session
        header("Location: edit_profile.php");
        exit();
    }
}

// Get messages from session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Clear messages after retrieving them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Edit Profile - FASHIONWEAR</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../userdashboard/css/edit_profile.css">
  <style>
  .success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
    border: 1px solid #c3e6cb;
  }

  .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
    border: 1px solid #f5c6cb;
  }
  </style>
</head>

<body>
  <?php include('../includes/header.php'); ?>

  <div class="edit-profile-container">
    <div class="edit-profile-form">
      <h2>Edit Profile</h2>

      <?php if ($success_message): ?>
      <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>

      <?php if ($error_message): ?>
      <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="username">Username (Cannot be changed)</label>
          <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>

        <div class="form-group">
          <label for="email">Email (Cannot be changed)</label>
          <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
            pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
        </div>

        <div class="form-group">
          <label for="address">Address</label>
          <textarea id="address" name="address"
            rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
          <label for="city">City</label>
          <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Country</label>
          <input type="text" value="Nepal" disabled>
          <input type="hidden" name="country" value="Nepal">
        </div>

        <div class="form-group">
          <label for="postal_code">Postal Code</label>
          <input type="text" id="postal_code" name="postal_code"
            value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>" pattern="[0-9]{5}"
            title="Please enter a valid 5-digit postal code">
        </div>

        <button type="submit" class="submit-btn">Update Profile</button>
      </form>
    </div>
  </div>

  <?php include('../includes/footer.php'); ?>
</body>

</html>