<?php
require_once('includes/common.php');
require_once('../database/config.php');

checkAuth();

// Handle different operations based on action parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Add this after checkAuth();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Modify your orders query
$query = "SELECT o.*, c.name as customer_name 
          FROM orders o 
          LEFT JOIN customers c ON o.customer_id = c.id 
          WHERE 1=1";
          
if($search) {
    $query .= " AND (c.name LIKE '%$search%' OR o.id LIKE '%$search%')";
}
if($status) {
    $query .= " AND o.status = '$status'";
}
if($date_from) {
    $query .= " AND DATE(o.order_date) >= '$date_from'";
}
if($date_to) {
    $query .= " AND DATE(o.order_date) <= '$date_to'";
}
$query .= " ORDER BY o.order_date DESC";
$orders = $conn->query($query);

getHeader('Orders Management');
getSidebar();
?>

<div class="main-content">
  <?php
    switch($action) {
        case 'view':
            // View Order Details
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $order = $conn->query("
                SELECT o.*, c.name as customer_name, c.email, c.phone
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                WHERE o.id = $id
            ")->fetch_assoc();

            if($order) {
                // Get order items
                $items = $conn->query("
                    SELECT oi.*, p.name as product_name, p.images 
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = $id
                ");
                ?>
  <div class="dashboard-header">
    <h1>Order #<?php echo $order['id']; ?></h1>
    <div class="nav-icon">
      <a href="?action=list" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>
  </div>

  <div class="order-details">
    <div class="order-info">
      <h3>Order Information</h3>
      <table class="info-table">
        <tr>
          <th>Order Date:</th>
          <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
        </tr>
        <tr>
          <th>Status:</th>
          <td>
            <form action="?action=update_status" method="POST" class="status-form">
              <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
              <select name="status" onchange="this.form.submit()">
                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing
                </option>
                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed
                </option>
                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled
                </option>
              </select>
            </form>
          </td>
        </tr>
        <tr>
          <th>Total Amount:</th>
          <td>NPR. <?php echo number_format($order['total_amount'], 2); ?></td>
        </tr>
      </table>
    </div>

    <div class="customer-info">
      <h3>Customer Information</h3>
      <table class="info-table">
        <tr>
          <th>Name:</th>
          <td><?php echo $order['customer_name']; ?></td>
        </tr>
        <tr>
          <th>Email:</th>
          <td><?php echo $order['email']; ?></td>
        </tr>
        <tr>
          <th>Phone:</th>
          <td><?php echo $order['phone']; ?></td>
        </tr>
      </table>
    </div>

    <div class="order-items">
      <h3>Order Items</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php while($item = $items->fetch_assoc()): ?>
          <tr>
            <td><?php echo $item['product_name']; ?></td>
            <td>
              <img src="../uploads/products/<?php echo $item['images']; ?>" alt="<?php echo $item['product_name']; ?>"
                class="product-thumbnail">
            </td>
            <td>NPR. <?php echo number_format($item['price'], 2); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>NPR. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php
            }
            break;

        case 'update_status':
            // Update Order Status
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $order_id = $_POST['order_id'];
                $status = $_POST['status'];
                
                if($conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id")) {
                    echo "<script>alert('Order status updated!'); window.location='?action=view&id=$order_id';</script>";
                } else {
                    echo "<script>alert('Error updating status!'); window.location='?action=view&id=$order_id';</script>";
                }
            }
            break;

        default:
            // List Orders (Default View)
            ?>
  <div class="dashboard-header">
    <h1>Orders Management</h1>
  </div>

  <div class="filter-section">
    <form method="GET" class="filter-form">
      <div class="form-group">
        <input type="text" name="search" placeholder="Search orders..." value="<?php echo $search; ?>">
      </div>
      <div class="form-group">
        <select name="status">
          <option value="">All Status</option>
          <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="processing" <?php echo $status == 'processing' ? 'selected' : ''; ?>>Processing</option>
          <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
          <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
      </div>
      <div class="form-group">
        <input type="date" name="date_from" value="<?php echo $date_from; ?>">
        <input type="date" name="date_to" value="<?php echo $date_to; ?>">
      </div>
      <button type="submit" class="btn btn-primary"
        style="display: inline-block; padding: 0.4rem 1rem; font-size: 16px; background-color: #7F22FE; border-color: #7F22FE; color: white; border-radius: 7px;">Filter</button>
      <a href="orders.php" style=" display: inline-block; padding: 0.4rem 1rem;
        font-size: 16px; background-color: #F1F5F9; border: 1px solid #CAD5E2; color: #314158; border-radius: 7px; text-decoration-line: none;
"><i class="fas fa-redo"></i>Reset</a>
    </form>
  </div>

  <div class=" orders-list">
    <table class="data-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($order = $orders->fetch_assoc()): ?>
        <tr>
          <td>#<?php echo $order['id']; ?></td>
          <td><?php echo $order['customer_name']; ?></td>
          <td>NPR. <?php echo number_format($order['total_amount'], 2); ?></td>
          <td>
            <span class="status status-<?php echo strtolower($order['status']); ?>">
              <?php echo ucfirst($order['status']); ?>
            </span>
          </td>
          <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
          <td>
            <a href="?action=view&id=<?php echo $order['id']; ?>" class="btn-view">
              <i class="fas fa-eye"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php
    }
    ?>
</div>

<?php getFooter(); ?>