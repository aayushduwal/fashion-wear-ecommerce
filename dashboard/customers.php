<?php
require_once('includes/common.php');
require_once('../database/config.php');

checkAuth();

// Handle different operations based on action parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

getHeader('Customers Management');
getSidebar();
?>

<div class="main-content">
    <?php
    switch($action) {
        case 'view':
            // View Customer Details
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $stmt = $conn->prepare("
                SELECT c.*,
                    COUNT(o.id) as total_orders,
                    COALESCE(SUM(o.total_amount), 0) as total_spent
                FROM customers c
                LEFT JOIN orders o ON c.id = o.customer_id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $customer = $stmt->get_result()->fetch_assoc();

            if($customer) {
                // Get customer's orders
                $stmt = $conn->prepare("
                    SELECT * FROM orders 
                    WHERE customer_id = ? 
                    ORDER BY order_date DESC
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $orders = $stmt->get_result();
                ?>
                <div class="dashboard-header">
                    <h1>Customer Details</h1>
                    <div class="nav-icon">
                        <a href="?action=list" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="customer-details">
                    <div class="customer-info">
                        <h3>Personal Information</h3>
                        <table class="info-table">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td><?php echo htmlspecialchars($customer['address']); ?></td>
                            </tr>
                            <tr>
                                <th>City:</th>
                                <td><?php echo htmlspecialchars($customer['city']); ?></td>
                            </tr>
                            <tr>
                                <th>Country:</th>
                                <td><?php echo htmlspecialchars($customer['country']); ?></td>
                            </tr>
                            <tr>
                                <th>Total Orders:</th>
                                <td><?php echo $customer['total_orders']; ?></td>
                            </tr>
                            <tr>
                                <th>Total Spent:</th>
                                <td>NPR. <?php echo number_format($customer['total_spent'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Joined Date:</th>
                                <td><?php echo date('d M Y', strtotime($customer['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="customer-orders">
                        <h3>Order History</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                    <td>NPR. <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn-view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            break;

        default:
            // List Customers (Default View)
            $query = "
                SELECT c.*,
                       COUNT(o.id) as total_orders,
                       COALESCE(SUM(o.total_amount), 0) as total_spent
                FROM customers c
                LEFT JOIN orders o ON c.id = o.customer_id
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ";
            $customers = $conn->query($query);
            ?>
            <div class="dashboard-header">
                <h1>Customers Management</h1>
            </div>

            <div class="customers-list">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Total Orders</th>
                            <th>Total Spent</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($customer = $customers->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['city']); ?></td>
                            <td><?php echo $customer['total_orders']; ?></td>
                            <td>NPR. <?php echo number_format($customer['total_spent'], 2); ?></td>
                            <td><?php echo date('d M Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <a href="?action=view&id=<?php echo $customer['id']; ?>" class="btn-view">
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