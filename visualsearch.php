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

    // 1. Python AI Microservice ko cURL ke zariye image bhejna
    $ch = curl_init();
    $cfile = new CURLFile($imagePath, $imageType, $imageName);
    
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:5000/predict-image');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cfile));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        $error_message = "AI Service Error: Python Server is not running. Please start 'python app.py'.";
    } else {
        $data = json_decode($response, true);

        if (isset($data['category']) && !empty($data['category'])) {
            
            // Check agar AI accuracy 40% se low thi aur category "unknown" aayi
            if ($data['category'] === 'unknown') {
                $error_message = "Image clear nahi hai ya product recognize nahi ho saka. Barah-e-karam koi doosri clear pic upload karein.";
            } else {
                $detected_tag = mysqli_real_escape_string($conn, $data['category']);
                $confidence = isset($data['confidence']) ? $data['confidence'] : 0;
                
                // Search History Log karein (Agar user logged in hai)
                if (isset($_SESSION['user_id'])) {
                    $user_id = (int)$_SESSION['user_id'];
                    mysqli_query($conn, "INSERT INTO search_history (user_id, search_term) VALUES ($user_id, '$detected_tag')");
                }
                
                // 2. AI Detected Category ke mutabiq Database Query
                $query = "SELECT * FROM products WHERE name LIKE '%$detected_tag%' OR description LIKE '%$detected_tag%' ORDER BY id DESC";
                $result = mysqli_query($conn, $query);
            }

        } else {
            $error_message = "AI Model could not classify the image. Try uploading a clearer picture.";
        }
    }
} else {
    $error_message = "No image uploaded or upload error occurred. Please select an image using the camera button.";
}
?>

<div class="main-products-container" style="padding: 30px 5%;">
    <h1 class="section-title" style="margin-bottom: 10px; text-align: center;">Visual Image Search Results</h1>

    <?php if (!empty($detected_tag)): ?>
        <p style="text-align: center; color: #28a745; font-weight: 600; margin-bottom: 25px; font-size: 1.1rem;">
            AI Detected Category: <span style="text-transform: uppercase; color: #007bff;"><?php echo htmlspecialchars($detected_tag); ?></span> 
            <?php if ($confidence > 0): ?> (Accuracy: <?php echo $confidence; ?>%)<?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <p style="text-align: center; color: #d9534f; font-size: 1.1rem; padding: 20px 0; font-weight: 500;">
            <?php echo $error_message; ?>
        </p>
    <?php endif; ?>

    <div class="products-grid">
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Image Path Resolution
            $image_url = file_exists($row['image']) ? $row['image'] : (file_exists("images/" . $row['image']) ? "images/" . $row['image'] : (file_exists("uploads/" . $row['image']) ? "uploads/" . $row['image'] : $row['image']));
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
        echo "<p class='no-products' style='grid-column: 1/-1; text-align: center; color: #d9534f; font-size: 1.1rem; padding: 40px 0;'>No matching products found in store for ' " . htmlspecialchars($detected_tag) . " '.</p>";
    }
    ?>
    </div>
</div>

<script src="js/main.js"></script>
<?php include("footer.php"); ?>