-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 20, 2025 at 08:13 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `slsupplyhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `barangay` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `email`, `street`, `barangay`, `city`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
(29, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 1, '2025-05-06 09:52:31', '2025-05-06 09:52:31'),
(59, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 11:42:10', '2025-05-06 11:42:10'),
(60, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 11:44:41', '2025-05-06 11:44:41'),
(61, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 11:52:59', '2025-05-06 11:52:59'),
(62, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 11:54:05', '2025-05-06 11:54:05'),
(63, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 13:40:07', '2025-05-06 13:40:07'),
(64, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 13:41:15', '2025-05-06 13:41:15'),
(65, 2, 'Mark Steven', 'Peligro', '09566434376', 'kmar0956@gmail.com', 'Paku, Real St.', 'Poblacion', 'Bontoc', '6604', 0, '2025-05-06 13:47:52', '2025-05-06 13:47:52');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Groceries', 'Food and household essentials', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(2, 'Electronics', 'Electronic devices and accessories', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(3, 'Fashion', 'Clothing and accessories', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(4, 'Home & Living', 'Home decor and furniture', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(5, 'Health & Beauty', 'Personal care and wellness products', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(6, 'Sports & Outdoor', 'Sports equipment and outdoor gear', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(7, 'Books & Stationery', 'Books, office, and school supplies', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(8, 'Toys & Games', 'Toys, games, and entertainment', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(9, 'Automotive', 'Car parts and accessories', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(10, 'Pet Supplies', 'Pet food and accessories', NULL, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(11, 'Fresh Food', 'Fresh fruits, vegetables, and meat', 1, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(12, 'Packaged Food', 'Canned goods, snacks, and beverages', 1, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(13, 'Smartphones', 'Mobile phones and accessories', 2, '2025-05-05 20:19:25', '2025-05-05 20:19:25'),
(14, 'Computers', 'Laptops, desktops, and accessories', 2, '2025-05-05 20:19:25', '2025-05-05 20:19:25');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `vehicle_plate` varchar(20) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_deliveries` int DEFAULT '0',
  `status` enum('available','busy','offline') DEFAULT 'offline',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `order_id` int NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `driver_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `type` enum('supplier','driver','product') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Triggers `feedback`
--
DELIMITER $$
CREATE TRIGGER `update_product_rating_after_feedback_delete` AFTER DELETE ON `feedback` FOR EACH ROW BEGIN
    IF OLD.type = 'product' THEN
        UPDATE products p
        SET p.rating = COALESCE((
            SELECT AVG(rating)
            FROM feedback
            WHERE product_id = OLD.product_id
            AND type = 'product'
        ), 0),
        p.review_count = (
            SELECT COUNT(*)
            FROM feedback
            WHERE product_id = OLD.product_id
            AND type = 'product'
        )
        WHERE p.id = OLD.product_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_product_rating_after_feedback_insert` AFTER INSERT ON `feedback` FOR EACH ROW BEGIN
    IF NEW.type = 'product' THEN
        UPDATE products p
        SET p.rating = (
            SELECT AVG(rating)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        ),
        p.review_count = (
            SELECT COUNT(*)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        )
        WHERE p.id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_product_rating_after_feedback_update` AFTER UPDATE ON `feedback` FOR EACH ROW BEGIN
    IF NEW.type = 'product' THEN
        UPDATE products p
        SET p.rating = (
            SELECT AVG(rating)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        ),
        p.review_count = (
            SELECT COUNT(*)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        )
        WHERE p.id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_rewards`
--

CREATE TABLE `loyalty_rewards` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `transaction_count` int DEFAULT '0',
  `tier` varchar(20) DEFAULT 'None',
  `reward_amount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_sent` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `driver_id` int DEFAULT NULL,
  `address_id` int NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','assigned','picked_up','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `supplier_id`, `driver_id`, `address_id`, `order_number`, `subtotal`, `total_amount`, `delivery_fee`, `status`, `payment_status`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(3, 2, 21, NULL, 29, 'ORD1746649906-2', '58429.00', '61350.45', '0.00', 'pending', 'pending', 'cod', '', '2025-05-07 20:49:26', '2025-05-07 20:49:26'),
