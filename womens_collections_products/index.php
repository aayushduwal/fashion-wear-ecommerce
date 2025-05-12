<?php
session_start();
require_once('../database/config.php');
require_once('../includes/functions.php');

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define the order of subcategories
$subcategory_order = ['Tops', 'Bottoms', 'Jeans', 'Outerwear'];

// Fetch products for each subcategory
$products_by_category = [];
foreach ($subcategory_order as $subcategory) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = 'Women' AND subcategory = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $subcategory);
    $stmt->execute();
    $result = $stmt->get_result();
    $products_by_category[$subcategory] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Women's Collection - FashionWear</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/collection.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
</head>

<body>
  <style>
  .hero {
    background: url("https://ccknitting.com.np/wp-content/uploads/2023/09/Womens-Clothing.jpg") no-repeat center center/cover;
    height: 60vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
    overflow: hidden;
  }

  .hero h1 {
    font-size: 48px;
    letter-spacing: 3px;
    margin-bottom: 10px;
    text-transform: uppercase;
  }

  .hero p {
    font-size: 20px;
    font-style: italic;
  }

  .categories {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 20px;
    font-size: 18px;
  }

  .categories a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
  }
  </style>
  <?php include('../includes/header.php'); ?>

  <section class="hero">
    <div class="herotxt">
      <h5>FASHIONWEAR</h5>
    </div>
  </section>

  <div class="categories">
    <a href="#tops">Tops</a>
    <a href="#bottoms">Bottoms</a>
    <a href="#jeans">Jeans</a>
    <a href="#outerwear">Outerwear</a>
  </div>

  <!-- Hero Section with Title -->
  <div class="collection-hero">
    <h1>WOMEN'S COLLECTION</h1>
  </div>

  <!-- Products by Subcategory -->
  <div class="container">
    <?php foreach ($subcategory_order as $subcategory): ?>
    <section class="collection-section" id="<?php echo strtolower($subcategory); ?>">
      <h2><?php echo htmlspecialchars($subcategory); ?></h2>
      <div class="collection-wrapper">
        <?php if (!empty($products_by_category[$subcategory])): ?>
        <?php foreach ($products_by_category[$subcategory] as $product): ?>
        <div class="collection-wrapper-child">
          <a href="details.php?id=<?php echo $product['id']; ?>">
            <img src="../uploads/products/<?php echo htmlspecialchars($product['images']); ?>"
              alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>

            <p>NPR. <?php echo number_format($product['price'], 2); ?></p>
          </a>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p class="no-products">No products available in <?php echo htmlspecialchars($subcategory); ?></p>
        <?php endif; ?>
      </div>
    </section>
    <?php endforeach; ?>
  </div>

  <?php include('../includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../cart/addToCart.js"></script>
</body>

</html>