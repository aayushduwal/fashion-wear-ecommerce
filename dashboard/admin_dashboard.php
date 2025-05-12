<?php
session_start();

require_once('includes/common.php');
require_once('../database/config.php');

checkAuth();

// Get dashboard statistics
$product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$order_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$customer_count = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY order_date DESC LIMIT 5
");

// Get header with title
getHeader('Dashboard - FashionWear');
getSidebar();

?>

<div class="main-content">
  <div class="dashboard-header">
    <h1>Dashboard Overview</h1>
    <div class="nav-icon">
      <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
      <a href="../logout.php">Logout</a>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="stats-container">
    <div class="stat-card">
      <h3>Total Products</h3>
      <div class="number"><?php echo $product_count; ?></div>
    </div>
    <div class="stat-card">
      <h3>Total Orders</h3>
      <div class="number"><?php echo $order_count; ?></div>
    </div>
    <div class="stat-card">
      <h3>Total Customers</h3>
      <div class="number"><?php echo $customer_count; ?></div>
    </div>
    <div class="stat-card">
      <h3>Total Revenue</h3>
      <div class="number">NPR. <?php echo number_format($total_revenue, 2); ?></div>
    </div>
  </div>

  <!-- Recent Orders -->
  <div class="recent-orders">
    <h2>Recent Orders</h2>
    <table class="order-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($order = $recent_orders->fetch_assoc()): ?>
        <tr>
          <td>#<?php echo $order['id']; ?></td>
          <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
          <td>NPR. <?php echo number_format($order['total_amount'], 2); ?></td>
          <td>
            <span class="status status-<?php echo strtolower($order['status']); ?>">
              <?php echo ucfirst($order['status']); ?>
            </span>
          </td>
          <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
/* Enhanced UI Styles */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.stats-container {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 20px;
}

.stat-card {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 20px;
  flex: 1;
  min-width: 200px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
  margin-bottom: 10px;
  font-size: 1.2em;
}

.stat-card .number {
  font-size: 2em;
  font-weight: bold;
  color: ##45556C;
}

.recent-orders {
  margin-top: 20px;
}

.order-table {
  width: 100%;
  border-collapse: collapse;
}

.order-table th,
.order-table td {
  padding: 10px;
  border: 1px solid #dee2e6;
  text-align: left;
}

.order-table th {
  background-color: #f8f9fa;
}

.status {
  padding: 5px 10px;
  border-radius: 4px;
  color: white;
}

.status-completed {
  background-color: #28a745;
}

.status-pending {
  background-color: #ffc107;
}

.status-cancelled {
  background-color: #dc3545;
}
</style>

<?php getFooter(); ?>