(6, 2, 21, NULL, 29, 'ORD1746652480-2', '58429.00', '61350.45', '0.00', 'pending', 'pending', 'cod', '', '2025-05-07 21:14:40', '2025-05-07 21:14:40'),
(7, 2, 21, NULL, 29, 'ORD1746652539-2', '55000.00', '57750.00', '0.00', 'pending', 'pending', 'cod', '', '2025-05-07 21:15:39', '2025-05-07 21:15:39'),
(8, 2, 21, NULL, 29, 'ORD1746653746-2', '3429.00', '3600.45', '0.00', 'pending', 'pending', 'cod', '', '2025-05-07 21:35:46', '2025-05-07 21:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `created_at`) VALUES
(1, 6, 3, 1, '55000.00', '55000.00', '2025-05-07 21:14:40'),
(2, 6, 4, 1, '3429.00', '3429.00', '2025-05-07 21:14:40'),
(3, 7, 3, 1, '55000.00', '55000.00', '2025-05-07 21:15:39'),
(4, 8, 4, 1, '3429.00', '3429.00', '2025-05-07 21:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `regular_price` decimal(10,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `unit` varchar(20) NOT NULL,
  `minimum_order` int DEFAULT '1',
  `rating` decimal(3,2) DEFAULT '0.00',
  `review_count` int DEFAULT '0',
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `category_id`, `name`, `description`, `price`, `regular_price`, `stock`, `unit`, `minimum_order`, `rating`, `review_count`, `status`, `created_at`, `updated_at`, `image_path`) VALUES
(3, 21, 14, 'ROG Zephyrus G14 (2025) GA403WP-QS024WSM', 'Windows 11 Home\n\nNVIDIA® GeForce RTX™ 5070 Laptop GPU\n\nAMD XDNA™ NPU up to 50TOPS\n\nAMD Ryzen™ AI 9 HX 370 Processor\n\n14\" 3K (2880 x 1800) 16:10 120Hz OLED ROG Nebula Display\n\n1TB M.2 NVMe™ PCIe® 4.0 SSD storage', '55000.00', '55000.00', 1000, 'pack', 1, '0.00', 0, 'active', '2025-05-05 21:50:32', '2025-05-05 22:50:56', 'uploads/images/file_681932a80658a2.74126321.png'),
(4, 21, 14, 'itel A50 RAM 8GB (3GB+5GB)+ROM 64GB | 6.6\" Big Screen | 5000mAh Big Battery 10W TypeC | AI Camera', '⭕️Size: 163.95 x 75.7 x 8.7 mm\r\n\r\n⭕️Weight（g）: 185g\r\n\r\n⭕️CPU: T603 Octa-core LTE Chipset\r\n\r\n⭕️Memory:3gb(extend to 8gb)+64gb\r\n\r\n⭕️Display: 6.6\" Big Screen with Dynamic Island 1612 x 720 Pixels\r\n\r\n⭕️Camera: 8MP Rear Cam 5MP Front Cam\r\n\r\n⭕️Battery: 5000mAh 10W Charging Type-C\r\n\r\n⭕️Network: 4G\r\n\r\n⭕️Wifi: 2.4G\r\n\r\n⭕️Bluetooth: 4.2\r\n\r\n⭕️Unlock: Side Fingerprint/Face Unlock\r\n\r\n⭕️Inbox: Phone*1 Charger*1 Cable*1 Phonecase*1\r\n\r\n⭕️Warraty: 12months', '3429.00', '3429.00', 98, 'pack', 5, '0.00', 0, 'active', '2025-05-06 06:07:56', '2025-05-07 21:14:40', 'uploads/images/file_6819a73cbb1814.90967553.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `business_address` text NOT NULL,
  `business_phone` varchar(20) NOT NULL,
  `business_email` varchar(255) NOT NULL,
  `business_permit_number` varchar(50) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_orders` int DEFAULT '0',
  `status` enum('pending','approved','suspended') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `business_permit_file` varchar(255) DEFAULT NULL,
  `tax_certificate_file` varchar(255) DEFAULT NULL,
  `store_photos_json` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `user_id`, `business_name`, `business_address`, `business_phone`, `business_email`, `business_permit_number`, `tax_id`, `rating`, `total_orders`, `status`, `created_at`, `updated_at`, `business_permit_file`, `tax_certificate_file`, `store_photos_json`) VALUES
(1, 21, 'Mark\'s Steven Store', 'Paku, Bontoc So. Leyte', '09566434376', 'kmar0956@gmail.com', '323231', NULL, '0.00', 0, 'approved', '2025-05-05 22:36:58', '2025-05-05 22:44:56', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_business_info`
--

CREATE TABLE `supplier_business_info` (
  `id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `business_type` enum('Individual','Corporation','Partnership') NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `shop_description` text,
  `operating_hours` json DEFAULT NULL,
  `delivery_areas` text,
  `return_policy` text,
  `cod_areas` text,
  `min_processing_days` int DEFAULT '1',
  `max_processing_days` int DEFAULT '3',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_photos`
--

CREATE TABLE `supplier_photos` (
  `id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT '0',
  `role` enum('customer','supplier','driver','admin') DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `fullname`, `phone`, `status`, `email_verified`, `role`, `created_at`, `updated_at`) VALUES
(2, 'kmar0956@gmail.com', '$2y$10$sNJeJned.5deU2UIlGS3f.JdouVMJsiTVANnykMHvqwfP/zmjfDh2', 'Mark Steven B. Peligro', NULL, 'active', 0, 'customer', '2025-05-04 03:32:20', '2025-05-04 03:32:20'),
(8, 'test@test.com', '$2y$10$dNS4sjzFfv238dVvuPfdkeniKcamP0bS8LCRaJwwiM/knrhQXrwZy', 'test', NULL, 'active', 0, 'customer', '2025-05-04 03:48:37', '2025-05-04 03:48:37'),
(9, 'support.slsupply@gmail.com', '$2y$10$77GVgdelKipZiXBNTkQeF.gJWVKkosPcHLr..rkB9vJB6xTDuGzPW', 'Administrator', NULL, 'active', 0, 'admin', '2025-05-04 09:48:15', '2025-05-04 09:51:21'),
(21, 'smorphyguy12@gmail.com', '$2y$10$fZy7NBe0as9i8NlSFc8ZhupxSoQO2pdwNqDufrsbILSYHrQGoMWJu', 'Mark Steven B. Peligro', '09566434376', 'active', 0, 'supplier', '2025-05-04 14:29:49', '2025-05-04 14:29:49'),
(25, 'markpeligro1234@gmail.com', '$2y$10$hsLHkysT9Z/2NHWfSVWmP.24SASO/AL6peFAcWi0tu4lSvW2TualG', 'Mark Steven', '09566434376', 'active', 0, 'supplier', '2025-05-20 07:47:57', '2025-05-20 07:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `customer_id`, `product_id`, `created_at`) VALUES
(1, 2, 3, '2025-05-05 23:33:40'),
(10, 2, 4, '2025-05-06 13:01:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_default` (`user_id`,`is_default`),
  ADD KEY `idx_city_barangay` (`city`,`barangay`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_feedback_type` (`type`),
  ADD KEY `idx_feedback_product` (`product_id`,`type`),
  ADD KEY `idx_feedback_supplier` (`supplier_id`,`type`),
  ADD KEY `idx_feedback_driver` (`driver_id`,`type`),
  ADD KEY `idx_feedback_customer` (`customer_id`);

--
-- Indexes for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loyalty_customer` (`customer_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_supplier` (`supplier_id`),
  ADD KEY `idx_orders_driver` (`driver_id`),
  ADD KEY `orders_ibfk_4` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_supplier` (`supplier_id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_review_count` (`review_count`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `supplier_business_info`
--
ALTER TABLE `supplier_business_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_photos`
--
ALTER TABLE `supplier_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_customer_product` (`customer_id`,`product_id`),
  ADD KEY `idx_wishlist_customer` (`customer_id`),
  ADD KEY `idx_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier_business_info`
--
ALTER TABLE `supplier_business_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_photos`
--
ALTER TABLE `supplier_photos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `email_verification_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_4` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_5` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD CONSTRAINT `loyalty_rewards_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_business_info`
--
ALTER TABLE `supplier_business_info`
  ADD CONSTRAINT `supplier_business_info_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_photos`
--
ALTER TABLE `supplier_photos`
  ADD CONSTRAINT `supplier_photos_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
