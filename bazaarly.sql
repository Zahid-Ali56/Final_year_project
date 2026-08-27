-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 09, 2026 at 10:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bazaarly`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(6, 5, 7, 2),
(25, 6, 7, 1),
(26, 6, 9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Clothes'),
(3, 'Furniture');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text NOT NULL,
  `payment_method` enum('Cash on Delivery','Online Payment') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`, `address`, `payment_method`) VALUES
(1, 2, 139650.00, 'Pending', '2026-05-02 06:54:28', '', 'Cash on Delivery'),
(2, 1, 2500.00, 'Pending', '2026-05-02 07:14:29', 'Karachi, Pakistan', 'Cash on Delivery'),
(3, 2, 90000.00, 'Pending', '2026-05-02 07:18:43', '', 'Cash on Delivery'),
(4, 2, 0.00, 'Pending', '2026-05-02 07:18:45', '', 'Cash on Delivery'),
(5, 2, 0.00, 'Pending', '2026-05-02 07:18:47', '', 'Cash on Delivery'),
(6, 2, 250.00, 'Pending', '2026-05-02 07:19:12', '', 'Cash on Delivery'),
(7, 2, 0.00, 'Pending', '2026-05-02 07:19:15', '', 'Cash on Delivery'),
(8, 2, 0.00, 'Pending', '2026-05-02 07:24:35', '', 'Cash on Delivery'),
(9, 2, 136950.00, 'Pending', '2026-05-02 07:36:25', 'karachi shanti nagar', 'Cash on Delivery'),
(10, 2, 0.00, 'Pending', '2026-05-02 07:36:48', 'karachi shanti nagar', 'Cash on Delivery'),
(11, 2, 0.00, 'Pending', '2026-05-02 07:37:10', 'karachi shanti nagar', 'Cash on Delivery'),
(12, 2, 850.00, 'Pending', '2026-05-02 07:38:14', 'hydrabad', 'Online Payment'),
(13, 2, 0.00, 'Pending', '2026-05-03 06:34:44', 'hydrabad', 'Online Payment'),
(14, 2, 45000.00, 'Pending', '2026-05-03 06:35:35', 'dadu', 'Cash on Delivery'),
(15, 2, 850.00, 'Pending', '2026-05-03 06:50:48', 'd', 'Cash on Delivery'),
(16, 2, 45000.00, 'Pending', '2026-05-03 07:06:13', 'hyd', 'Cash on Delivery'),
(17, 2, 0.00, 'Pending', '2026-05-03 07:08:53', 'hyd', 'Cash on Delivery'),
(18, 2, 850.00, 'Pending', '2026-05-03 07:09:19', 'a', 'Cash on Delivery'),
(19, 2, 45000.00, 'Pending', '2026-05-03 07:09:43', 'a', 'Cash on Delivery'),
(20, 2, 100.00, 'Pending', '2026-05-04 00:26:00', 'a', 'Cash on Delivery'),
(21, 2, 0.00, 'Pending', '2026-05-04 00:26:09', 'a', 'Cash on Delivery'),
(22, 2, 2400.00, 'Pending', '2026-05-04 00:26:43', 'b', 'Cash on Delivery'),
(23, 2, 0.00, 'Pending', '2026-05-04 00:26:53', 'a', 'Cash on Delivery');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 2, 100.00),
(2, 1, 3, 1, 1200.00),
(3, 1, 2, 1, 2500.00),
(4, 1, 8, 3, 250.00),
(5, 1, 9, 3, 45000.00),
(6, 3, 9, 2, 45000.00),
(7, 6, 8, 1, 250.00),
(8, 9, 8, 1, 250.00),
(9, 9, 9, 3, 45000.00),
(10, 9, 7, 2, 850.00),
(11, 12, 7, 1, 850.00),
(12, 14, 9, 1, 45000.00),
(13, 15, 7, 1, 850.00),
(14, 16, 9, 1, 45000.00),
(15, 18, 7, 1, 850.00),
(16, 19, 9, 1, 45000.00),
(17, 20, 1, 1, 100.00),
(18, 22, 1, 24, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `image4` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sizes` varchar(255) DEFAULT NULL,
  `colors` varchar(255) DEFAULT NULL,
  `model_specs` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `sold` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `image2`, `image3`, `image4`, `description`, `sizes`, `colors`, `model_specs`, `category_id`, `views`, `sold`) VALUES
(1, 'Shoes', 100.00, 'images/shoes.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 25),
(2, 'Watch', 2500.00, 'images/watch.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0),
(3, 'Bag', 1200.00, 'images/bag.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 0),
(4, 'Phone', 70000.00, 'images/phone.webp', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0),
(5, 'Headphones', 700.00, 'images/headphone.webp', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0),
(6, 'Laptop', 80000.00, 'images/laptop.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0),
(7, 'T-shirt', 850.00, 'images/tshirt.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 1),
(8, 'Sunglasses', 250.00, 'images/glasses.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0),
(9, 'Camera', 45000.00, 'images/camera.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

CREATE TABLE `search_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `search_term` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'Zahid', 'zahidjamali455@gmail.com', '$2y$10$2usZuCR9w9rL1fMGux6XJ.IlyEo1rKv5HdkUIwdZiCMCD6zODCao6'),
(2, 'Zahid', 'zahidjamali45@gmail.com', '$2y$10$ymhgAZtuWLOGegaNOuPYO.3nrwfyjWXgoKclm12g7DQBXGc6ZXOay'),
(3, 'zahid jamali', 'zahidjamali455@gmail.com', '$2y$10$FrSQpJWtwXbNy0j.ZrKby.CAxeZDBoCrYB7eSIB4jHNEloDV01Zqu'),
(5, 'zahid jamali', 'zahidjamali5@gmail.com', '$2y$10$gDcyBSDgD6VHpCdRnuza5.a0r2Bdz8Zjyt.vMP5XoSpEMKnw2/6T2'),
(6, 'Ali', 'ali@gmail.com', '$2y$10$KocADeb8zOSdTYAwlf.n9uAQtF97qLPaEq/YoLNpWhzeWNhmzjIbW');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_user` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_order` (`order_id`),
  ADD KEY `fk_items_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indexes for table `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

--users table mein phone column add kiya hai,
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
