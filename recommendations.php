<?php
// DB Connection setup
if (!isset($conn)) {
    include("config.php");
}

$rec_result = false;

// 1. Personal Recommendation (Agar user logged in hai)
if (isset($_SESSION['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    
    $personalized_query = "
        SELECT DISTINCT p.* 
        FROM products p
        JOIN search_history s ON p.name LIKE CONCAT('%', s.search_term, '%')
        WHERE s.user_id = '$user_id'
        ORDER BY s.created_at DESC
        LIMIT 12
    ";
    
    $check_result = mysqli_query($conn, $personalized_query);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $rec_result = $check_result;
    }
}

// 2. Fallback / Default Recommendation (Highest Sold + Most Viewed)
if (!$rec_result) {
    $default_query = "SELECT * FROM products ORDER BY sold DESC, views DESC";
    $rec_result = mysqli_query($conn, $default_query);
}
?>

<div class="recommendations-container">
    <h2 class="section-title">Recommended Products</h2>

    <div class="products-grid" id="productsGrid">
    <?php 
    $count = 0;
    if ($rec_result && mysqli_num_rows($rec_result) > 0) {
        while ($row = mysqli_fetch_assoc($rec_result)) { 
            $count++;
            // Pehle 6 visible honge, baaki hidden rahenge
            $hidden_class = ($count > 6) ? 'extra-product hidden' : '';
    ?>
            <div class="product-card <?php echo $hidden_class; ?>">
                <div class="product-image-box">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p class="product-price">PKR <?php echo number_format($row['price'], 2); ?></p>
                    <div class="product-actions">
                        <a href="cart.php?add=<?php echo $row['id']; ?>" class="btn-cart">Add to Cart</a>
                        <a href="product.php?id=<?php echo $row['id']; ?>" class="btn-view">View</a>
                    </div>
                </div>
            </div>
    <?php 
        } 
    } 
    ?>
    </div>

    <!-- Toggle Buttons (Sirf tabhi dikhenge agar products 6 se zyada hain) -->
    <?php if ($count > 6) { ?>
        <div class="toggle-btn-wrapper">
            <button id="toggleProductsBtn" class="btn-toggle-products" onclick="toggleProducts()">
                <span>See More Products</span>
                <span class="btn-arrow">↓</span>
            </button>
        </div>
    <?php } ?>
</div>