<?php 
include("config.php");

$error_msg = "";
$success_msg = "";

if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success_msg = "Your password has been reset successfully. Please login.";
}

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $pass = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
        if(password_verify($pass, $user['password'])){
            // Dono tarah ke session variables set kar dein taake kisi file mein issue na aaye
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['id']; 
            $_SESSION['user_name'] = $user['name'];
            
            header("Location: index.php");
            exit();
        } else {
            $error_msg = "Invalid email or password.";
        }
    } else {
        $error_msg = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bazaarly</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo file_exists('css/style.css') ? filemtime('css/style.css') : '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

<div class="auth-container">
    <div class="auth-card">
        <!-- Logo Header -->
        <div class="auth-header">
            <a href="index.php" class="auth-logo-link">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" class="auth-logo-svg">
                    <g id="logo-mark" fill="none" stroke="#24426a" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 230 180 C 230 150, 270 150, 270 180 L 270 200 L 230 200 Z" stroke-width="5" fill="none" />
                        <path d="M 215 200 L 285 200 L 280 250 C 265 258, 235 258, 220 250 Z" stroke-width="5" fill="none" />
                        <path d="M 290 190 C 315 220, 310 270, 260 300 C 230 315, 190 310, 175 300 C 210 308, 255 300, 280 270 C 298 248, 298 210, 290 190 Z" fill="#f97c06" stroke="none" transform="translate(15, -10)" />
                    </g>
                    <text x="250" y="345" font-family="system-ui, -apple-system, sans-serif" font-size="45" font-weight="700" fill="#24426a" text-anchor="middle" letter-spacing="3">BAZAARLY</text>
                </svg>
            </a>
            <h2>Welcome Back</h2>
            <p>Please enter your credentials to log in.</p> 
        </div>

        <!-- Success Alert -->
        <?php if(!empty($success_msg)): ?>
            <div class="auth-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo htmlspecialchars($success_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if(!empty($error_msg)): ?>
            <div class="auth-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" placeholder="name@example.com" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)"></i>
                </div>
            </div>

            <div class="form-options">
                <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn-auth-submit">
                <span>Login</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <!-- Footer Link -->
        <div class="auth-footer">
            <p>You don't have an account? <a href="signup.php">Create Account</a></p>
        </div>
    </div>
</div>

<script src="js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>