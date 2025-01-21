-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2024 at 08:29 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `barcode`, `product_name`, `description`, `price`, `quantity`, `category`, `created_at`) VALUES
(3, 'ELEC001', 'Bluetooth Speaker', 'High-quality portable Bluetooth speaker with excellent bass.', '1499.00', 100, 'Electronics and Gadgets', '2024-11-27 11:15:23'),
(4, 'asdas', 'Organic Brown Rice (1kg)', '100% organic, gluten-free brown rice perfect for healthy meals.', '199.00', 100, 'Food', '2024-11-27 11:17:31'),
(5, 'CLOTH001', 'Men\'s Cotton T-Shirt', 'Comfortable and stylish cotton T-shirt for everyday wear.', '499.00', 100, 'Clothing', '2024-11-27 11:26:59'),
(6, 'BEAUTY001', 'Hydrating Face Cream', 'Lightweight, non-greasy face cream for all skin types.', '799.00', 100, 'Health and Beauty', '2024-11-27 11:33:15'),
(7, 'BOOK001', 'Learn PHP in 7 Days', 'Beginner-friendly book to master PHP programming.', '349.00', 100, 'Books', '2024-11-27 11:34:12'),
(8, 'HOME001', 'Electric Kettle', '1.7L electric kettle with auto shut-off and boil-dry protection.', '999.00', 100, 'Home and Kitchen Appliances', '2024-11-27 11:34:47'),
(9, 'TOY001', 'Remote-Controlled Car', 'Durable and fast RC car with multiple speed settings.', '1299.00', 100, 'Toys', '2024-11-27 11:36:06'),
(10, 'SPORT001', 'Yoga Mat', 'Non-slip yoga mat with extra cushioning for comfort.', '899.00', 100, 'Workout Equipment', '2024-11-27 11:37:24'),
(11, 'STATION001', 'Gel Ink Pens (Pack of 10)', 'Smooth-flowing gel ink pens for professional and personal use.', '159.00', 100, 'Stationery', '2024-11-27 11:38:09'),
(12, 'KITCH001', 'Stainless Steel Knife Set', 'High-quality knife set with ergonomic handles.', '2499.00', 100, 'Home and Kitchen Appliances', '2024-11-27 11:38:50'),
(14, 'GYM1001', 'Dumbbell (45lbs)', 'Gym Equipment', '1499.00', 100, 'Gym Equipment', '2024-11-29 16:10:27'),
(15, 'GYM20220', 'Lifting Straps', 'For better pulling', '250.00', 100, 'Gym Equipment ', '2024-12-07 14:36:31'),
(16, 'ASDHB2921AS', 'Clip electric fan', 'Clip electric fan', '100.00', 100, 'Home and Kitchen Appliances', '2024-12-07 19:19:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `contact_info`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin_user@gmail.com', '$2y$10$8R9EGskdOgeYZLI8Uesud.kN7tr7vEFMqEF01uON3qLHOx3ZmrILK', '1234', 'admin', '2024-12-07 15:40:30'),
(2, 'User01', 'User01@gmail.com', '$2y$10$xaGwHMIThZ0Nb8mQ189e2.Jw4utKcGwFjZKtYe/s9i2ZAmqKa3q8q', '1234', 'user', '2024-12-07 16:06:27'),
(3, 'User02', 'User02@gmail.com', '$2y$10$yRupeEx5apnjo4iyNC00kOTPik5/g/acWhP7hlDzHq01CmFJ/ncRy', '1234', 'user', '2024-12-07 17:32:34'),
(4, 'User03', 'User03@gmail.com', '$2y$10$8PCgtCb7TjqXgW.LUa3cyOcVwqUwBuqgYnw7pI0KFf460/wba5ioa', '1234', 'user', '2024-12-07 19:24:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
