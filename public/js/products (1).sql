-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2021 at 10:52 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dodostock`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_code` varchar(255) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `from_where` int(11) NOT NULL COMMENT '1 = add product, 2 = genereate qr code',
  `price` varchar(255) DEFAULT NULL,
  `weight` varchar(255) DEFAULT NULL,
  `pack` varchar(255) DEFAULT NULL,
  `cost_pc` int(10) DEFAULT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `specifications` text NOT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `alert_stock` int(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category_id`, `shop_id`, `image`, `product_code`, `seller_id`, `warehouse_id`, `from_where`, `price`, `weight`, `pack`, `cost_pc`, `currency`, `specifications`, `created_at`, `updated_at`, `alert_stock`) VALUES
(4, 'laptop', NULL, NULL, 'uploads/product/1618952970eY__vhHk_400x400.png', 'lp-123', 15, NULL, 1, '200', '23', '34', NULL, NULL, 'qweer', '2021-04-04', '2021-04-21', 0),
(5, 'eaeasdads', NULL, NULL, 'uploads/product/1618953006asif_passport.png', 'lp-12ss', 15, NULL, 2, '1231', '12312', '123123', NULL, NULL, '', '2021-04-04', '2021-04-21', 0),
(6, 'asdasd', NULL, NULL, 'uploads/product/1618997959asif_passport.png', 'li-23ww', 15, NULL, 2, '23123', '1231', '31', 123, 'asd', 'asdasd', '2021-04-04', '2021-04-21', 0),
(7, 'new', NULL, NULL, 'uploads/product/1617570118pro-wired-mouse-rgb-hero-new.png', 'qw-233', 15, NULL, 1, NULL, '', NULL, NULL, NULL, '', '2021-04-05', '2021-04-05', 0),
(8, NULL, NULL, NULL, NULL, 'tp-123', 15, NULL, 2, NULL, '', NULL, NULL, NULL, '', '2021-04-06', '2021-04-06', 0),
(9, 'lifi device', NULL, NULL, 'uploads/product/1618953048asif_passport.png', 'lf-123', 15, NULL, 2, '23', '32234', '1212', 123, '4', '', '2021-04-06', '2021-04-21', 0),
(33, 'test pro', 15, 0, 'uploads/product/1618946039asif_passport.png', 'p-102', 15, NULL, 1, '200', '50', '2', 22, '$', 'Specifications', '2021-04-21', '2021-04-21', 0),
(34, 'pen', 15, NULL, 'uploads/product/1618955323Screenshot_1.png', 'p102', 15, NULL, 1, '33', '23', '3', 2, '$', 'asdasdasd', '2021-04-21', '2021-04-21', 0),
(35, 'mouse-100', 15, NULL, 'uploads/product/pro-wired-mouse-rgb-hero-new.png', 'm-102', 15, NULL, 0, '100', '20', NULL, NULL, NULL, '', '2021-04-21', '2021-04-21', 0),
(36, 'mouse-999', NULL, NULL, 'uploads/product/1618997733asif_passport.png', 'm-103', 15, NULL, 0, '2151', '45', '12313', 123, '@', '', '2021-04-21', '2021-04-21', 0),
(37, 'mouse-11', NULL, NULL, 'uploads/product/pro-wired-mouse-rgb-hero-new.png', 'm-111', 15, NULL, 0, '234', '23', NULL, NULL, NULL, '', '2021-04-21', '2021-04-21', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
