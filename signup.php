<?php 
include("config.php"); 

$error_msg = "";

if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'])); // Optional field
    $raw_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Minimum 3 characters check
    if (strlen($raw_password) < 3) {
        $error_msg = "Password must be at least 3 characters long.";
    } 
    // 2. Strong Password Check: Must contain both letters and numbers
    elseif (!preg_match('/[A-Za-z]/', $raw_password) || !preg_match('/[0-9]/', $raw_password)) {
        $error_msg = "Password must contain both letters (A-Z, a-z) and numbers (0-9).";
    } 
    // 3. Confirm Password Match
    elseif ($raw_password !== $confirm_password) {
        $error_msg = "Password and Confirm Password do not match.";
    } 
    else {
        // 4. Check if email already exists
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error_msg = "An account with this email already exists!";
        } else {
            // Hash Password
            $pass = password_hash($raw_password, PASSWORD_DEFAULT);

            // Insert into Database
            $query = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$pass')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: login.php?signup=success");
                exit();
            } else {
                $error_msg = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Bazaarly</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo file_exists('css/style.css') ? filemtime('css/style.css') : '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">

<div class="auth-container">
    <div class="auth-card">
        
        <!-- Header -->
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
            <h2>Create Account</h2>
            <p>Enter your details to create a new account.</p> 
        </div>

        <!-- Error Notification -->
        <?php if(!empty($error_msg)): ?>
            <div class="auth-error" style="background-color: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-user input-icon"></i>
                    <input type="text" id="name" name="name" placeholder="John Doe" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autocomplete="off">
                </div>
            </div>

            <!-- Optional Phone Number -->
            <div class="form-group">
                <label for="phone">Phone Number <span style="font-weight: normal; color: #a0aec0; font-size: 12px;">(Optional)</span></label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-phone input-icon"></i>
                    <input type="text" id="phone" name="phone" placeholder="+92 300 1234567" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="e.g. Pass123" minlength="3" required>
                </div>
                <small style="color: #718096; font-size: 12px; display: block; margin-top: 4px;">Must contain letters and numbers (min 3 chars).</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" name="signup" class="btn-auth-submit">
                <span>Create Account</span>
                <i class="fa-solid fa-user-plus"></i>
            </button>
        </form>

        <!-- Footer Link -->
        <div class="auth-footer" style="margin-top: 20px; text-align: center;">
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>

    </div>
</div>

</body>
</html>