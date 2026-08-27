<?php
include("config.php");

$id = $_GET['id'];

// increase views
mysqli_query($conn, "UPDATE products SET views = views + 1 WHERE id = $id");

$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $id"));
?>

<h2><?php echo $product['name']; ?></h2>
<p>Price: PKR <?php echo $product['price']; ?></p>

<a href="add_to_cart.php?id=<?php echo $product['id']; ?>">Add to Cart</a>