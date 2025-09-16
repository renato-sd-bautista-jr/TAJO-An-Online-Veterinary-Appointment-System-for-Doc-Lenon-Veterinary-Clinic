-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 08:19 AM
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
-- Database: `taho`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `pet_type` varchar(50) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `owner_phone` varchar(20) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Category` varchar(100) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp(),
  `Updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`ID`, `Title`, `Content`, `Category`, `Image`, `Created_at`, `Updated_at`) VALUES
(1, 'Testing', '<div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px; color: rgb(236, 236, 236); font-family: Arial, sans-serif; font-size: 14px; background-color: rgb(16, 18, 24);\"><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><b><span jsname=\"YS01Ge\">Ako ay isang model</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Doon sa Ermita</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Gabi-gabi sa disco</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">At nagpapabongga</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Sa pagka-istariray</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Talbog lahat sila</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ang mga foreigner</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ay nagkakandarapa</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">\'Pag ako\'y sumayaw na</span></b></div><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><b><span jsname=\"YS01Ge\">Ako\'y may nakilala</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Mestizo na Hapon</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Na-in-love siya sa akin</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Type n\'yang gawing girlfriend</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ako\'y niregaluhan</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Bahay, lupa\'t datung</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ang \'di n\'ya lang alam</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">At \'di ko masabi</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Na ako\'y isang...</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">\"Darna!\" (Darna! Darna! Darna!)</span></b></div><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><b><span jsname=\"YS01Ge\">Ang iniingat-ingatan ko</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ang ugat, lawit at muscle ko</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Na tinatago-tago ko pa</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Sa t\'wing kami\'y magkasama</span></b></div><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><b><span jsname=\"YS01Ge\">Ako\'y nananalangin</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Na sana\'y manawari</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">\'Wag sanang mabuking</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Nakatali kong akin</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Na ubod nang itim</span></b></div><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><b><span jsname=\"YS01Ge\">Ang iniingat-ingatan ko</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ang ugat, lawit at muscle ko</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Na tinatago-tago ko pa</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Sa t\'wing kami\'y magkasama</span></b></div><div jsname=\"U8S5sf\" class=\"ujudUb\" style=\"margin-bottom: 12px;\"><b><span jsname=\"YS01Ge\">Kami\'y biglang nagkita</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Nang \'di sinasadya</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Sa restroom ng lalaki</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Doon sa Megamall</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Napatingin siya sa \'kin</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ako\'y napahiya</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Sa galit ng Hapon</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Inumbag n\'ya ako</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">\"バカヤロ, what is that?\"</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">\"Just like yours, papa\"</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Sira ang beauty ko</span></b></div><p style=\"margin-bottom: 0px;\"><b><span jsname=\"YS01Ge\">Binawi pa\'ng lahat</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Bahay, lupa\'t datung</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ng nobyo kong Hapon</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Kaya ang beauty ko ngayon</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Nagtitinda na lang</span><br aria-hidden=\"true\"><span jsname=\"YS01Ge\">Ng itlog at talong</span></b></p></div></div>', 'Pet Wellness', 'post_images/1743857673_449699514_336036782881611_5525702645839532818_n.jpg', '2025-04-05 20:54:33', '2025-04-05 20:55:30'),
(2, 'Testing1', 'asd asd asd asd aasd asd aasd asd asd asd asd asd asdasdasdasda sa  da ads ads ads ad sad sa s ad dsa sda dsa sad ad sad as a as ', 'Consultation', 'post_images/1743864946_s1.jpg', '2025-04-05 22:55:46', '2025-04-05 22:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ID` int(11) NOT NULL,
  `Product_Name` varchar(255) NOT NULL,
  `Category` varchar(255) NOT NULL,
  `Price` int(11) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `Username`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Glenn Julius Almonte', 'glennjuliusalmonte@gmail.com', 'admin', '12345678', '2025-03-29 06:50:45', '2025-03-29 00:11:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
