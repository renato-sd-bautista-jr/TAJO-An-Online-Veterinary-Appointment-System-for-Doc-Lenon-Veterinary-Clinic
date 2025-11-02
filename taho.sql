-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 06:05 AM
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
  `owner_email` varchar(255) DEFAULT NULL,
  `service_type` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `appointment_date`, `appointment_time`, `pet_name`, `pet_type`, `owner_name`, `owner_phone`, `owner_email`, `service_type`, `notes`, `status`, `created_at`) VALUES
(23, '2025-10-02', '09:00:00', 'Buddy', 'Dog', 'Alice Johnson', '09171234567', NULL, 'Vaccination', 'First vaccine', 'Cancelled', '2025-10-03 10:09:08'),
(24, '2025-10-02', '10:00:00', 'Mittens', 'Cat', 'Brian Smith', '09182345678', NULL, 'Checkup', 'Coughing issue', 'Cancelled', '2025-10-03 10:09:08'),
(25, '2025-10-02', '11:00:00', 'Charlie', 'Dog', 'Cathy Lee', '09193456789', NULL, 'Grooming', 'Full grooming', 'Completed', '2025-10-03 10:09:08'),
(26, '2025-10-02', '01:00:00', 'Luna', 'Cat', 'Daniel Cruz', '09204567891', NULL, 'Checkup', 'Yearly check', 'Cancelled', '2025-10-03 10:09:08'),
(27, '2025-10-02', '02:00:00', 'Rocky', 'Dog', 'Ella Torres', '09215678912', NULL, 'Surgery', 'Neutering', 'Cancelled', '2025-10-03 10:09:08'),
(28, '2025-10-02', '03:00:00', 'Coco', 'Parrot', 'Francis Yu', '09226789123', NULL, 'Consultation', 'Feather loss', 'Cancelled', '2025-10-03 10:09:08'),
(29, '2025-10-02', '04:00:00', 'Max', 'Dog', 'Grace Tan', '09237891234', NULL, 'Vaccination', 'Rabies shot', 'Cancelled', '2025-10-03 10:09:08'),
(53, '2025-10-03', '01:00:00', 'Milo', 'Cat', 'Karen Chan', '09271234578', NULL, 'Grooming', 'Bath and trim', 'Cancelled', '2025-10-03 10:10:45'),
(54, '2025-10-03', '02:00:00', 'Chico', 'Dog', 'Leo Ramos', '09282345689', NULL, 'Vaccination', 'Deworming', 'Cancelled', '2025-10-03 10:10:45'),
(55, '2025-10-03', '03:00:00', 'Nala', 'Cat', 'Megan Cruz', '09293456790', NULL, 'Consultation', 'Loss of appetite', 'Cancelled', '2025-10-03 10:10:45'),
(56, '2025-10-03', '04:00:00', 'Bruno', 'Dog', 'Nina Lopez', '09304567891', NULL, 'Checkup', 'Limping', 'Cancelled', '2025-10-03 10:10:45'),
(58, '2025-10-04', '15:00:00', 'Snowy', 'Dog', 'Paula Reyes', '09326789123', NULL, 'Vaccination', 'Myxomatosis', 'Cancelled', '2025-10-03 10:10:45'),
(59, '2025-10-04', '11:00:00', 'Shadow', 'Cat', 'Quinn Santos', '09337891234', NULL, 'Checkup', 'Sneezing', 'Cancelled', '2025-10-03 10:10:45'),
(61, '2025-10-04', '14:00:00', 'Mochi', 'Cat', 'Sophia Lim', '09359012356', NULL, 'Grooming', 'Nail trim', 'Cancelled', '2025-10-03 10:10:45'),
(62, '2025-10-04', '16:00:00', 'Bubbles', 'Fish', 'Timothy Go', '09360123467', NULL, 'Consultation', 'White spots', 'Cancelled', '2025-10-03 10:10:45'),
(68, '2025-09-15', '02:00:00', 'Max', 'Dog', 'Daniel Lee', '09671234567', NULL, 'Vaccination', 'Deworming', 'Completed', '2025-09-07 07:20:00'),
(76, '2025-09-30', '09:00:00', 'Lucky', 'Dog', 'Kevin Santos', '09823456789', NULL, 'Grooming', 'Bath & trim', 'Cancelled', '2025-09-20 02:00:00'),
(77, '2025-10-05', '04:00:00', 'mr aaaaaaaaaaaaaaaa', 'Dog', 'alvin', '6546879', NULL, 'Checkup', 'not barking', 'Cancelled', '2025-10-03 12:57:38'),
(78, '2025-10-05', '09:00:00', 'snoop', 'Cat', 'thea', '11654863', NULL, 'Checkup', 'what da dog doin', 'Cancelled', '2025-10-03 13:00:49'),
(80, '2025-10-15', '11:00:00', 'hassan', 'Dog', 'alvin', '234135235333', NULL, 'Vaccination', 'werw', 'Cancelled', '2025-10-04 21:24:46'),
(81, '2025-10-08', '09:00:00', 'rencie', 'Cat', 'shang', '097543675422', NULL, 'Dental', '', 'Cancelled', '2025-10-08 02:16:47'),
(152, '2025-10-30', '14:00:00', 'qwe', 'Dog', 'qwe', 'qwe', NULL, 'Surgery', 'qwe', 'Confirmed', '2025-10-30 07:11:36'),
(153, '2025-10-30', '16:00:00', 'qwe', 'Cat', 'qwe', 'qwe', NULL, 'Surgery', 'qwe', 'Cancelled', '2025-10-30 07:11:46');

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
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notificationid` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `readstatus` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notificationid`, `username`, `action`, `date`, `readstatus`) VALUES
(4, 'admin', 'Order #5 has been Accepted by admin.', '2025-10-22 22:34:21', ''),
(5, 'flor', 'New order placed (#6) by flor.', '2025-10-22 22:43:08', ''),
(6, 'renats', 'New order (#7) placed with GCash payment.', '2025-10-22 23:32:09', ''),
(7, 'admin', 'Order #7 has been Accepted by admin.', '2025-10-22 23:32:25', ''),
(8, 'flor', 'New order (#8) placed with Pickup payment.', '2025-10-22 23:41:02', ''),
(9, 'admin', 'Order #6 has been Accepted by admin.', '2025-10-22 23:41:12', ''),
(10, 'admin', 'Order #8 has been Accepted by admin.', '2025-10-22 23:45:47', ''),
(11, '32141', 'New order (#9) placed with GCash payment.', '2025-10-30 08:19:30', '');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('GCash','Pickup') DEFAULT 'Pickup',
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_status` enum('Unpaid','Paid','To Claim','Completed') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_number`, `total_amount`, `status`, `created_at`, `payment_method`, `reference_number`, `payment_status`) VALUES
(5, 'yuu', '09583456653', 240.00, 'To Claim', '2025-10-22 20:34:12', 'Pickup', NULL, 'Unpaid'),
(6, 'flor', '09738274463', 400.00, 'Paid', '2025-10-22 20:43:08', 'Pickup', NULL, 'Unpaid'),
(7, 'renats', '09738274463', 120.00, 'Completed', '2025-10-22 21:32:09', 'GCash', '', 'To Claim'),
(8, 'flor', '09738274463', 360.00, 'Accepted', '2025-10-22 21:41:02', 'Pickup', '', 'Unpaid'),
(9, '32141', '12312312341', 200.00, 'Pending', '2025-10-30 07:19:30', 'GCash', '12412421412', 'To Claim');

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
(6, 5, 13, 'Pet Stainless feed bowl', 120.00, 2),
(7, 6, 14, 'Catnip Garden ', 200.00, 2),
(8, 7, 13, 'Pet Stainless feed bowl', 120.00, 1),
(9, 8, 13, 'Pet Stainless feed bowl', 120.00, 3),
(10, 9, 14, 'Catnip Garden ', 200.00, 1);

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
(3, 'Consultation', 'Pet Consultation\r\nRoutine consultations are a key part of maintaining your pet’s health and well-being. At Doclenon Veterinary Clinic, we believe that regular checkups are more than just appointments—they\'re opportunities to detect health issues early, receive expert advice, and give your pet the care they truly deserve.\r\nWhether you have a new puppy or kitten, an active adult pet, or a senior companion, regular pet consultations help track their growth, monitor changes, and ensure they\'re living their best life.\r\n\r\nWhy Schedule a Pet Consultation?\r\nEarly detection of potential health problems\r\n\r\n\r\nPersonalized recommendations for nutrition, behavior, and lifestyle\r\n\r\n\r\nMonitoring weight, dental health, and vital signs\r\n\r\n\r\nGuidance on vaccinations, parasite prevention, and grooming\r\n\r\n\r\nA chance to ask questions and get professional advice\r\n\r\n\r\nPeace of mind knowing your pet is healthy and happy\r\n\r\n\r\nOur licensed veterinarians take time to understand your pet’s unique needs. Every consultation includes a thorough physical exam and a discussion of your pet’s health history, habits, and environment.\r\n\r\nWhen to Schedule a Consultation\r\nAnnual wellness exams\r\n\r\n\r\nBefore starting a vaccination schedule\r\n\r\n\r\nIf you notice behavioral or appetite changes\r\n\r\n\r\nFor senior pet health monitoring\r\n\r\n\r\nFor travel, surgery, or boarding requirements\r\n\r\n\r\nWith Doclenon Veterinary Clinic Online System, booking a consultation is fast, convenient, and hassle-free—no more waiting in long lines or struggling to find the right schedule.\r\n\r\nGive your pet the gift of good health. Book a pet consultation today through TAJO: An Online Veterinary System for Doclenon Veterinary Clinic and stay one step ahead in your pet’s health journey.\r\n', 'Consultation', 'post_images/1761016575_1744100672_484052880_1099985988809585_7202418957427637986_n.jpg', '2025-10-08 09:47:46', '2025-10-21 11:16:15'),
(4, 'Pet Wellness', 'WHAT IS WELLNESS?\r\nAt Online Veterinary Appointment System, we believe pet wellness goes beyond just treating illnesses.\r\nIt’s about taking proactive steps to ensure your pet lives a happy and healthy life. Wellness isn’t just the\r\nabsence of disease; it’s the continuous effort to maintain and improve your pet’s well-being.\r\nThink about what wellness means for us. It’s more than just avoiding sickness. True wellness involves\r\nreducing the risk of illness by:\r\n- Eating a balanced diet\r\n- Maintaining a healthy weight\r\n- Regular exercise\r\n- Getting enough sleep\r\n- Practicing good hygiene\r\n- Building strong emotional bonds\r\n- Keeping the mind engaged with challenges\r\n- Regular health checkups\r\n- Preventive care, including vaccinations\r\nThis isn’t a passive approach. In fact, it requires effort and commitment. Wellness is an active process\r\nthat involves time, attention, and dedication to ensure your pet’s long-term health.\r\nWith the help of the Online Veterinary Appointment System, scheduling wellness checkups and\r\npreventive care for your pet has never been easier. Our platform allows you to book appointments\r\nconveniently, ensuring your pet receives the care they need without long wait times or unnecessary\r\nhassle.\r\nOnline Veterinary Appointment System emphasizes the importance of preventive care. By committing\r\ntime and effort now, you can help your pet stay comfortable, active, and happy for as long as possible.\r\nWhether it’s vaccinations, routine checkups, or expert advice, our system makes veterinary care\r\naccessible and stress-free for you and your pet.', 'Pet Wellness', 'post_images/1761016565_1744101059_OIP (10).jpg', '2025-10-08 09:58:19', '2025-10-21 11:16:05'),
(5, 'Vaccination', 'Pet Vaccination at TAJO\r\n\r\nVaccinations are one of the most effective ways to protect your pet from harmful and potentially\r\nlife-threatening diseases. At TAJO: An Online Veterinary System for Doclenon Veterinary Clinic, we\r\ntake a proactive approach to pet care by ensuring your dog or cat receives the right vaccines at the\r\nright time.\r\n\r\nWhether your pet is a playful puppy, a curious kitten, or a senior companion, staying on top of their\r\nvaccination schedule is essential to their health and safety. Vaccines not only prevent common\r\nillnesses—they also reduce the risk of outbreaks in your community and help your pet live a longer,\r\nhealthier life.\r\n\r\nDog Vaccinations\r\n\r\nDogs are often exposed to contagious diseases, especially when visiting parks, grooming salons, or\r\nboarding facilities. That’s why regular dog vaccinations are critical.\r\nJust brought home a new puppy? Start their protection early with a full set of puppy shots to build\r\nstrong immunity during their early months.\r\n\r\nRecommended Schedule for Dog Vaccinations:\r\n\r\nEvery 6 months – Intranasal Bordetella vaccine\r\nEvery year – Leptospirosis vaccine\r\nEvery 3 years – Distemper, Hepatitis, Parvovirus, Parainfluenza, and Rabies vaccines\r\n\r\nCat Vaccinations\r\n\r\nEven indoor cats are at risk of contracting airborne or contagious diseases. A window left open or an\r\nunexpected slip outside can put them in danger. Regular cat vaccinations help ensure your feline\r\nfriend stays safe and protected.\r\n\r\nRecommended Schedule for Cat Vaccinations:\r\nEvery year – Purevax Rabies vaccine, Feline Leukemia vaccine\r\n\r\nEvery 3 years – Feline distemper vaccine, Rhinotracheitis vaccine\r\n\r\nScheduling vaccinations through TAJO is quick and easy—no long waits, no stress. Protect your pets\r\nbefore they get sick by staying on top of their vaccination needs.\r\n\r\nBook your appointment today with TAJO: An Online Veterinary System for Doclenon Veterinary\r\nClinic and give your pet the protection they deserve.', 'Vaccination', 'post_images/1761016548_1744100929_OIP (8).jpg', '2025-10-08 09:59:44', '2025-10-21 11:15:48'),
(6, 'Deworming', 'DEWORMING CARE: PROTECTING YOUR PET FROM PARASITES\r\nAt TAJO, we know how essential it is to keep your pet free from harmful parasites that can affect their health and overall well-being. Our deworming services are designed to keep your pet protected, whether they are showing signs of parasitic infections or simply need routine preventive care.\r\nDeworming is recommended at regular intervals depending on your pet’s age, lifestyle, and health condition. It’s an essential part of maintaining your pet’s health and preventing serious health issues related to internal parasites.\r\nWHEN DEWORMING IS NECESSARY:\r\nFor puppies and kittens – Early deworming is crucial to protect them from common internal parasites that could affect their growth and development.\r\n\r\n\r\nFor adult pets – Regular deworming helps prevent infections from parasites like roundworms, tapeworms, and hookworms.\r\n\r\n\r\nFor pets with exposure to external environments – If your pet spends time outdoors or has contact with other animals, they may need more frequent deworming.\r\n\r\n\r\nIf symptoms of parasitic infection appear – If your pet shows signs like vomiting, diarrhea, weight loss, or a dull coat, deworming may be necessary.\r\n\r\n\r\nWHAT TO EXPECT DURING DEWORMING:\r\nThorough assessment – We’ll assess your pet’s health and lifestyle to determine the appropriate deworming treatment.\r\n\r\n\r\nTailored treatment – We use safe, effective medications that target specific types of parasites based on your pet’s needs.\r\n\r\n\r\nPost-treatment care – We’ll provide guidance on what to watch for after deworming, ensuring your pet’s continued health and comfort.\r\n\r\n\r\nBENEFITS OF DEWORMING:\r\nPrevents the harmful effects of parasites, such as malnutrition, anemia, and digestive issues.\r\n\r\n\r\nProtects both your pet and your family from zoonotic diseases that can be transmitted by parasites.\r\n\r\n\r\nPromotes a healthier, happier, and more energetic pet.\r\n\r\n\r\nKeep your pet healthy and parasite-free with regular deworming care at TAJO. Book an appointment today to ensure your pet’s continued well-being!\r\n', 'Deworming', 'post_images/1761016526_1761016515_1744100747_HowtoGiveaCataPill.png', '2025-10-08 10:00:56', '2025-10-21 11:15:26'),
(7, 'Laboratory', 'LABORATORY SERVICES AT TAJO: PRECISION DIAGNOSTICS FOR YOUR PET\'S WELL-BEING\r\nAt TAJO, the Online Veterinary System of Doclenon Veterinary Clinic, we know that accurate diagnostics are critical in ensuring your pet\'s health and recovery. Whether your pet is suffering from a chronic condition, recovering from surgery, or experiencing unexplained symptoms, timely and precise lab tests are essential for guiding treatment decisions.\r\nOur advanced laboratory services are not just about testing—they’re about providing clarity, insights, and peace of mind to both you and your pet. With our expert veterinary team and state-of-the-art equipment, we ensure that every diagnosis is thorough and every treatment plan is tailored to your pet’s unique needs.\r\n\r\nWHEN LABORATORY SERVICES ARE NECESSARY\r\nOur veterinarians may recommend lab testing in the following scenarios:\r\nPre-Surgical Testing – To assess your pet’s overall health before surgery and minimize risks.\r\n\r\n\r\nPost-Surgical Monitoring – To ensure there are no complications during recovery, including infections or organ failure.\r\n\r\n\r\nInfection Diagnosis – To identify bacterial, viral, or fungal infections and determine the best course of treatment.\r\n\r\n\r\nChronic Disease Management – To monitor ongoing conditions like diabetes, kidney failure, or heart disease and adjust treatment accordingly.\r\n\r\n\r\nEmergency Care – For immediate diagnosis of trauma, poisoning, or other acute conditions that require urgent intervention.\r\n\r\n\r\n\r\nWHAT TO EXPECT FROM OUR VETERINARY LAB\r\n🐾 Advanced Diagnostic Equipment – We use the latest technology to ensure that all tests are accurate and reliable, including blood analysis, X-rays, ultrasounds, and microbiology tests.\r\n🐾 Rapid Results – We know that every moment matters, especially in emergencies. That’s why we provide quick turnaround times for test results, often within hours, to help us make immediate and informed decisions about your pet’s care.\r\n🐾 Comprehensive Testing – Our laboratory performs a wide range of tests, from blood work and urinalysis to cultures and imaging, covering all aspects of your pet’s health and ensuring nothing is overlooked.\r\n🐾 Expert Interpretation and Tailored Treatment Plans – Once test results are available, our veterinary team works directly with lab staff to analyze the data and develop a personalized treatment plan for your pet. Whether it’s medication, dietary changes, or surgery, every treatment plan is designed specifically for your pet’s needs.\r\n\r\nSUPPORTING YOUR PET THROUGH SCIENCE AND CARE\r\nAt TAJO, we understand that the road to recovery can be daunting, but we’re here to guide you every step of the way. Our laboratory services provide the insights needed to make informed, timely decisions about your pet’s health, ensuring that they receive the right care at the right time.\r\n📌 Book a Lab Consultation or Testing – You can easily schedule laboratory services online, making it convenient to get the diagnostic care your pet needs. With our skilled team and advanced technology, we’re here to provide you with the answers you need and the care your pet deserves.\r\n', 'Laboratory', 'post_images/1761016515_1744100747_HowtoGiveaCataPill.png', '2025-10-08 10:02:21', '2025-10-21 11:15:15'),
(8, 'Surgery', 'CAT & DOG SURGERY \r\nYour pet deserves to live a happy, healthy, and comfortable life. At Doclenon Veterinary Clinic, we understand how challenging it can be when a health condition requires surgery. Whether it\'s a routine procedure or a more urgent intervention, our trusted veterinary team is here to guide you every step of the way.\r\nSurgery can help restore your pet’s health, reduce pain or discomfort, and improve their quality of life. Our goal is always to provide compassionate care and ensure your pet’s safety, comfort, and recovery.\r\n\r\nWHEN SURGERY IS NECESSARY\r\nWhen surgery becomes the best option, it’s natural to feel anxious. That’s why our veterinarians at Doclenon Veterinary Clinic take the time to explain every part of the process—so you’re informed and confident in your decision.\r\nWe prioritize:\r\nAnswering your questions clearly\r\n\r\n\r\nMinimizing your pet’s stress and discomfort\r\n\r\n\r\nProviding expert post-surgical care and recovery plans\r\n\r\n\r\nYour pet’s health is our priority—and that includes your peace of mind.\r\n\r\nCommon Surgical Procedures We Perform:\r\n🩺 Soft Tissue Surgeries\r\nSpay (ovariohysterectomy)\r\n\r\n\r\nNeuter (castration)\r\n\r\n\r\nUmbilical hernia repair\r\n\r\n\r\nLump and bump removals (tumors, cysts, growths)\r\n\r\n\r\nSkin and soft tissue biopsies\r\n\r\n\r\nBladder stone removal (cystotomy)\r\n\r\n\r\nGastrointestinal foreign body removal (gastrotomy, enterotomy)\r\n\r\n\r\n“Cherry eye” repair (prolapsed third eyelid gland)\r\n\r\n\r\nCryptorchid (retained testicle) surgery\r\n\r\n\r\nCaesarian (C-section) – for safe delivery during complicated pregnancies\r\n\r\n\r\nWe perform these procedures in a sterile, well-equipped surgical suite, supported by a skilled team with experience in both common and complex surgeries.\r\n\r\n📌 Need surgical advice or want to schedule a procedure?\r\n Let Doclenon Veterinary Clinic help you get started online today. We\'re committed to making surgery safer, smoother, and more accessible—for you and your pet.\r\n', 'Surgery', 'post_images/1761016503_1744101880_487826678_1114566964018154_8233192612581829936_n.jpg', '2025-10-08 10:04:58', '2025-10-21 11:15:03'),
(10, 'Confinement', ' PET CONFINEMENT CARE: A SUPPORTIVE SPACE FOR HEALING\r\nAt TAJO, we understand that some conditions or recovery processes require more than just home care. Our pet confinement services offer a dedicated, secure environment to help your pet heal with the right care and supervision.\r\nWe provide a safe space for pets who need continuous monitoring and professional support, from post-surgical recovery to serious health conditions. With 24/7 care and customized treatment plans, we ensure your pet receives the best possible environment for their recovery.\r\nWHAT WE OFFER:\r\nSafe and Comfortable Spaces – Clean, quiet enclosures designed for your pet\'s peace of mind and rest.\r\n\r\n\r\nConstant Care – Our experienced veterinary team monitors your pet’s condition around the clock, with updates to keep you informed.\r\n\r\n\r\nPersonalized Treatment – From medication to wound care, we offer tailored solutions that cater to your pet’s needs.\r\n\r\n\r\nCompassionate Handling – We understand that healing involves emotional comfort, so we handle each pet with gentle care to minimize stress.\r\n\r\n\r\nWith TAJO, you can trust that your pet will have the right environment and care during their recovery process. Let us support both you and your pet through their healing journey.\r\n\r\n\r\n', 'Confinement', 'post_images/1761016493_1744100360_476887346_1072394721568712_5315353126333565533_n.jpg', '2025-10-08 10:06:43', '2025-10-21 11:14:53'),
(11, 'Grooming', 'DOG AND CAT GROOMING: CARE FOR BOTH PETS WITH LOVE AND EXPERTISE\r\nAt TAJO, we offer professional grooming services for both dogs and cats, understanding that each pet has unique grooming needs. Our expert groomers ensure that your pets not only look their best but also feel great with a thorough grooming experience that promotes health and comfort.\r\nWhether it’s a dog in need of a refreshing bath or a cat requiring gentle coat maintenance, we provide a soothing, stress-free grooming experience tailored to your pet’s specific needs.\r\nOUR DOG AND CAT GROOMING SERVICES INCLUDE:\r\nBath and Coat Care – We use premium pet-friendly shampoos and conditioners, ensuring your pet’s coat remains clean, soft, and healthy.\r\n\r\n\r\nNail Trimming and Paw Care – Regular nail care to keep your pet comfortable and prevent overgrowth, along with paw pad grooming.\r\n\r\n\r\nEar Cleaning and Teeth Brushing – Ear maintenance to prevent infections, and gentle teeth brushing to promote oral hygiene.\r\n\r\n\r\nHaircuts and Styling – Breed-specific trims for dogs or stylish cuts for cats, keeping your pets looking neat and tidy.\r\n\r\n\r\nFlea and Tick Prevention – If needed, we provide flea and tick treatments as part of the grooming session to protect your pet from external parasites.\r\n\r\n\r\nBENEFITS OF PROFESSIONAL GROOMING:\r\nPromotes better skin and coat health, preventing tangles, mats, and skin irritation.\r\n\r\n\r\nRegular grooming helps identify potential health issues like infections or parasites early.\r\n\r\n\r\nKeeps your pet feeling comfortable and refreshed with proper hygiene care.\r\n\r\n\r\nCreates a positive grooming experience that builds trust and comfort with the grooming process.\r\n\r\n\r\nNo matter if it’s your dog or cat, TAJO provides top-tier grooming care to make sure they feel their absolute best. Book an appointment today and let your pet enjoy a day of pampering!\r\n', 'Grooming', 'post_images/1761016484_1744100804_198303911_3026976437536668_8914404879820849759_n.jpg', '2025-10-08 10:07:48', '2025-10-21 11:14:44'),
(12, 'Pet Boarding', 'Pet Boarding \r\nLeaving your pet behind while you\'re away can be stressful—but it doesn\'t have to be. At TAJO, we help ensure your pet’s boarding experience is comfortable, safe, and tailored to their needs by providing everything you need to prepare before drop-off.\r\n\r\n🐾 What to Prepare Before Pet Boarding\r\n✅ Bring your pet’s regular food\r\n To avoid stomach upset or stress, stick with their normal diet. Sudden food changes can lead to digestive issues.\r\n✅ Provide updated vaccination records\r\n All pets must be up to date on required vaccinations for their safety and that of other animals.\r\n✅ Include any medications or supplements\r\n Label all medications clearly with dosage instructions and schedule.\r\n✅ Add familiar items (optional)\r\n A favorite toy, blanket, or bed can help reduce anxiety and provide comfort in a new environment.\r\n\r\n📋 Vaccination Requirements for Boarding\r\nTo ensure a safe and healthy environment for all pets, we require the following vaccines before boarding:\r\nFor Dogs:\r\nRabies\r\n\r\n\r\nDistemper\r\n\r\n\r\nParvovirus\r\n\r\n\r\nBordetella (kennel cough)\r\n\r\n\r\nFor Cats:\r\nRabies\r\n\r\n\r\nFeline Distemper (FVRCP)\r\n\r\n\r\nFeline Leukemia (for outdoor or multi-cat environments)\r\n\r\n\r\n\r\n💬 Before You Board\r\n✔ Schedule a pre-boarding consultation if your pet has special medical needs or anxiety\r\n ✔ Let us know about any allergies, habits, or behavioral quirks\r\n ✔ Make your booking in advance to secure your spot—especially during peak seasons\r\n\r\nYour pet deserves the best care even when you\'re away. With TAJO: An Online Veterinary System for Doclenon Veterinary Clinic, you can book and prepare for boarding stress-free and with confidence.\r\n📲 Book your pet\'s boarding appointment today through TAJO!\r\n\r\n', 'Pet Boarding', 'post_images/1761016465_1744100979_OIP (4).jpg', '2025-10-08 10:09:19', '2025-10-21 11:14:25');

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
(13, 'Pet Stainless feed bowl', 'Pet Accessories', 120, 'product_images/1759607906_1745306006_16.png', 71),
(14, 'Catnip Garden ', 'Cat Food', 200, 'product_images/1759608162_1745307675_18 (5).png', 66),
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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notificationid`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notificationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
