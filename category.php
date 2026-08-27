<link rel="stylesheet" href="css/style.css ">
<?php
include("config.php");

// 1. Get Category ID safely
$cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Fetch Category Name from database
$cat_name = "Products"; // Default fallback title
if ($cat_id > 0) {
    $cat_query = mysqli_query($conn, "SELECT name FROM categories WHERE id = $cat_id");
    if ($cat_query && $cat_data = mysqli_fetch_assoc($cat_query)) {
        $cat_name = $cat_data['name'];
    }
}

// 3. Fetch Products for this Category
$query = "SELECT * FROM products WHERE category_id = $cat_id ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!-- Dynamic Category Title -->
<h2><?php echo htmlspecialchars($cat_name); ?> Products</h2>

<div class="products">
<?php 
if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) { 
?>
        <div class="product">
            <img src="<?php echo htmlspecialchars($row['image']); ?>" width="150">
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            <p>PKR <?php echo number_format($row['price'], 2); ?></p>
            <a href="cart.php?add=<?php echo $row['id']; ?>">Add to Cart</a>
            <a href="product.php?id=<?php echo $row['id']; ?>">View</a>
        </div>
<?php 
    } 
} else {
    echo "<p>No products found in this category.</p>";
}
?>
</div>