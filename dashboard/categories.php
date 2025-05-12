<?php
require_once('includes/common.php');
require_once('../database/config.php');

checkAuth();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch categories from the database
function fetchCategories() {
    global $conn;
    $query = "SELECT DISTINCT category FROM products";
    $result = $conn->query($query);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    return $categories;
}

// Fetch products based on search and filter
function fetchProducts($search, $category) {
    global $conn;
    $query = "SELECT * FROM products WHERE 1=1";
    
    if ($search) {
        $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
    }
    
    if ($category && $category !== 'All Categories') {
        $query .= " AND category = '$category'";
    }
    
    return $conn->query($query);
}

$categoriesList = fetchCategories();
$products = fetchProducts($search, $category);

getHeader('Category Management');
getSidebar();
?>

<div class="main-content">
  <div class="dashboard-header">
    <h1>Category Management</h1>
    <!-- <div class="nav-icon">
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Category
            </a>
        </div> -->
  </div>

  <div class="filter-section">
    <form method="GET" class="filter-form">
      <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>"
        class="form-control" style="display: inline-block; width: 500px; height: 40px; font-size: 16px;">
      <select name="category" class="form-control"
        style="display: inline-block; width: 500px; height: 40px; font-size: 16px;">
        <option value="All Categories">All Categories</option>
        <?php foreach ($categoriesList as $cat): ?>
        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($cat); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary"
        style="display: inline-block; padding: 0.4rem 1rem; font-size: 16px; background-color: #7F22FE; border-color: #7F22FE; color: white; border-radius: 7px;">Filter</button>
      <a href="?action=list" class="btn btn-secondary" style=" display: inline-block; padding: 0.4rem 1rem;
        font-size: 16px; background-color: #F1F5F9; border: 1px solid #CAD5E2; color: #314158; border-radius: 7px; text-decoration-line: none;
"><i class="fas fa-redo"></i> Reset</a>
    </form>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Category</th>
        <th>Inventory</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($product = $products->fetch_assoc()): ?>
      <tr>
        <td><?php echo $product['id']; ?></td>
        <td>
          <img src="../uploads/products/<?php echo htmlspecialchars($product['images']); ?>"
            alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
        </td>
        <td><?php echo htmlspecialchars($product['name']); ?></td>
        <td>NPR. <?php echo number_format($product['price'], 2); ?></td>
        <td><?php echo htmlspecialchars($product['category']); ?></td>
        <td><?php echo $product['inventory']; ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php getFooter(); ?>