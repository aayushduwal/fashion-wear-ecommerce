<?php
session_start();
include('../../database/config.php');

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

// Fetch all jackets
$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'Kids' AND subcategory = 'Jeans'");
$stmt->execute();
$jeans = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kids' Jeans - FASHIONWEAR</title>
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/collection.css">
  <!-- Include other CSS and icon links -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
</head>

<body>
  <!-- Include header -->
  <?php include('../../includes/header.php'); ?>

  <!-- Category Header -->
  <div class="category-header">
    <h1>Kids' Jeans</h1>
    <div class="breadcrumb">
      <a href="../../index.php">Home</a> /
      <a href="../index.php">Kids' Collection</a> /
      <span>Jeans</span>
    </div>
  </div>

  <!-- Products Section -->
  <section class="collection">
    <div class="container">
      <div class="collection-wrapper">
        <?php while($product = $jeans->fetch_assoc()): ?>
        <div class="collection-wrapper-child">
          <a href="../details.php?id=<?php echo $product['id']; ?>">
            <img src="../../uploads/products/<?php echo htmlspecialchars($product['images']); ?>"
              alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>

            <p>रु. <?php echo number_format($product['price'], 2); ?></p>
          </a>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- Include footer -->
  <?php include('../../includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../cart/addToCart.js"></script>
</body>

</html>