<?php
// Config file include
include_once("config.php");

// 1. GLOBAL CATEGORIES ARRAY (Fetch once, use everywhere)
$categories_list = array();
$cats_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
if ($cats_query) {
    while ($row = mysqli_fetch_assoc($cats_query)) {
        $categories_list[] = $row;
    }
}

// 2. REAL DATABASE: CART COUNTER LOGIC
$cart_count = 0;
if (isset($_SESSION['user'])) {
    $current_user_id = intval($_SESSION['user']);
    $cart_count_query = mysqli_query($conn, "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = $current_user_id");
    
    if ($cart_count_query && $c_row = mysqli_fetch_assoc($cart_count_query)) {
        $cart_count = $c_row['total_items'] ? intval($c_row['total_items']) : 0;
    }
}

// 3. REAL DATABASE: USER NAME LOGIC
$display_name = "User";
if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
    $display_name = $_SESSION['user_name'];
} elseif (isset($_SESSION['user'])) {
    $u_id = intval($_SESSION['user']);
    $user_query = mysqli_query($conn, "SELECT name FROM users WHERE id = $u_id");
    if ($user_query && $u_data = mysqli_fetch_assoc($user_query)) {
        $display_name = $u_data['name'];
        $_SESSION['user_name'] = $display_name; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bazaarly - Online Store</title>
    
    <!-- Dynamic CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo file_exists('css/style.css') ? filemtime('css/style.css') : '1.0'; ?>">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="header" id="mainHeader">
    <nav class="nav-container">
        <!-- Site Logo -->
        <div class="site-logo">
            <a href="index.php">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
                    <g id="logo-mark" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 230 180 C 230 150, 270 150, 270 180 L 270 200 L 230 200 Z" stroke-width="5" fill="none" />
                        <path d="M 215 200 L 285 200 L 280 250 C 265 258, 235 258, 220 250 Z" stroke-width="5" fill="none" />
                        <path d="M 290 190 C 315 220, 310 270, 260 300 C 230 315, 190 310, 175 300 C 210 308, 255 300, 280 270 C 298 248, 298 210, 290 190 Z" 
                              fill="#f97c06" 
                              stroke="none" 
                              transform="translate(15, -10)" />
                    </g>
                    <text x="250" y="345" 
                          font-family="system-ui, -apple-system, sans-serif" 
                          font-size="45" 
                          font-weight="700" 
                          fill="#ffffff" 
                          text-anchor="middle" 
                          letter-spacing="3">BAZAARLY</text>
                </svg>
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="index.php" class="search-form" id="searchForm" enctype="multipart/form-data">
            <select name="category" class="search-category" onchange="this.form.submit()">
                <option value="">All</option>
                <?php
                if (!empty($categories_list)) {
                    foreach ($categories_list as $c) {
                        $selected = (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'selected' : '';
                        echo "<option value='".$c['id']."' $selected>".htmlspecialchars($c['name'])."</option>";
                    }
                }
                ?>
            </select>

            <div id="imagePreviewContainer" class="image-preview-box" style="display: none;">
                <img id="imagePreviewThumb" src="" alt="Search Preview">
                <button type="button" id="removeImgBtn" title="Remove image">&times;</button>
            </div>

            <input type="text" name="search" id="searchInput" placeholder="Search Anything..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" autocomplete="off">

            <input type="file" name="search_image" id="imageSearchInput" accept="image/*" style="display: none;">

            <button type="button" class="btn-camera" id="cameraBtn" title="Search by Image">
                <i class="fa-solid fa-camera"></i>
            </button>

            <button type="button" class="btn-mic" id="micBtn" title="Search by Voice">
                <i class="fa-solid fa-microphone"></i>
            </button>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <!-- Right Side Nav List -->
        <ul class="header-nav-list">
            <li>
                <a href="index.php" class="nav-item-link" title="Home">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="nav-label">Home</span>
                </a>
            </li>

            <li class="account-dropdown-wrapper">
                <?php if(isset($_SESSION['user'])) { ?>
                    <button type="button" class="nav-item-btn">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <div class="nav-text-group">
                            <span class="nav-subtext">Welcome,</span>
                            <span class="nav-label"><?php echo htmlspecialchars($display_name); ?> ▾</span>
                        </div>
                    </button>
                    <div class="account-menu">
                        <a href="my_orders.php" class="account-menu-item">My Orders</a>
                        <a href="logout.php" class="account-menu-item accent">Logout</a>
                    </div>
                <?php } else { ?>
                    <button type="button" class="nav-item-btn">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <div class="nav-text-group">
                            <span class="nav-subtext">Hello, Sign in</span>
                            <span class="nav-label">Account ▾</span>
                        </div>
                    </button>
                    <div class="account-menu">
                        <a href="login.php" class="account-menu-item">Login</a>
                        <a href="signup.php" class="account-menu-item accent">Create Account</a>
                    </div>
                <?php } ?>
            </li>

            <li>
                <a href="cart.php" class="nav-item-link cart-link" title="Cart">
                    <div class="cart-icon-wrapper">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <?php if($cart_count > 0) { ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php } ?>
                    </div>
                    <span class="nav-label">Cart</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<script src="js/main.js?v=<?php echo file_exists('js/main.js') ? filemtime('js/main.js') : '1.0'; ?>" defer></script>