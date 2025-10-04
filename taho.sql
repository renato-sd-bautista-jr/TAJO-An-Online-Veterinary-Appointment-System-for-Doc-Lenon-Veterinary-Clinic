-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 11:27 PM
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
(58, '2025-10-04', '15:00:00', 'Snowy', 'Dog', 'Paula Reyes', '09326789123', 'Vaccination', 'Myxomatosis', 'Cancelled', '2025-10-03 10:10:45'),
(59, '2025-10-04', '11:00:00', 'Shadow', 'Cat', 'Quinn Santos', '09337891234', 'Checkup', 'Sneezing', 'Cancelled', '2025-10-03 10:10:45'),
(61, '2025-10-04', '14:00:00', 'Mochi', 'Cat', 'Sophia Lim', '09359012356', 'Grooming', 'Nail trim', 'Cancelled', '2025-10-03 10:10:45'),
(62, '2025-10-04', '16:00:00', 'Bubbles', 'Fish', 'Timothy Go', '09360123467', 'Consultation', 'White spots', 'Cancelled', '2025-10-03 10:10:45'),
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
(78, '2025-10-05', '09:00:00', 'snoop', 'Cat', 'thea', '11654863', 'Checkup', 'what da dog doin', 'Pending', '2025-10-03 13:00:49'),
(80, '2025-10-15', '11:00:00', 'hassan', 'Dog', 'alvin', '234135235333', 'Vaccination', 'werw', 'Pending', '2025-10-04 21:24:46');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(2, 'Cat Food'),
(1, 'Dog Food'),
(5, 'Grooming Supplies'),
(9, 'Leashes & Collars'),
(6, 'Medications'),
(3, 'Pet Accessories'),
(8, 'Pet Beds'),
(7, 'Toys'),
(4, 'Vitamins & Supplements');

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
(1, 'Water', 'Dont forget to stay hydrated,even for your furpets', 'Pet Wellness', 'post_images/1759611132_1758013764_Screenshot_2024-09-07-04-50-20-345_com.facebook.katana.png', '2025-04-05 20:54:33', '2025-10-05 04:52:12'),
(2, 'Free Vaccination', 'Free 1 vaccination after you vaccine 2 pet', 'Vaccination', 'post_images/1759611067_1758013764_Screenshot_2024-09-07-04-50-20-345_com.facebook.katana.png', '2025-04-05 22:55:46', '2025-10-05 04:51:07');

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
(12, 'Whiskas Cat Food 1kg', 'Cat Food', 100, 'product_images/1758610895_1745307991_8.png', 22),
(13, 'Pet Stainless feed bowl', 'Pet Accessories', 120, 'product_images/1759607906_1745306006_16.png', 78),
(14, 'Catnip Garden ', 'Cat Food', 200, 'product_images/1759608162_1745307675_18 (5).png', 70),
(15, 'Catnip Garden ', 'Cat Food', 200, 'product_images/1759608268_1745307675_18 (5).png', 70);

-- --------------------------------------------------------

--
-- Table structure for table `servicecategory`
--

CREATE TABLE `servicecategory` (
  `ID` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicecategory`
--

INSERT INTO `servicecategory` (`ID`, `Category_Name`, `Created_At`) VALUES
(1, 'Pet Wellness', '2025-10-04 20:50:12'),
(2, 'Consultation', '2025-10-04 20:50:12'),
(3, 'Vaccination', '2025-10-04 20:50:12'),
(4, 'Deworming', '2025-10-04 20:50:12'),
(5, 'Laboratory', '2025-10-04 20:50:12'),
(6, 'Surgery', '2025-10-04 20:50:12'),
(7, 'Confinement', '2025-10-04 20:50:12'),
(8, 'Grooming', '2025-10-04 20:50:12'),
(9, 'Pet Boarding', '2025-10-04 20:50:12');

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
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- Indexes for table `servicecategory`
--
ALTER TABLE `servicecategory`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `servicecategory`
--
ALTER TABLE `servicecategory`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
