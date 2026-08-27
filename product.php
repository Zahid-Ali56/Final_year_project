<?php 
include("header.php"); 

// Product ID Sanitize
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Views increment
    mysqli_query($conn, "UPDATE products SET views = views + 1 WHERE id = $id");

    // Fetch Product
    $query = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
    $product = mysqli_fetch_assoc($query);
} else {
    $product = null;
}

if (!$product) {
    echo "<div style='padding:50px; text-align:center;'><h2>Product Not Found!</h2><a href='index.php'>Back to Store</a></div>";
    include("footer.php");
    exit();
}

// Split strings into clean arrays (Handles both commas and slashes automatically)
$raw_sizes = str_replace('/', ',', $product['sizes'] ?? '');
$raw_colors = str_replace('/', ',', $product['colors'] ?? '');

$sizes = !empty(trim($raw_sizes)) ? array_filter(array_map('trim', explode(',', $raw_sizes))) : [];
$colors = !empty(trim($raw_colors)) ? array_filter(array_map('trim', explode(',', $raw_colors))) : [];
?>

<link rel="stylesheet" href="css/style.css?v=<?php echo file_exists('css/style.css') ? filemtime('css/style.css') : time(); ?>">

<div class="product-detail-container">
    
    <!-- LEFT SIDE: GALLERY -->
    <div class="product-gallery">
        <div class="main-image-box">
            <img id="mainProductImg" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        
        <!-- Thumbnails Grid with Hover + Click Support -->
        <div class="thumbnail-grid">
            <img class="thumb active" 
                 src="<?php echo htmlspecialchars($product['image']); ?>" 
                 onmouseover="previewImage(this)" 
                 onclick="setActiveImage(this)">
            
            <?php if(!empty($product['image2'])): ?>
                <img class="thumb" 
                     src="<?php echo htmlspecialchars($product['image2']); ?>" 
                     onmouseover="previewImage(this)" 
                     onclick="setActiveImage(this)">
            <?php endif; ?>
            
            <?php if(!empty($product['image3'])): ?>
                <img class="thumb" 
                     src="<?php echo htmlspecialchars($product['image3']); ?>" 
                     onmouseover="previewImage(this)" 
                     onclick="setActiveImage(this)">
            <?php endif; ?>
            
            <?php if(!empty($product['image4'])): ?>
                <img class="thumb" 
                     src="<?php echo htmlspecialchars($product['image4']); ?>" 
                     onmouseover="previewImage(this)" 
                     onclick="setActiveImage(this)">
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT SIDE: PRODUCT DETAILS -->
    <div class="product-info-details">
        <h1 class="p-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="p-price">PKR <?php echo number_format($product['price'], 2); ?></p>

        <div class="p-description">
            <h3>Description & Specifications</h3>
            <p><?php echo !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : "No detailed description available."; ?></p>
        </div>

        <form action="cart.php" method="GET" class="product-form" id="productCartForm">
            <input type="hidden" name="add" value="<?php echo $product['id']; ?>">

            <!-- Dynamic Model/Specs -->
            <?php if(!empty($product['model_specs'])): ?>
                <div class="variation-group">
                    <label><strong>Specs/Model:</strong></label>
                    <span class="spec-tag"><?php echo htmlspecialchars($product['model_specs']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Dynamic Sizes Custom Dropdown -->
            <?php if(!empty($sizes)): ?>
                <div class="variation-group">
                    <label><strong>Select Size:</strong></label>
                    <div class="custom-dropdown" id="sizeDropdown">
                        <input type="hidden" name="size" class="dropdown-input" data-label="Size">
                        <div class="custom-select-trigger">
                            <span>-- Choose Size --</span>
                            <svg class="dropdown-arrow" viewBox="0 0 24 24" width="18" height="18"><path d="M7 10l5 5 5-5z" fill="#1a1a1a"/></svg>
                        </div>
                        <div class="custom-options">
                            <div class="custom-option default" data-value="">Select</div>
                            <?php foreach($sizes as $sz): ?>
                                <div class="custom-option" data-value="<?php echo htmlspecialchars($sz); ?>"><?php echo htmlspecialchars($sz); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dynamic Colors Custom Dropdown -->
            <?php if(!empty($colors)): ?>
                <div class="variation-group">
                    <label><strong>Select Color:</strong></label>
                    <div class="custom-dropdown" id="colorDropdown">
                        <input type="hidden" name="color" class="dropdown-input" data-label="Color">
                        <div class="custom-select-trigger">
                            <span>-- Choose Color --</span>
                            <svg class="dropdown-arrow" viewBox="0 0 24 24" width="18" height="18"><path d="M7 10l5 5 5-5z" fill="#1a1a1a"/></svg>
                        </div>
                        <div class="custom-options">
                            <div class="custom-option default" data-value="">Select</div>
                            <?php foreach($colors as $clr): ?>
                                <div class="custom-option" data-value="<?php echo htmlspecialchars($clr); ?>"><?php echo htmlspecialchars($clr); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="p-buttons">
                <button type="submit" class="btn-add-cart-large">
                    <i class="fa fa-shopping-cart"></i> Add to Cart
                </button>
                <a href="index.php" class="btn-back-store">Back to Store</a>
            </div>
        </form>
    </div>

</div>

<script src="js/main.js?v=<?php echo time(); ?>"></script>
<?php include("footer.php"); ?>