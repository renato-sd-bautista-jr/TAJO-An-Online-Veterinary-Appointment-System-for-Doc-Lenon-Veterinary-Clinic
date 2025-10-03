-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2025 at 04:13 PM
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

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `appointment_date`, `appointment_time`, `pet_name`, `pet_type`, `owner_name`, `owner_phone`, `service_type`, `notes`, `status`, `created_at`) VALUES
(21, '2025-10-30', '09:00:00', 'snow', 'Cat', 'renato', '09654325678', 'Checkup', 'vomit', 'Completed', '2025-09-30 19:22:46'),
(22, '2025-10-30', '10:00:00', 'jess', 'Cat', 'renato', '2948712', 'Grooming', 'dirty cat', 'Pending', '2025-10-01 15:51:14'),
(23, '2025-10-02', '09:00:00', 'Buddy', 'Dog', 'Alice Johnson', '09171234567', 'Vaccination', 'First vaccine', 'Cancelled', '2025-10-03 10:09:08'),
(24, '2025-10-02', '10:00:00', 'Mittens', 'Cat', 'Brian Smith', '09182345678', 'Checkup', 'Coughing issue', 'Cancelled', '2025-10-03 10:09:08'),
(25, '2025-10-02', '11:00:00', 'Charlie', 'Dog', 'Cathy Lee', '09193456789', 'Grooming', 'Full grooming', 'Completed', '2025-10-03 10:09:08'),
(26, '2025-10-02', '01:00:00', 'Luna', 'Cat', 'Daniel Cruz', '09204567891', 'Checkup', 'Yearly check', 'Cancelled', '2025-10-03 10:09:08'),
(27, '2025-10-02', '02:00:00', 'Rocky', 'Dog', 'Ella Torres', '09215678912', 'Surgery', 'Neutering', 'Cancelled', '2025-10-03 10:09:08'),
(28, '2025-10-02', '03:00:00', 'Coco', 'Parrot', 'Francis Yu', '09226789123', 'Consultation', 'Feather loss', 'Cancelled', '2025-10-03 10:09:08'),
(29, '2025-10-02', '04:00:00', 'Max', 'Dog', 'Grace Tan', '09237891234', 'Vaccination', 'Rabies shot', 'Cancelled', '2025-10-03 10:09:08'),
(53, '2025-10-03', '01:00:00', 'Milo', 'Cat', 'Karen Chan', '09271234578', 'Grooming', 'Bath and trim', 'Cancelled', '2025-10-03 10:10:45'),
(54, '2025-10-03', '02:00:00', 'Chico', 'Dog', 'Leo Ramos', '09282345689', 'Vaccination', 'Deworming', 'Cancelled', '2025-10-03 10:10:45'),
(55, '2025-10-03', '03:00:00', 'Nala', 'Cat', 'Megan Cruz', '09293456790', 'Consultation', 'Loss of appetite', 'Cancelled', '2025-10-03 10:10:45'),
(56, '2025-10-03', '04:00:00', 'Bruno', 'Dog', 'Nina Lopez', '09304567891', 'Checkup', 'Limping', 'Cancelled', '2025-10-03 10:10:45'),
(58, '2025-10-04', '15:00:00', 'Snowy', 'Dog', 'Paula Reyes', '09326789123', 'Vaccination', 'Myxomatosis', 'Confirmed', '2025-10-03 10:10:45'),
(59, '2025-10-04', '11:00:00', 'Shadow', 'Cat', 'Quinn Santos', '09337891234', 'Checkup', 'Sneezing', 'Confirmed', '2025-10-03 10:10:45'),
(61, '2025-10-04', '14:00:00', 'Mochi', 'Cat', 'Sophia Lim', '09359012356', 'Grooming', 'Nail trim', 'Confirmed', '2025-10-03 10:10:45'),
(62, '2025-10-04', '16:00:00', 'Bubbles', 'Fish', 'Timothy Go', '09360123467', 'Consultation', 'White spots', 'Confirmed', '2025-10-03 10:10:45'),
(63, '2025-08-05', '09:00:00', 'Bella', 'Dog', 'Alice Smith', '09171234567', 'Check-up', 'Routine annual check', 'Completed', '2025-08-01 02:00:00'),
(64, '2025-08-10', '11:00:00', 'Whiskers', 'Cat', 'John Doe', '09281234567', 'Vaccination', 'Rabies shot', 'Completed', '2025-08-05 01:30:00'),
(65, '2025-08-18', '01:00:00', 'Rex', 'Dog', 'Maria Garcia', '09391234567', 'Surgery', 'Minor surgery', 'Completed', '2025-08-10 06:00:00'),
(66, '2025-09-02', '10:00:00', 'Chloe', 'Cat', 'Robert Johnson', '09451234567', 'Check-up', 'Fever symptoms', 'Completed', '2025-09-01 04:00:00'),
(67, '2025-09-08', '03:00:00', 'Buddy', 'Dog', 'Emily Davis', '09561234567', 'Grooming', 'Full grooming service', 'Completed', '2025-09-05 01:00:00'),
(68, '2025-09-15', '02:00:00', 'Max', 'Dog', 'Daniel Lee', '09671234567', 'Vaccination', 'Deworming', 'Completed', '2025-09-07 07:20:00'),
(69, '2024-03-12', '10:00:00', 'Nala', 'Dog', 'Victor Reyes', '09123456789', 'Check-up', 'Puppy exam', 'Completed', '2024-03-05 00:00:00'),
(70, '2024-05-20', '02:00:00', 'Simba', 'Cat', 'Olivia Cruz', '09223456789', 'Surgery', 'Spay surgery', 'Completed', '2024-05-15 06:30:00'),
(71, '2024-07-08', '09:00:00', 'Shadow', 'Dog', 'Chris Lim', '09323456789', 'Vaccination', 'Anti-rabies', 'Completed', '2024-07-01 01:00:00'),
(72, '2024-09-18', '01:00:00', 'Luna', 'Cat', 'Samantha Tan', '09423456789', 'Check-up', 'Weight loss issue', 'Completed', '2024-09-10 05:00:00'),
(73, '2024-11-25', '03:00:00', 'Mochi', 'Rabbit', 'Ethan Young', '09523456789', 'Check-up', 'Dental exam', 'Completed', '2024-11-20 07:30:00'),
(74, '2025-07-12', '10:00:00', 'Buster', 'Dog', 'David Cruz', '09623456789', 'Check-up', 'Ear infection', 'Cancelled', '2025-07-05 02:00:00'),
(75, '2025-08-21', '02:00:00', 'Snowball', 'Cat', 'Sarah Lee', '09723456789', 'Vaccination', '2nd round vaccine', 'Cancelled', '2025-08-10 08:00:00'),
(76, '2025-09-30', '09:00:00', 'Lucky', 'Dog', 'Kevin Santos', '09823456789', 'Grooming', 'Bath & trim', 'Cancelled', '2025-09-20 02:00:00'),
(77, '2025-10-05', '04:00:00', 'mr aaaaaaaaaaaaaaaa', 'Dog', 'alvin', '6546879', 'Checkup', 'not barking', 'Pending', '2025-10-03 12:57:38'),
(78, '2025-10-05', '09:00:00', 'snoop', 'Cat', 'thea', '11654863', 'Checkup', 'what da dog doin', 'Pending', '2025-10-03 13:00:49');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_number`, `total_amount`, `status`, `created_at`) VALUES
(1, 'renats', '33333333', 100.00, 'Accepted', '2025-09-23 08:47:59'),
(2, 'erer', '555555555555', 400.00, 'Rejected', '2025-09-23 08:50:55'),
(3, 'renats', '555555555555', 200.00, 'Rejected', '2025-09-23 11:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`) VALUES
(1, 1, 12, 'Whiskas Cat Food 1kg', 100.00, 1),
(2, 2, 12, 'Whiskas Cat Food 1kg', 100.00, 4),
(3, 3, 12, 'Whiskas Cat Food 1kg', 100.00, 2);

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
  `Stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ID`, `Product_Name`, `Category`, `Price`, `Image`, `Stock`) VALUES
(12, 'Whiskas Cat Food 1kg', 'cat food', 100, 'product_images/1758610895_1745307991_8.png', 22);

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
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
