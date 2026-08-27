<?php 
include("config.php");

$error_msg = "";
$user_verified = false;
$email = "";

if (isset($_POST['verify'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND phone='$phone'");
    if ($check && mysqli_num_rows($check) > 0) {
        $user_verified = true;
    } else {
        $error_msg = "Invalid email or phone number combination.";
    }
}

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 3) {
        $error_msg = "Password must be at least 3 characters long.";
        $user_verified = true;
    } elseif (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error_msg = "Password must contain both letters and numbers.";
        $user_verified = true;
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
        $user_verified = true;
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE email='$email'");
        
        if ($update) {
            header("Location: login.php?reset=success");
            exit();
        } else {
            $error_msg = "Failed to update password. Try again.";
            $user_verified = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Bazaarly</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo file_exists('css/style.css') ? filemtime('css/style.css') : '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
  
</head>
<body class="auth-body">

<div class="auth-container">
    <div class="auth-card">
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
            <h2>Reset Password</h2>
            <p><?php echo $user_verified ? "Enter your new password." : "Verify your account details below."; ?></p>
        </div>

        <?php if(!empty($error_msg)): ?>
            <div class="auth-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <?php if(!$user_verified): ?>
            <!-- Step 1: Verification Form -->
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Registered Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-phone input-icon"></i>
                        <input type="text" id="phone" name="phone" placeholder="+92 300 1234567" required autocomplete="off">
                    </div>
                </div>

                <button type="submit" name="verify" class="btn-auth-submit">
                    <span>Verify Account</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        <?php else: ?>
            <!-- Step 2: New Password Form -->
            <form method="POST" class="auth-form">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="new_password" name="new_password" placeholder="Pass123" required>
                        <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('new_password', this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Pass123" required>
                        <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                    </div>
                </div>

                <button type="submit" name="reset_password" class="btn-auth-submit">
                    <span>Update Password</span>
                    <i class="fa-solid fa-check"></i>
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            <p>Remembered your password? <a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

<script src="js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>