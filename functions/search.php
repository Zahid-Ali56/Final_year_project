<?php
// Secure & Clean Product Fetching Function
function getProducts($conn) {
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $search = mysqli_real_escape_string($conn, trim($_GET['search']));

        $query = "SELECT * FROM products 
                  WHERE name LIKE '%$search%' 
                  OR description LIKE '%$search%'
                  ORDER BY id DESC";
    } else {
        // Search na hone par default products
        $query = "SELECT * FROM products ORDER BY id DESC LIMIT 9";
    }

    return mysqli_query($conn, $query);
}