-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 03:41 PM
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
-- Database: `application_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`admin_id`, `user_id`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 2, 'admin_profile_2_1775054837.png', '2026-04-01 14:44:32', '2026-04-01 14:47:17');

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

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) NOT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `contact_phone`, `address`, `industry`, `contact_person`, `website`, `description`, `profile_image`, `city`, `province`, `postal_code`, `country`, `contact_email`, `created_at`, `updated_at`) VALUES
(10, 'Company', '0123456789', 'Company', 'Company', 'Person', 'https://@example.com', 'Company', 'default.png', NULL, NULL, NULL, '', NULL, '2026-04-02 05:47:42', '2026-04-02 05:47:42');

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
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `course` varchar(150) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(100) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interns`
--

INSERT INTO `interns` (`intern_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birthdate`, `age`, `contact_no`, `address`, `city`, `province`, `postal_code`, `university`, `course`, `year_level`, `created_at`, `profile_image`) VALUES
(23, 45, 'Intern', 'Intern', 'Intern', NULL, 'Male', '2022-01-01', 25, '0123456789', 'Intern', 'Intern', 'Intern', 'Intern', 'Intern', 'Intern', 'Intern', '2026-04-02 05:48:57', 'default.png');

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
(131, 1, 'New staff member registered: company@gmail.com', 'send_verification.php?user_id=44', 'Send Verification Link', 44, 0, '2026-04-02 05:47:42', ''),
(132, 1, 'New intern registered: intern@gmail.com', 'send_verification.php?user_id=45', 'Send Verification Link', 45, 0, '2026-04-02 05:48:57', '');

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
(3, 44, 10, 'Person', '', 'company@gmail.com', '0123456789', 'Contact Person', 'default.png', '2026-04-02 05:47:42');

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
(1, 'superadmin@gmail.com', '$2y$10$oIGKeUK8PO0.sxiKA/ZcKOp28LFZV8y20mCvdnQLH2NcDrJblKkCi', 'superadmin', 1, '2026-04-01 14:42:54', '2026-04-01 14:42:54', 0, NULL, NULL, 'active'),
(2, 'admin@gmail.com', '$2y$10$tR3NJ0CkGP903/yCN1SwBePZW/jSLK4Se4LCu6crziPPEdkp3/fXi', 'admin', 1, '2026-04-01 14:43:48', '2026-04-01 14:43:48', 0, NULL, NULL, 'active'),
(44, 'company@gmail.com', '$2y$10$nRtAZE9B1KBHycS58zv7auBXyT468RaIjXhAX7xDAC9kVO1x9xLai', 'staff', 1, '2026-04-02 05:47:42', '2026-04-02 05:49:57', 0, NULL, NULL, 'active'),
(45, 'intern@gmail.com', '$2y$10$0GqXyTmQRLQse1aOrlcElOG3U97jXUbIZsOMISxLNY2opg5kyC3mm', 'intern', 1, '2026-04-02 05:48:57', '2026-04-02 10:46:11', 0, NULL, NULL, 'active');

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
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `interns`
--
ALTER TABLE `interns`
  MODIFY `intern_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

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
