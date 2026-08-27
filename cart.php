<?php

include("config.php");

// User authentication check
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user']);

/* -------------------------------------------------------------
   1. ADD ITEM TO CART (DATABASE)
   ------------------------------------------------------------- */
if (isset($_GET['add'])) {
    $pid = intval($_GET['add']);
    $qty = isset($_GET['qty']) ? max(1, intval($_GET['qty'])) : 1;

    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$pid");

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty WHERE user_id=$user_id AND product_id=$pid");
    } else {
        mysqli_query($conn, "INSERT INTO cart(user_id, product_id, quantity) VALUES($user_id, $pid, $qty)");
    }
    header("Location: cart.php");
    exit();
}

/* -------------------------------------------------------------
   2. UPDATE ALL / SINGLE QUANTITY
   ------------------------------------------------------------- */
if (isset($_POST['update'])) {
    if (isset($_POST['cart_id']) && isset($_POST['quantity'])) {
        $cid = intval($_POST['cart_id']);
        $qty = max(1, intval($_POST['quantity']));
        mysqli_query($conn, "UPDATE cart SET quantity=$qty WHERE id=$cid AND user_id=$user_id");
    } elseif (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cid => $qty) {
            $cid = intval($cid);
            $qty = intval($qty);
            if ($qty <= 0) {
                mysqli_query($conn, "DELETE FROM cart WHERE id=$cid AND user_id=$user_id");
            } else {
                mysqli_query($conn, "UPDATE cart SET quantity=$qty WHERE id=$cid AND user_id=$user_id");
            }
        }
    }
    header("Location: cart.php");
    exit();
}

/* -------------------------------------------------------------
   3. DELETE SINGLE ITEM
   ------------------------------------------------------------- */
if (isset($_GET['delete'])) {
    $cid = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM cart WHERE id=$cid AND user_id=$user_id");
    header("Location: cart.php");
    exit();
}

/* -------------------------------------------------------------
   4. CLEAR ENTIRE CART
   ------------------------------------------------------------- */
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id");
    header("Location: cart.php");
    exit();
}

/* -------------------------------------------------------------
   5. FETCH CART ITEMS
   ------------------------------------------------------------- */
$query = "SELECT cart.id AS cart_id, products.id AS product_id, products.name, products.price, products.image, cart.quantity
          FROM cart
          JOIN products ON cart.product_id = products.id
          WHERE cart.user_id = $user_id";

$result = mysqli_query($conn, $query);

$total = 0;
$total_items = 0;
$cart_products = array();

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subtotal = $row['price'] * $row['quantity'];
        $total += $subtotal;
        $total_items += $row['quantity'];
        $row['subtotal'] = $subtotal;
        $cart_products[] = $row;
    }
}

if (file_exists("header.php")) {
    include("header.php");
}
?>

<div class="cart-page-wrapper">
    <div class="cart-container">
        <h1 class="cart-title">
            <i class="fa-solid fa-cart-shopping"></i> Your Shopping Cart
        </h1>

        <?php if (!empty($cart_products)): ?>
            <form action="cart.php" method="POST" id="cartForm">
                <div class="cart-layout">
                    
                    <!-- LEFT SIDE: PRODUCTS TABLE -->
                    <div class="cart-items-section">
                        <div class="cart-table-wrapper">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_products as $item): ?>
                                        <tr>
                                            <td class="cart-product-detail" data-label="Product">
                                                <div class="cart-thumb-box">
                                                    <img class="cart-img" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                </div>
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" class="cart-product-title">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </td>

                                            <td class="cart-price-cell" data-label="Price">
                                                PKR <?php echo number_format($item['price'], 2); ?>
                                            </td>

                                            <td class="cart-qty-cell" data-label="Quantity">
                                                <div class="cart-qty-control">
                                                    <button type="button" class="btn-qty-minus" onclick="decreaseQty(<?php echo $item['cart_id']; ?>)">-</button>
                                                    <input type="number" 
                                                           name="quantities[<?php echo $item['cart_id']; ?>]" 
                                                           id="qty_<?php echo $item['cart_id']; ?>" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           min="1" 
                                                           class="cart-qty-input">
                                                    <button type="button" class="btn-qty-plus" onclick="increaseQty(<?php echo $item['cart_id']; ?>)">+</button>
                                                </div>
                                            </td>

                                            <td class="cart-subtotal-cell" data-label="Subtotal">
                                                PKR <?php echo number_format($item['subtotal'], 2); ?>
                                            </td>

                                            <td class="cart-action-cell" data-label="Action">
                                                <a href="cart.php?delete=<?php echo $item['cart_id']; ?>" class="btn-remove-item" title="Remove Item" onclick="return confirm('Remove this item?');">
                                                    <i class="fa-solid fa-trash-can"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- BUTTONS: Continue Shopping / Update / Clear -->
                        <div class="cart-bottom-actions">
                            <a href="index.php" class="btn-continue-shopping">
                                <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                            </a>
                            <div class="cart-update-clear-group">
                                <a href="cart.php?clear=1" class="btn-clear-cart" onclick="return confirm('Are you sure you want to clear your cart?');">
                                    <i class="fa-solid fa-broom"></i> Clear Cart
                                </a>
                                <button type="submit" name="update" class="btn-update-cart">
                                    <i class="fa-solid fa-arrows-rotate"></i> Update Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDE: ORDER SUMMARY SIDEBAR -->
                    <div class="cart-summary-section">
                        <div class="summary-card">
                            <h3 class="summary-title">Order Summary</h3>
                            
                            <div class="summary-row">
                                <span>Total Items</span>
                                <span><?php echo $total_items; ?></span>
                            </div>

                            <div class="summary-row">
                                <span>Shipping Charge</span>
                                <span class="free-shipping">FREE</span>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-row summary-total">
                                <span>Total Amount</span>
                                <span>PKR <?php echo number_format($total, 2); ?></span>
                            </div>

                            <a href="checkout.php" class="btn-proceed-checkout">
                                Proceed to Checkout <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        <?php else: ?>
            <!-- EMPTY CART STATE -->
            <div class="empty-cart-card">
                <div class="empty-cart-icon">
                    <i class="fa-solid fa-cart-flatbed-suitcase"></i>
                </div>
                <h2>Your Shopping Cart is Empty</h2>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="index.php" class="btn-shop-now">
                    Start Shopping Now
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
if (file_exists("footer.php")) {
    include("footer.php"); 
}
?>