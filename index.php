<?php include("header.php"); ?>

<?php
// Check karein ke search query ya category query active hai ya nahi
$is_searching = isset($_GET['search']) && !empty(trim($_GET['search']));
$is_category = isset($_GET['category']) && !empty(trim($_GET['category']));
?>

<?php if (!$is_searching && !$is_category): ?>
    <!-- 1. CATEGORIES BUTTON WITH MENU TOGGLE (Shown ONLY on Homepage) -->
    <div class="category-menu-wrapper">
        <button type="button" class="category-toggle-btn" id="catToggleBtn">
            <i class="fa-solid fa-bars"></i>
            <span>CATEGORIES</span>
            <i class="fa-solid fa-caret-down"></i>
        </button>

        <!-- Sidebar Dropdown -->
        <div class="category-dropdown-sidebar" id="catSidebar">
            <ul>
                <?php
                if (!empty($categories_list)) {
                    foreach ($categories_list as $c) {
                ?>
                    <li>
                        <a href="index.php?category=<?php echo intval($c['id']); ?>">
                            <span><?php echo htmlspecialchars($c['name']); ?></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </li>
                <?php 
                    } 
                } else {
                    echo "<li><a href='#'><span>No Categories Found</span></a></li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- 2. RECOMMENDATIONS SECTION (Shown ONLY on Homepage) -->
    <?php include("recommendations.php"); ?>
<?php endif; ?>

<!-- 3. MAIN PRODUCTS SECTION (Always Visible) -->
<div class="main-products-container">
    
    <?php 
    // Selected category ka dynamic name nikalne ke liye logic
    $selected_cat_name = "";
    if ($is_category && isset($categories_list) && is_array($categories_list)) {
        $selected_cat_id = intval($_GET['category']);
        foreach ($categories_list as $cat_item) {
            if ($cat_item['id'] == $selected_cat_id) {
                $selected_cat_name = $cat_item['name'];
                break;
            }
        }
    }
    ?>

    <!-- Dynamic Section Title -->
    <?php if ($is_searching && $is_category && !empty($selected_cat_name)): ?>
        <h1 class="section-title">Results for "<?php echo htmlspecialchars($_GET['search']); ?>" in <?php echo htmlspecialchars($selected_cat_name); ?></h1>
    <?php elseif ($is_searching): ?>
        <h1 class="section-title">Search Results for "<?php echo htmlspecialchars($_GET['search']); ?>"</h1>
    <?php elseif ($is_category && !empty($selected_cat_name)): ?>
        <h1 class="section-title"><?php echo htmlspecialchars($selected_cat_name); ?> Products</h1>
    <?php elseif ($is_category): ?>
        <h1 class="section-title">Category Products</h1>
    <?php else: ?>
        <h1 class="section-title">All Products</h1>
    <?php endif; ?>

    <div class="products-grid">
    <?php
    // Base Query setup
    $where_clauses = array();

    if ($is_searching) {
        $search = mysqli_real_escape_string($conn, trim($_GET['search']));

        // Search History Log
        if (isset($_SESSION['user'])) {
            $user_id = intval($_SESSION['user']);
            mysqli_query($conn, "INSERT INTO search_history (user_id, search_term) VALUES ('$user_id', '$search')");
        }

        $where_clauses[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
    }

    if ($is_category) {
        $cat_id = intval($_GET['category']);
        $where_clauses[] = "category_id = '$cat_id'";
    }

    // Dynamic Query generation
    if (count($where_clauses) > 0) {
        $query = "SELECT * FROM products WHERE " . implode(' AND ', $where_clauses) . " ORDER BY id DESC";
    } else {
        $query = "SELECT * FROM products ORDER BY id DESC LIMIT 9";
    }

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("SQL Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
    ?>
            <div class="product-card">
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
    } else {
        echo "<p class='no-products'>No products found matching your request.</p>";
    }
    ?>
    </div>
</div>

<?php include("footer.php"); ?>