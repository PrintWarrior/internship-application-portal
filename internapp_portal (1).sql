-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 04:36 PM
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
-- Database: `internapp_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_type` enum('intern','company') NOT NULL,
  `address_type` enum('primary','mailing','billing') NOT NULL DEFAULT 'primary',
  `address_line` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) NOT NULL DEFAULT 'Philippines',
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `entity_id`, `entity_type`, `address_type`, `address_line`, `city`, `province`, `postal_code`, `country`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 29, 'intern', 'primary', 'Port 80 Ave Street', 'Tangub City', 'Region X/Misamis Occidental', '7214', 'Philippines', 1, '2026-04-10 16:41:53', '2026-04-10 16:41:53'),
(2, 16, 'company', 'primary', 'Gravebattery residence street 14', 'Tangub City', 'Region X/Misamis Occidental', '7214', 'Philippines', 1, '2026-04-10 16:47:16', '2026-04-10 16:50:37');

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_fname` varchar(100) NOT NULL,
  `admin_lname` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`admin_id`, `user_id`, `admin_fname`, `admin_lname`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 2, 'System', 'Administrator', 'admin_69dba6253389f4.96125812.png', '2026-04-12 14:02:51', '2026-04-12 14:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `date_applied` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Reviewed','Shortlisted','Rejected','Offered','Accepted','Declined','Contract Signed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `internship_id`, `intern_id`, `date_applied`, `status`) VALUES
(14, 21, 29, '2026-04-12 14:09:00', 'Accepted');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `contact_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `contact_phone`, `industry`, `contact_person`, `website`, `description`, `profile_image`, `contact_email`, `created_at`, `updated_at`) VALUES
(16, 'None', '09263636477', 'Software Development & Cloud Solutions', 'Xavier Ace Clark Jucel Azcona', 'https://www.nexasofttech.com', 'lol', 'default.png', NULL, '2026-04-10 16:47:16', '2026-04-10 16:47:16');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `contract_pdf` varchar(255) NOT NULL,
  `contract_file` varchar(255) NOT NULL,
  `signed_file` varchar(255) DEFAULT NULL,
  `signed_date` datetime DEFAULT NULL,
  `hr_confirmed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`contract_id`, `application_id`, `contract_pdf`, `contract_file`, `signed_file`, `signed_date`, `hr_confirmed`, `created_at`) VALUES
(16, 14, 'contract_14_1776003120.pdf', 'contract_14_1776003120.pdf', 'signed_contract_16_1776004185.pdf', '2026-04-12 22:29:45', 1, '2026-04-12 14:12:00');

-- --------------------------------------------------------

--
-- Table structure for table `interns`
--

CREATE TABLE `interns` (
  `intern_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `course` varchar(150) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(100) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interns`
--

INSERT INTO `interns` (`intern_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birthdate`, `age`, `contact_no`, `university`, `course`, `year_level`, `created_at`, `profile_image`) VALUES
(29, 59, 'Xavier Ace Clark Jucel', '', 'Azcona', NULL, '', '2012-02-07', 14, '09067470860', ' Northwestern Mindanao State College of Science and Technology', 'Bachelor of Science in Information Technology', '3rd Year', '2026-04-10 16:41:53', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `internship_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `allowance` decimal(10,2) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('pending','approved','rejected','closed') DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`internship_id`, `company_id`, `title`, `description`, `requirements`, `duration`, `allowance`, `deadline`, `status`, `start_date`, `end_date`, `created_at`) VALUES
(21, 16, 'Test', 'Test', 'Test', '4 Test', 500.00, '2026-04-15', 'approved', NULL, NULL, '2026-04-12 14:07:17');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `action_label` varchar(100) DEFAULT NULL,
  `related_user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `action_url`, `action_label`, `related_user_id`, `is_read`, `created_at`, `link`) VALUES
(1, 2, 'New intern registered: xavierazcona0422@gmail.com', 'send_verification.php?user_id=59', 'Send Verification Link', 59, 1, '2026-04-10 16:41:53', ''),
(2, 2, 'User xavierazcona0422@gmail.com has verified their email.', 'send_otp.php?user_id=59', 'Send Temopory Password', 59, 1, '2026-04-10 16:44:41', ''),
(3, 2, 'New staff registered: gamingxac@gmail.com', 'send_verification.php?user_id=60', 'Send Verification Link', 60, 1, '2026-04-10 16:47:16', ''),
(4, 2, 'User gamingxac@gmail.com has verified their email.', 'send_otp.php?user_id=60', 'Send Temopory Password', 60, 1, '2026-04-10 16:49:19', ''),
(5, 2, 'Company \'None\' has posted a new internship: Test', NULL, NULL, NULL, 1, '2026-04-12 14:07:17', '../admin/view_internship.php?id=21'),
(6, 60, 'Your internship has been approved by the admin.', NULL, NULL, NULL, 1, '2026-04-12 14:08:12', ''),
(7, 60, 'A new intern applied for your internship: Test', 'applications.php', 'View Application', NULL, 1, '2026-04-12 14:09:00', ''),
(8, 59, 'Your application for \'Test\' has been offered', NULL, NULL, NULL, 1, '2026-04-12 14:09:36', ''),
(9, 60, 'Xavier Ace Clark Jucel Azcona accepted your internship offer for Test.', 'applications.php', 'View Applications', NULL, 1, '2026-04-12 14:10:46', ''),
(10, 59, 'A new contract for \'Test\' is ready for your signature.', NULL, NULL, NULL, 1, '2026-04-12 14:12:00', 'intern/contracts.php'),
(11, 60, 'An intern has signed a contract for \'Test\'. Please review and confirm.', NULL, NULL, NULL, 0, '2026-04-12 14:29:45', 'company/contracts.php'),
(12, 59, 'Your contract has been confirmed by None', NULL, NULL, NULL, 0, '2026-04-12 14:30:23', '');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`staff_id`, `user_id`, `company_id`, `first_name`, `last_name`, `email`, `contact_no`, `position`, `profile_image`, `created_at`) VALUES
(9, 60, 16, 'Xavier', 'Ace Clark Jucel Azcona', 'chicj@gmail.com', '09263636477', 'Contact Person', 'default.png', '2026-04-10 16:47:16');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `user_id`, `action`, `description`, `created_at`) VALUES
(1, 60, 'Create internship', 'Created new internship: Test (ID: 21)', '2026-04-12 14:07:17'),
(2, 2, 'Approve Internship', 'Approved internship \'Test\' (ID 21)', '2026-04-12 14:08:12'),
(3, 60, 'Update application status', 'Changed application #14 for Xavier Ace Clark Jucel Azcona from Pending to Offered', '2026-04-12 14:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `user_type` enum('admin','staff','intern','superadmin') NOT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `first_login` tinyint(1) DEFAULT 1,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `status` enum('active','banned','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `user_type`, `verified`, `created_at`, `updated_at`, `first_login`, `reset_token`, `token_expiry`, `status`) VALUES
(1, 'superadmin@gmail.com', '$2y$10$oIGKeUK8PO0.sxiKA/ZcKOp28LFZV8y20mCvdnQLH2NcDrJblKkCi', 'superadmin', 1, '2026-04-01 14:42:54', '2026-04-03 11:31:29', 0, NULL, NULL, 'active'),
(2, 'admin@gmail.com', '$2y$10$tR3NJ0CkGP903/yCN1SwBePZW/jSLK4Se4LCu6crziPPEdkp3/fXi', 'admin', 1, '2026-04-01 14:43:48', '2026-04-01 14:43:48', 0, NULL, NULL, 'active'),
(59, 'xavierazcona0422@gmail.com', '$2y$10$J8R8WcbV0/n6xOcFmbBPxu7ipGqmZThWAZljqXFdIbtn2/YK1ZSsq', 'intern', 1, '2026-04-10 16:41:53', '2026-04-10 16:46:12', 0, NULL, NULL, 'active'),
(60, 'gamingxac@gmail.com', '$2y$10$b1LYJLk2TGIUq8mmm74GaugQwFhcWvTXkeq5H.COCEn3yOanaWMx6', 'staff', 1, '2026-04-10 16:47:16', '2026-04-10 16:50:23', 0, NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `verification_tokens`
--

CREATE TABLE `verification_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `type` enum('email_verify','otp') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_tokens`
--

INSERT INTO `verification_tokens` (`token_id`, `user_id`, `token`, `expiry`, `used`, `type`) VALUES
(90, 59, '585be1b39f15cced1828d750497a2b271e3f980aecd11a5bf462ced003a80b8e', '2026-04-11 18:42:38', 0, 'email_verify'),
(91, 59, '94d484b6a1cc402a924b6bdb4ac2ffa84ab79cc2fce46511947cd1aa1afff4e9', '2026-04-11 18:44:26', 1, 'email_verify'),
(92, 59, '801771', '2026-04-11 01:45:06', 1, 'otp'),
(93, 60, '07a388f8024de1c02d7ffc38a10faaf985ef1e4907c3398e6c9e330f869d00ed', '2026-04-11 18:47:45', 1, 'email_verify'),
(94, 60, '427583', '2026-04-11 01:49:26', 1, 'otp');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_entity` (`entity_id`,`entity_type`),
  ADD KEY `idx_primary` (`entity_id`,`entity_type`,`is_primary`);

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `idx_intern` (`intern_id`),
  ADD KEY `idx_internship` (`internship_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_hr_confirmed` (`hr_confirmed`);

--
-- Indexes for table `interns`
--
ALTER TABLE `interns`
  ADD PRIMARY KEY (`intern_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `related_user_id` (`related_user_id`),
  ADD KEY `user_id` (`user_id`,`is_read`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_log` (`user_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`),
  ADD KEY `user_type` (`user_type`);

--
-- Indexes for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`,`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `interns`
--
ALTER TABLE `interns`
  MODIFY `intern_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD CONSTRAINT `admin_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`intern_id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `interns`
--
ALTER TABLE `interns`
  ADD CONSTRAINT `interns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `staffs`
--
ALTER TABLE `staffs`
  ADD CONSTRAINT `staffs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staffs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD CONSTRAINT `verification_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
