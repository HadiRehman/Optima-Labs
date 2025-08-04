-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 12:02 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `optimalabs_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `cat_no` varchar(100) NOT NULL,
  `batch_no` varchar(50) NOT NULL,
  `cas_no` varchar(100) DEFAULT NULL,
  `synonyms` varchar(200) DEFAULT NULL,
  `chemical_name` varchar(255) DEFAULT NULL,
  `mg` varchar(50) DEFAULT NULL,
  `molecular_formula` varchar(255) DEFAULT NULL,
  `solubility` varchar(200) DEFAULT NULL,
  `storage` text DEFAULT NULL,
  `shipping_condition` text DEFAULT NULL,
  `specification` varchar(255) DEFAULT NULL,
  `purity` varchar(50) DEFAULT NULL,
  `lab_test_number` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `retest_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `product_name`, `cat_no`, `batch_no`, `cas_no`, `synonyms`, `chemical_name`, `mg`, `molecular_formula`, `solubility`, `storage`, `shipping_condition`, `specification`, `purity`, `lab_test_number`, `note`, `test_date`, `retest_date`, `created_at`) VALUES
(3, 'BPC-157 5mg', 'BPC5-024', '25066A', '1628202-19-6', 'Not Available / Experimental Peptide / Blend of TWO or more', 'BPC-157 5mg', '5mg', 'C64H102N16O24', 'Water (Slightly) / Methanol (Slightly)', 'Powder: -20°C 3 years ; 4°C 2 years ; 15°C 3 months In solvent: -80°C 6 months ; -2°C 1 month ; 10°C 1 week', 'Suitable to be shipped at ambient temperature.', 'White Powder', '99.1%', '866402', 'Product has not been fully validated for medical applications. For research use only.', '2025-07-25', '2025-09-25', '2025-07-28 18:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'bd6fc72d2064f373d40a3a9569dc3bcf', '2025-07-25 23:32:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
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
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
