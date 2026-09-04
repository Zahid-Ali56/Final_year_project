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
    // FAST & ACCURATE MULTI-ENVIRONMENT DETECTION (Localhost vs Live)
    // ------------------------------------------------------------------
    $local_domain = "http://127.0.0.1:5000";
    $railway_domain = "https://finalyearproject-production-c2dc.up.railway.app";
    
    // Auto-Detect Environment based on Domain/Server Name
    $server_host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
    $is_local_env = (
        strpos($server_host, 'localhost') !== false || 
        strpos($server_host, '127.0.0.1') !== false
    );

    if ($is_local_env) {
        // Localhost testing ke liye local Python Flask server hit karein
        $ai_url = $local_domain . '/predict-image';
    } else {
        // Online live production site ke liye Railway API hit karein
        $ai_url = $railway_domain . '/predict-image';
    }

    // ------------------------------------------------------------------
    // 1. Python AI Microservice ko cURL ke zariye image bhejna
    // ------------------------------------------------------------------
    $ch = curl_init();
    $cfile = new CURLFile($imagePath, $imageType, $imageName);
    
    curl_setopt($ch, CURLOPT_URL, $ai_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cfile));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // SSL Verification Bypass (Online Server Security Compatibility)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // ------------------------------------------------------------------
    // 2. RESPONSE PARSING & 502/FAIL-SAFE FALLBACK HANDLING
    // ------------------------------------------------------------------
    if ($curl_error) {
        $error_message = "AI Service Connection Error: AI server tak connection fail ho gaya. Details: " . htmlspecialchars($curl_error);
    } else {
        $data = json_decode($response, true);

        // Check karein agar HTTP Status 200 na ho ya JSON Parse error ho (e.g. 502 HTML page return hona)
        if ($http_status !== 200 || !is_array($data)) {
            // Agar local crash hua ho par server online ho, fallback option automatically run hoga
            if ($is_local_env && !empty($railway_domain)) {
                // Secondary Fallback Attempt on Railway Live Cloud
                $ch_alt = curl_init();
                curl_setopt($ch_alt, CURLOPT_URL, $railway_domain . '/predict-image');
                curl_setopt($ch_alt, CURLOPT_POST, true);
                curl_setopt($ch_alt, CURLOPT_POSTFIELDS, array('image' => $cfile));
                curl_setopt($ch_alt, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_alt, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch_alt, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch_alt, CURLOPT_SSL_VERIFYHOST, false);
                
                $response_alt = curl_exec($ch_alt);
                $http_status_alt = curl_getinfo($ch_alt, CURLINFO_HTTP_CODE);
                curl_close($ch_alt);

                if ($http_status_alt === 200) {
                    $data = json_decode($response_alt, true);
                }
            }
        }

        // Final Category Extraction
        $category_key = null;
        if (is_array($data)) {
            if (isset($data['category'])) {
                $category_key = $data['category'];
            } elseif (isset($data['label'])) {
                $category_key = $data['label'];
            } elseif (isset($data['prediction'])) {
                $category_key = $data['prediction'];
            }
        }

        if (!empty($category_key)) {
            
            // Low accuracy / UNKNOWN Category handling
            if ($category_key === 'unknown') {
                $error_message = "Image clear nahi hai ya product recognize nahi ho saka. Barah-e-karam koi doosri clear pic upload karein.";
            } else {
                /** @var mysqli $conn */
                $detected_tag = mysqli_real_escape_string($conn, $category_key);
                $confidence = isset($data['confidence']) ? $data['confidence'] : (isset($data['score']) ? round($data['score'] * 100) : 0);
                
                // Search History Logging
                if (isset($_SESSION['user_id'])) {
                    $user_id = (int)$_SESSION['user_id'];
                    mysqli_query($conn, "INSERT INTO search_history (user_id, search_term) VALUES ($user_id, '$detected_tag')");
                }
                
                // AI Category ke mutabiq Products Query Execution
                $query = "SELECT * FROM products WHERE name LIKE '%$detected_tag%' OR description LIKE '%$detected_tag%' ORDER BY id DESC";
                $result = mysqli_query($conn, $query);
            }

        } else {
            $error_message = "AI API Response Error (Code: " . $http_status . "). Server response read nahi ho saka.";
        }
    }
} else {
    $error_message = "No image uploaded or upload error occurred. Please select an image using the camera button.";
}
?>

<style>
/* Internal CSS for Visual Search Page */
:root {
    --primary-navy: #24426a;
    --primary-navy-hover: #1b365d;
    --accent-orange: #f97c06;
    --accent-orange-hover: #e97304;
    --bg-light: #f5f7fa;
    --card-bg: #ffffff;
    --border-color: #e2e8f0;
    --text-dark: #1a202c;
    --text-muted: #718096;
}

.main-products-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-dark);
    text-align: center;
    margin: 30px 0 20px 0;
}

.ai-detected-info {
    text-align: center;
    font-size: 16px;
    color: #4a5568;
    margin-bottom: 25px;
    font-weight: 500;
}

.category-badge {
    background-color: var(--primary-navy);
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
    text-transform: capitalize;
}

.error-alert {
    background-color: #fff5f5;
    color: #c53030;
    border: 1px solid #feb2b2;
    padding: 12px 20px;
    border-radius: 8px;
    text-align: center;
    max-width: 800px;
    margin: 0 auto 25px auto;
    font-size: 14px;
    font-weight: 500;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
    max-width: 1100px;
    margin: 0 auto;
}

.product-card {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.product-image-box {
    width: 100%;
    height: 180px;
    background-color: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.product-image-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.product-info {
    padding: 16px;
    text-align: center;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.product-title {
    font-size: 15px;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 8px 0;
    line-height: 1.3;
    height: 38px;
    overflow: hidden;
}

.product-price {
    font-size: 17px;
    font-weight: 700;
    color: var(--accent-orange);
    margin: 0 0 15px 0;
}

.product-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.btn-cart, .btn-view {
    flex: 1;
    padding: 8px 0;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-cart {
    background-color: var(--primary-navy);
    color: #ffffff;
}

.btn-cart:hover {
    background-color: var(--primary-navy-hover);
}

.btn-view {
    background-color: #edf2f7;
    color: #4a5568;
}

.btn-view:hover {
    background-color: #e2e8f0;
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    color: var(--text-muted);
    font-size: 16px;
    padding: 40px 0;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}
</style>

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