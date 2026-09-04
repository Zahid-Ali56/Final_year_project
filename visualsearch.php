<?php 
@ini_set('memory_limit', '256M');
include("header.php"); 

$detected_tag = "";
$confidence = 0;
$error_message = "";
$result = false;

// Image Input Field check
$file_field = isset($_FILES['imageSearchInput']) ? 'imageSearchInput' : (isset($_FILES['search_image']) ? 'search_image' : null);

if ($file_field && $_FILES[$file_field]['error'] == UPLOAD_ERR_OK) {
    $imagePath = $_FILES[$file_field]['tmp_name'];
    $imageName = $_FILES[$file_field]['name'];
    $imageType = $_FILES[$file_field]['type'];

    // ------------------------------------------------------------------
    // DYNAMIC MULTI-ENVIRONMENT ROUTING (Railway, Local & Fallback)
    // ------------------------------------------------------------------
    
    // 1. Aap ka Railway Live Deployed Domain
    $railway_domain = "https://finalyearproject-production-c2dc.up.railway.app"; 
    
    // 2. Local Python Server URL
    $local_domain = "http://127.0.0.1:5000";

    // Server health checking function
    function check_service_alive($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code >= 200 && $code < 500);
    }

    // Auto-Routing Logic: Pehle Railway, phr Local Fallback
    if (check_service_alive($railway_domain . "/")) {
        $ai_url = $railway_domain . '/predict-image';
    } elseif (check_service_alive($local_domain . "/")) {
        $ai_url = $local_domain . '/predict-image';
    } else {
        // Direct Fallback to Railway
        $ai_url = $railway_domain . '/predict-image';
    }

    // 1. Python AI Microservice ko cURL ke zariye image bhejna
    $ch = curl_init();
    $cfile = new CURLFile($imagePath, $imageType, $imageName);
    
    curl_setopt($ch, CURLOPT_URL, $ai_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cfile));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // SSL Verification Bypass for hosting compatibility
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        $error_message = "AI Service Connection Error: " . $curl_error;
    } else {
        $data = json_decode($response, true);

        // Debugging Response Keys
        $category_key = null;
        if (isset($data['category'])) {
            $category_key = $data['category'];
        } elseif (isset($data['label'])) {
            $category_key = $data['label'];
        } elseif (isset($data['prediction'])) {
            $category_key = $data['prediction'];
        }

        if (!empty($category_key)) {
            
            // Low accuracy check
            if ($category_key === 'unknown') {
                $error_message = "Image clear nahi hai ya product recognize nahi ho saka. Barah-e-karam koi doosri clear pic upload karein.";
            } else {
                $detected_tag = mysqli_real_escape_string($conn, $category_key);
                $confidence = isset($data['confidence']) ? $data['confidence'] : (isset($data['score']) ? round($data['score'] * 100) : 0);
                
                // Search History Log
                if (isset($_SESSION['user_id'])) {
                    $user_id = (int)$_SESSION['user_id'];
                    mysqli_query($conn, "INSERT INTO search_history (user_id, search_term) VALUES ($user_id, '$detected_tag')");
                }
                
                // AI Category ke mutabiq DB query
                $query = "SELECT * FROM products WHERE name LIKE '%$detected_tag%' OR description LIKE '%$detected_tag%' ORDER BY id DESC";
                $result = mysqli_query($conn, $query);
            }

        } else {
            $error_message = "AI API Response Error. Raw Response: " . htmlspecialchars(substr($response, 0, 150));
        }
    }
} else {
    $error_message = "No image uploaded or upload error occurred. Please select an image using the camera button.";
}
?>

<div class="main-products-container">
    <h1 class="section-title">Visual Image Search Results</h1>

    <?php if (!empty($detected_tag)): ?>
        <p class="ai-detected-info">
            AI Detected Category: <span class="category-badge"><?php echo htmlspecialchars($detected_tag); ?></span> 
            <?php if ($confidence > 0): ?> (Accuracy: <?php echo $confidence; ?>%)<?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <p class="error-alert">
            <?php echo $error_message; ?>
        </p>
    <?php endif; ?>

    <div class="products-grid">
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            
            // File Fallback Path Check
            $raw_img = trim($row['image']);
            if (!empty($raw_img)) {
                if (file_exists($raw_img)) {
                    $image_url = $raw_img;
                } elseif (file_exists("images/" . $raw_img)) {
                    $image_url = "images/" . $raw_img;
                } elseif (file_exists("uploads/" . $raw_img)) {
                    $image_url = "uploads/" . $raw_img;
                } else {
                    $image_url = (strpos($raw_img, '/') !== false) ? $raw_img : "images/" . $raw_img;
                }
            } else {
                $image_url = "images/no-image.png";
            }
            ?>
            <div class="product-card">
                <div class="product-image-box">
                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
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
    } elseif (empty($error_message) && !empty($detected_tag)) {
        echo "<p class='no-products'>No matching products found in store for '" . htmlspecialchars($detected_tag) . "'.</p>";
    }
    ?>
    </div>
</div>

<script src="js/main.js"></script>
<?php include("footer.php"); ?>