<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth() {
    if (!isset($_SESSION['admin_id']) || !$_SESSION['is_admin']) {
        header("Location: login.php");
        exit();
    }
    return true;
}

function getHeader($title = 'Admin Dashboard') {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="../css/admin_dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <?php
}

function getSidebar() {
    ?>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-logo">
      <a href="../index.php">
        <h2>FashionWear</h2>
      </a>
    </div>
    <ul class="sidebar-menu">
      <li>
        <a href="admin_dashboard.php">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="categories.php">
          <i class="fas fa-th-list"></i>
          <span>Categories</span>
        </a>
      </li>
      <li class="has-dropdown">
        <a href="#">
          <i class="fas fa-box"></i>
          <span>Products</span>
        </a>
        <div class="dropdown">
          <a href="products.php?action=list">View All</a>
          <a href="products.php?action=add">Add New</a>
        </div>
      </li>
      <li>
        <a href="orders.php">
          <i class="fas fa-shopping-cart"></i>
          <span>Orders</span>
        </a>
      </li>
      <li>
        <a href="customers.php">
          <i class="fas fa-users"></i>
          <span>Customers</span>
        </a>
      </li>
    </ul>
  </div>
  <?php
}

function getFooter() {
    ?>
</body>

</html>
<?php
}

// New function for Categories Management
function getCategoriesManagement() {
    ?>
<div class="categories-management">
  <h1>Category Management</h1>
  <input type="text" placeholder="Search products..." id="search-products">
  <select id="category-filter">
    <option value="all">All Categories</option>
    <option value="men">Men</option>
    <option value="women">Women</option>
    <option value="kids">Kids</option>
  </select>
  <button onclick="filterCategories()">Filter</button>

  <table>
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
    <tbody id="categories-list">
      <!-- Dynamic content will be populated here -->
    </tbody>
  </table>
</div>
<script>
function filterCategories() {
  // Logic to filter categories based on selected category and search input
  const searchValue = document.getElementById('search-products').value;
  const categoryValue = document.getElementById('category-filter').value;
  // Fetch and display filtered categories
}
</script>
<?php
}
?>