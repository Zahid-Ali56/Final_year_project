<?php include("config.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Ecommerce</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <nav>
        <h2>MyShop</h2>
        
       <!-- <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search products...">
            <button type="submit">Search</button>
        </form>-->
        <ul>
            <li><a href="index.php">Home</a></li>

            <?php if(isset($_SESSION['user'])) { ?>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php } else { ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Signup</a></li>
            <?php } ?>
        </ul>
    </nav>
</header>