<?php
// Error reporting & Config
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once("config.php");

// User authentication check
$user_id = 0;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} elseif (isset($_SESSION['user']) && !is_array($_SESSION['user'])) {
    $user_id = intval($_SESSION['user']);
}

if ($user_id <= 0) {
    header("Location: login.php");
    exit();
}

// Fetch user orders
$orders_query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");

if (file_exists("header.php")) {
    include("header.php");
}
?>

<div class="orders-page-wrapper">
    <h2 class="orders-title"><i class="fa-solid fa-box-open"></i> My Orders</h2>
    
    <?php if (mysqli_num_rows($orders_query) == 0): ?>
        
        <div class="empty-orders-card">
            <p>You haven't placed any orders yet.</p>
            <a href="index.php" class="btn-primary-action">Shop Now</a>
        </div>

    <?php else: ?>
        
        <div class="orders-table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders_query)): ?>
                        <tr>
                            <td class="order-id">#<?php echo $order['id']; ?></td>
                            <td class="order-amount">PKR <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <?php 
                                    $statusClass = (strtolower($order['status']) === 'completed') ? 'completed' : 'pending';
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <details class="order-details-accordion">
                                    <summary>See Items </summary>
                                    <div class="order-items-box">
                                        <?php
                                        $order_id = $order['id'];
                                        $items_query = mysqli_query($conn, "SELECT order_items.*, products.name FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_id = $order_id");
                                        while ($item = mysqli_fetch_assoc($items_query)) {
                                            echo "<p class='order-item-detail'>• " . htmlspecialchars($item['name']) . " (Qty: " . $item['quantity'] . ") - PKR " . number_format($item['price'], 2) . "</p>";
                                        }
                                        ?>]
                                    </div>
                                </details>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php 
if (file_exists("footer.php")) {
    include("footer.php"); 
}
?>