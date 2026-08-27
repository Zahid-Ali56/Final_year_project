<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("config.php");

// Standardized User ID Extraction
$user_id = 0;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} elseif (isset($_SESSION['user'])) {
    if (is_array($_SESSION['user'])) {
        $user_id = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : (isset($_SESSION['user']['user_id']) ? intval($_SESSION['user']['user_id']) : 0);
    } else {
        $user_id = intval($_SESSION['user']);
    }
}

if ($user_id <= 0) {
    header("Location: login.php");
    exit();
}

// PLACE ORDER LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $payment = mysqli_real_escape_string($conn, trim($_POST['payment_method']));

    if (!empty($address) && !empty($payment)) {
        
        $cart_sql = "SELECT cart.product_id, cart.quantity, products.price 
                    FROM cart 
                    JOIN products ON cart.product_id = products.id 
                    WHERE cart.user_id = $user_id";
                    
        $cart_query = mysqli_query($conn, $cart_sql);
        
        if (!$cart_query) {
            die("Cart Query Error: " . mysqli_error($conn));
        }

        $total_amount = 0;
        $items = array();
        
        if (mysqli_num_rows($cart_query) > 0) {
            while ($c_row = mysqli_fetch_assoc($cart_query)) {
                $subtotal = floatval($c_row['price']) * intval($c_row['quantity']);
                $total_amount += $subtotal;
                $c_row['subtotal'] = $subtotal;
                $items[] = $c_row;
            }

            if ($total_amount > 0 && !empty($items)) {
                
                // Insert Main Order
                $sql_order = "INSERT INTO orders (user_id, total_amount, status, address, payment_method) 
                              VALUES ($user_id, $total_amount, 'Pending', '$address', '$payment')";
                $insert_order = mysqli_query($conn, $sql_order);
                
                if (!$insert_order) {
                    die("Orders Table DB Error: " . mysqli_error($conn));
                }

                $order_id = mysqli_insert_id($conn);

                // Insert Items
                foreach ($items as $item) {
                    $pid = intval($item['product_id']);
                    $qty = intval($item['quantity']);
                    $price = floatval($item['price']);

                    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                 VALUES ($order_id, $pid, $qty, $price)";
                    mysqli_query($conn, $sql_item);
                }

                // Empty User Cart
                mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

                // Redirect on success
                header("Location: checkout.php?success=1&order_id=" . $order_id);
                exit();
            }
        } else {
            die("Cart Empty: Database cart table mein matches nahi mile.");
        }
    }
}

// FETCH CART ITEMS FOR DISPLAY
$cart_items_list = array();
$total = 0;

$query = "SELECT cart.quantity, products.id AS product_id, products.name, products.price 
          FROM cart 
          JOIN products ON cart.product_id = products.id
          WHERE cart.user_id = $user_id";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subtotal = floatval($row['price']) * intval($row['quantity']);
        $total += $subtotal;
        $row['subtotal'] = $subtotal;
        $cart_items_list[] = $row;
    }
}

if (empty($cart_items_list) && !isset($_GET['success'])) {
    header("Location: cart.php");
    exit();
}

if (file_exists("header.php")) {
    include("header.php");
}
?>

<div class="checkout-page-wrapper">
    
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        
        <!-- SUCCESS CARD SECTION -->
        <div class="checkout-success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Order Placed Successfully!</h2>
            <p>Thank you for your purchase. Your Order ID is <strong>#<?php echo intval($_GET['order_id']); ?></strong></p>
            <a href="my_orders.php" class="btn-primary-action">View My Orders</a>
        </div>

    <?php else: ?>

        <h1 class="checkout-title"><i class="fas fa-shopping-bag"></i> Checkout</h1>

        <form method="POST" action="checkout.php">
            <div class="checkout-layout">
                
                <!-- LEFT SECTION: ADDRESS & PAYMENT -->
                <div class="checkout-main-info">
                    
                    <div class="checkout-card">
                        <h3 class="card-heading"><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                        <div class="form-group">
                            <label for="address">Full Street Address <span class="required-star">*</span></label>
                            <textarea id="address" name="address" required placeholder="House/Flat No., Street Address, City, Zip Code" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h3 class="card-heading"><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div class="form-group">
                            <label for="payment_method">Select Payment Option <span class="required-star">*</span></label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">-- Select Payment --</option>
                                <option value="Cash on Delivery">Cash on Delivery (COD)</option>
                                <option value="Online Payment">Online Payment</option>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- RIGHT SECTION: ORDER SUMMARY -->
                <div class="checkout-card checkout-summary-card">
                    <h3 class="card-heading"><i class="fas fa-receipt"></i> Order Summary</h3>
                    
                    <div class="checkout-items-list">
                        <?php foreach ($cart_items_list as $item): ?>
                            <div class="checkout-item-row">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="item-qty">Qty: <?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="item-price">PKR <?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span>Shipping Fee:</span>
                        <span class="free-badge">FREE</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row summary-total">
                        <span>Grand Total:</span>
                        <span>PKR <?php echo number_format($total, 2); ?></span>
                    </div>

                    <button type="submit" name="place_order" class="btn-place-order">
                        <i class="fas fa-lock"></i> Confirm & Place Order
                    </button>
                </div>

            </div>
        </form>

    <?php endif; ?>

</div>

<?php 
if (file_exists("footer.php")) {
    include("footer.php"); 
}
?>