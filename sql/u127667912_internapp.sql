-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2026 at 12:25 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u127667912_internapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_type` enum('intern','company') NOT NULL,
  `intern_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
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
(3, 30, 'intern', 'primary', 'Street 1', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(4, 31, 'intern', 'primary', 'Street 2', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(5, 32, 'intern', 'primary', 'Street 3', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(6, 33, 'intern', 'primary', 'Street 4', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(7, 34, 'intern', 'primary', 'Street 5', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(8, 35, 'intern', 'primary', 'Street 6', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(9, 36, 'intern', 'primary', 'Street 7', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(10, 37, 'intern', 'primary', 'Street 8', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(11, 38, 'intern', 'primary', 'Street 9', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(12, 39, 'intern', 'primary', 'Street 10', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:00:47', '2026-04-17 14:00:47'),
(13, 50, 'intern', 'primary', 'Street 21', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(14, 51, 'intern', 'primary', 'Street 22', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(15, 52, 'intern', 'primary', 'Street 23', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(16, 53, 'intern', 'primary', 'Street 24', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(17, 54, 'intern', 'primary', 'Street 25', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(18, 55, 'intern', 'primary', 'Street 26', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(19, 56, 'intern', 'primary', 'Street 27', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(20, 57, 'intern', 'primary', 'Street 28', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(21, 58, 'intern', 'primary', 'Street 29', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(22, 59, 'intern', 'primary', 'Street 30', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(23, 60, 'intern', 'primary', 'Street 31', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(24, 61, 'intern', 'primary', 'Street 32', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(25, 62, 'intern', 'primary', 'Street 33', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(26, 63, 'intern', 'primary', 'Street 34', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(27, 64, 'intern', 'primary', 'Street 35', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(28, 65, 'intern', 'primary', 'Street 36', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(29, 66, 'intern', 'primary', 'Street 37', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(30, 67, 'intern', 'primary', 'Street 38', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(31, 68, 'intern', 'primary', 'Street 39', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(32, 69, 'intern', 'primary', 'Street 40', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(33, 70, 'intern', 'primary', 'Street 41', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(34, 71, 'intern', 'primary', 'Street 42', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(35, 72, 'intern', 'primary', 'Street 43', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(36, 73, 'intern', 'primary', 'Street 44', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(37, 74, 'intern', 'primary', 'Street 45', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(38, 75, 'intern', 'primary', 'Street 46', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(39, 76, 'intern', 'primary', 'Street 47', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(40, 77, 'intern', 'primary', 'Street 48', 'Lapu-Lapu City', 'Cebu', '6015', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(41, 78, 'intern', 'primary', 'Street 49', 'Talisay City', 'Cebu', '6045', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(42, 79, 'intern', 'primary', 'Street 50', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:04:37', '2026-04-17 14:04:37'),
(46, 17, 'company', 'primary', 'IT Park Tower 1', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(47, 18, 'company', 'primary', 'Ayala Business Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(48, 19, 'company', 'primary', 'Tech Hub Building', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(49, 20, 'company', 'primary', 'Innovation Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(50, 21, 'company', 'primary', 'Skyrise Building 2', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(51, 22, 'company', 'primary', 'IT Park Tower 2', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(52, 23, 'company', 'primary', 'Park Centrale', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(53, 24, 'company', 'primary', 'North Reclamation Area', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(54, 25, 'company', 'primary', 'Business Park Plaza', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(55, 26, 'company', 'primary', 'Cebu Exchange Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(56, 27, 'company', 'primary', 'IT Zone Building A', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(57, 28, 'company', 'primary', 'Startup Hub Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(58, 29, 'company', 'primary', 'Research Park', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(59, 30, 'company', 'primary', 'Software Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(60, 31, 'company', 'primary', 'Digital Park', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(61, 32, 'company', 'primary', 'Alpha Tech Building', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(62, 33, 'company', 'primary', 'CoreLink Center', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(63, 34, 'company', 'primary', 'Cloud Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(64, 35, 'company', 'primary', 'Data Center Cebu', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(65, 36, 'company', 'primary', 'Future Stack Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(66, 37, 'company', 'primary', 'Quantix Building', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(67, 38, 'company', 'primary', 'BlueCore Plaza', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(68, 39, 'company', 'primary', 'Orbit Tech Park', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(69, 40, 'company', 'primary', 'Peak Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(70, 41, 'company', 'primary', 'Innovix Hub', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(71, 42, 'company', 'primary', 'WebWorks Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(72, 43, 'company', 'primary', 'Forge Building', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(73, 44, 'company', 'primary', 'CloudMesh Plaza', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(74, 45, 'company', 'primary', 'BitLabs Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(75, 46, 'company', 'primary', 'DigiCore Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(76, 47, 'company', 'primary', 'TechFlow Hub', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(77, 48, 'company', 'primary', 'Codexia Building', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(78, 49, 'company', 'primary', 'Infonity Plaza', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(79, 50, 'company', 'primary', 'NetPrime Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(80, 51, 'company', 'primary', 'DevSpire Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(81, 52, 'company', 'primary', 'BrightLabs Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(82, 53, 'company', 'primary', 'LogicFlow Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(83, 54, 'company', 'primary', 'CloudAxis Plaza', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(84, 55, 'company', 'primary', 'CyberCore Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(85, 56, 'company', 'primary', 'HyperTech Hub', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(86, 57, 'company', 'primary', 'NovaSys Building', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(87, 58, 'company', 'primary', 'SmartWare Center', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(88, 59, 'company', 'primary', 'NetCore Tower', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(89, 60, 'company', 'primary', 'InfoWorks Hub', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(90, 61, 'company', 'primary', 'TechVerse Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(91, 62, 'company', 'primary', 'ByteCore Plaza', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(92, 63, 'company', 'primary', 'SoftNova Building', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(93, 64, 'company', 'primary', 'CloudByte Center', 'Mandaue City', 'Cebu', '6014', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(94, 65, 'company', 'primary', 'FutureCore Hub', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(95, 66, 'company', 'primary', 'DigiHub Tower', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-17 14:31:03', '2026-04-17 14:31:03'),
(96, 67, 'company', 'primary', 'Port 80 Ave Street', NULL, NULL, NULL, 'Philippines', 1, '2026-04-19 14:24:12', '2026-04-19 14:24:12'),
(97, 80, 'intern', 'primary', 'For', 'Testing', 'Only', '1234', 'Philippines', 1, '2026-04-20 19:17:36', '2026-04-20 19:17:36'),
(98, 81, 'intern', 'primary', 'For', 'Testing', 'Only', '1234', 'Philippines', 1, '2026-04-20 19:19:15', '2026-04-20 19:19:15'),
(99, 82, 'intern', 'primary', 'For', 'Testing', 'Only', '1234', 'Philippines', 1, '2026-04-20 19:35:17', '2026-04-20 19:35:17'),
(100, 68, 'company', 'primary', 'Port 80 Ave Street', NULL, NULL, NULL, 'Philippines', 1, '2026-04-20 19:50:20', '2026-04-20 19:50:20'),
(101, 83, 'intern', 'primary', 'Block 13', 'Oroquieta City', 'Misamis Occidental', '7214', 'Philippines', 1, '2026-04-30 14:10:54', '2026-04-30 14:10:54'),
(102, 69, 'company', 'primary', 'Archbishop Reyes Ave', 'Cebu City', 'Cebu', '6000', 'Philippines', 1, '2026-04-30 14:20:15', '2026-04-30 14:20:15');

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
(1, 2, 'System', 'Administrator', 'admin_f8f611d24a93b109.jpg', '2026-04-12 14:02:51', '2026-05-04 05:12:29'),
(2, 1, 'Xavier', 'Azcona', 'admin_3405c8e243001f62.png', '2026-04-19 12:32:52', '2026-04-24 06:41:54');

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
(17, 63, 30, '2026-04-19 14:51:40', 'Accepted'),
(18, 69, 30, '2026-04-20 06:45:43', 'Accepted');

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
(17, 'Nexora Technologies', NULL, 'AI & Software', 'Alex Rivera', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(18, 'Cloudspire Solutions', NULL, 'Cloud Computing', 'Jasmine Lee', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(19, 'ByteForge Labs', NULL, 'Software Development', 'Kevin Tan', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(20, 'ZenithWorks Inc.', NULL, 'IT Consulting', 'Michelle Sy', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(21, 'Infinitix Systems', NULL, 'Enterprise Solutions', 'Daniel Ong', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(22, 'PixelCraft Studio', NULL, 'Web Development', 'Karen Lim', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(23, 'CodeWave Technologies', NULL, 'Software Engineering', 'Ryan Chua', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(24, 'SkyGrid Networks', NULL, 'Networking', 'Sophia Nguyen', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(25, 'Vertex Labs', NULL, 'Data Science', 'Ethan Kim', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(26, 'BrightPath Solutions', NULL, 'IT Services', 'Louis Garcia', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(27, 'Logicore Systems', NULL, 'Software Engineering', 'Chris Morales', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(28, 'NextGen Hub', NULL, 'Startup Incubator', 'Anna Reyes', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(29, 'Techtonic Innovations', NULL, 'R&D', 'Paul Dizon', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(30, 'SoftSpark Solutions', NULL, 'Software Dev', 'Grace Velasco', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(31, 'Digisphere Corp', NULL, 'Digital Solutions', 'Mark Bautista', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(32, 'AlphaTech Systems', NULL, 'IT Services', 'Liza Fuentes', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(33, 'CoreLink Solutions', NULL, 'Networking', 'Ian Santos', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(34, 'CloudNova Tech', NULL, 'Cloud Computing', 'Ella Perez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(35, 'DataSurge Analytics', NULL, 'Data Analytics', 'Ronnie Castro', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(36, 'FutureStack Dev', NULL, 'Software Dev', 'Mia Ramos', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(37, 'Quantix Systems', NULL, 'AI Solutions', 'Noah Lopez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(38, 'BlueCore IT', NULL, 'IT Consulting', 'Ivy Torres', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(39, 'OrbitWare Tech', NULL, 'Software Dev', 'Joshua Gomez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(40, 'PeakLogic Solutions', NULL, 'Software Dev', 'Nicole Mendoza', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(41, 'Innovix Labs', NULL, 'Innovation Tech', 'Adrian Aquino', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(42, 'WebWorks Studio', NULL, 'Web Dev', 'Trisha Valdez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(43, 'SoftForge Systems', NULL, 'Software Dev', 'Carl Domingo', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(44, 'CloudMesh Corp', NULL, 'Cloud Infra', 'Bianca Castillo', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(45, 'BitLabs Tech', NULL, 'Software Dev', 'Vince Ortega', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(46, 'DigiCore Systems', NULL, 'IT Services', 'Diana Pascual', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(47, 'TechFlow Solutions', NULL, 'Software Dev', 'Leo Ferrer', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(48, 'Codexia Labs', NULL, 'Software Dev', 'Sarah De Guzman', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(49, 'Infonity Systems', NULL, 'IT Services', 'Miguel Navarro', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(50, 'NetPrime Solutions', NULL, 'Networking', 'Kylie Salazar', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(51, 'DevSpire Technologies', NULL, 'Software Dev', 'Omar Dela Cruz', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(52, 'BrightLabs Inc.', NULL, 'R&D', 'Angelica Martinez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(53, 'LogicFlow Systems', NULL, 'Software Dev', 'Jay Rivero', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(54, 'CloudAxis Tech', NULL, 'Cloud Services', 'Shane Fernandez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(55, 'CyberCore Solutions', NULL, 'Cybersecurity', 'Nina Villanueva', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(56, 'HyperTech Systems', NULL, 'IT Services', 'Enzo Cruz', NULL, NULL, 'company_56_52f682586df78ead.jpg', NULL, '2026-04-17 14:24:45', '2026-04-24 06:50:19'),
(57, 'NovaSys Tech', NULL, 'Software Dev', 'Rafael Dela Cruz', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(58, 'SmartWare Solutions', NULL, 'IT Services', 'Patricia Garcia', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(59, 'NetCore Systems', NULL, 'Networking', 'Keith Moreno', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(60, 'InfoWorks Tech', NULL, 'Software Dev', 'Alyssa Chavez', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(61, 'TechVerse Innovations', NULL, 'Software Dev', 'Brandon Pineda', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(62, 'ByteCore Labs', NULL, 'Software Dev', 'Camille Soriano', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(63, 'SoftNova Systems', NULL, 'Software Dev', 'Jerome Balagtas', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(64, 'CloudByte Tech', NULL, 'Cloud Computing', 'Danica Roxas', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(65, 'FutureCore Systems', NULL, 'IT Services', 'Marvin Tan', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(66, 'DigiHub Solutions', NULL, 'Digital Solutions', 'Celine Yap', NULL, NULL, 'default.png', NULL, '2026-04-17 14:24:45', '2026-04-17 14:24:45'),
(67, 'Test', '09067470860', 'Test', 'Xavier Azcona', 'https://@example.com', '', 'default.png', NULL, '2026-04-19 14:24:12', '2026-04-19 14:24:12'),
(68, 'For ', '09067470860', 'Only', 'Test', 'https://@example.com', 'Hhehe', 'default.png', NULL, '2026-04-20 19:50:20', '2026-04-20 19:50:20'),
(69, 'Rei Corp', '12345678901', 'Machine', 'Rei Vax', 'https://example.com', 'Hello', 'default.png', 'reivax0525@gmail.com', '2026-04-30 14:17:25', '2026-04-30 14:17:25');

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
(19, 17, 'contract_17_1776610821.pdf', 'contract_17_1776610821.pdf', 'signed_contract_19_1776611242.pdf', '2026-04-19 23:07:22', 1, '2026-04-19 15:00:21'),
(20, 18, 'contract_18_1776667767.pdf', 'contract_18_1776667767.pdf', NULL, NULL, 0, '2026-04-20 06:49:27');

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
(30, 61, 'Juan', 'Santos', 'Dela Cruz', NULL, 'Male', '2002-05-12', 23, '09170000001', 'University of Cebu', 'BSIT', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(31, 62, 'Maria', 'Lopez', 'Garcia', NULL, 'Female', '2003-02-20', 22, '09170000002', 'Cebu Technological University', 'BSCS', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(33, 64, 'Angela', 'Cruz', 'Flores', NULL, 'Female', '2002-07-15', 23, '09170000004', 'University of Cebu', 'BSIS', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(34, 65, 'Mark', 'Villanueva', 'Santos', NULL, 'Male', '2003-09-10', 22, '09170000005', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(35, 66, 'Claire', 'Ramos', 'Mendoza', NULL, 'Female', '2002-01-25', 24, '09170000006', 'University of San Jose-Recoletos', 'BSCS', '4th Year', '2026-04-17 14:00:36', 'default.png'),
(36, 67, 'Kevin', 'Diaz', 'Navarro', NULL, 'Male', '2003-03-18', 22, '09170000007', 'University of Cebu', 'BSIT', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(37, 68, 'Jessa', 'Aquino', 'Perez', NULL, 'Female', '2001-06-08', 24, '09170000008', 'Cebu Technological University', 'BSIT', '4th Year', '2026-04-17 14:00:36', 'default.png'),
(38, 69, 'Paul', 'Castro', 'Gomez', NULL, 'Male', '2002-10-30', 23, '09170000009', 'University of San Carlos', 'BSCS', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(39, 70, 'Liza', 'Torres', 'Morales', NULL, 'Female', '2003-04-11', 22, '09170000010', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(40, 71, 'Rico', 'Fernandez', 'Lopez', NULL, 'Male', '2002-08-21', 23, '09170000011', 'University of Cebu', 'BSIT', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(41, 72, 'Anne', 'Gutierrez', 'Reyes', NULL, 'Female', '2003-01-05', 23, '09170000012', 'University of San Jose-Recoletos', 'BSIS', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(42, 73, 'Bryan', 'Hernandez', 'Cruz', NULL, 'Male', '2001-12-19', 24, '09170000013', 'Cebu Technological University', 'BSIT', '4th Year', '2026-04-17 14:00:36', 'default.png'),
(43, 74, 'Diane', 'Ramirez', 'Flores', NULL, 'Female', '2002-06-22', 23, '09170000014', 'University of Cebu', 'BSCS', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(44, 75, 'Leo', 'Santiago', 'Garcia', NULL, 'Male', '2003-09-14', 22, '09170000015', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(45, 76, 'Mika', 'Valdez', 'Torres', NULL, 'Female', '2002-11-02', 23, '09170000016', 'University of San Carlos', 'BSIS', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(46, 77, 'Noel', 'Morales', 'Reyes', NULL, 'Male', '2001-07-17', 24, '09170000017', 'University of Cebu', 'BSIT', '4th Year', '2026-04-17 14:00:36', 'default.png'),
(47, 78, 'Ella', 'Castillo', 'Santos', NULL, 'Female', '2003-05-29', 22, '09170000018', 'Cebu Technological University', 'BSCS', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(48, 79, 'Jason', 'Domingo', 'Flores', NULL, 'Male', '2002-03-13', 23, '09170000019', 'University of San Jose-Recoletos', 'BSIT', '3rd Year', '2026-04-17 14:00:36', 'default.png'),
(49, 80, 'Kim', 'Ortega', 'Navarro', NULL, 'Female', '2003-12-01', 22, '09170000020', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:00:36', 'default.png'),
(50, 81, 'Carlo', 'Pascual', 'Garcia', NULL, 'Male', '2002-04-07', 23, '09170000021', 'University of Cebu', 'BSCS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(51, 82, 'Faith', 'Ramos', 'Lopez', NULL, 'Female', '2003-06-16', 22, '09170000022', 'University of San Carlos', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(52, 83, 'Allan', 'Mendoza', 'Reyes', NULL, 'Male', '2001-09-23', 24, '09170000023', 'Cebu Technological University', 'BSIS', '4th Year', '2026-04-17 14:04:27', 'default.png'),
(53, 84, 'Joy', 'Fernandez', 'Cruz', NULL, 'Female', '2002-02-27', 23, '09170000024', 'University of Cebu', 'BSIT', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(54, 85, 'Peter', 'Villanueva', 'Flores', NULL, 'Male', '2003-07-19', 22, '09170000025', 'CIT-U', 'BSCS', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(55, 86, 'Rose', 'Aquino', 'Torres', NULL, 'Female', '2002-10-05', 23, '09170000026', 'University of San Jose-Recoletos', 'BSIT', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(56, 87, 'Dennis', 'Diaz', 'Garcia', NULL, 'Male', '2001-03-30', 25, '09170000027', 'University of Cebu', 'BSIT', '4th Year', '2026-04-17 14:04:27', 'default.png'),
(57, 88, 'Ivy', 'Castro', 'Reyes', NULL, 'Female', '2003-08-11', 22, '09170000028', 'Cebu Technological University', 'BSIS', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(58, 89, 'Joel', 'Gutierrez', 'Lopez', NULL, 'Male', '2002-05-01', 23, '09170000029', 'University of San Carlos', 'BSCS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(59, 90, 'Nina', 'Torres', 'Flores', NULL, 'Female', '2003-01-14', 23, '09170000030', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(60, 91, 'Arvin', 'Santos', 'Garcia', NULL, 'Male', '2002-06-09', 23, '09170000031', 'University of Cebu', 'BSIT', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(61, 92, 'Grace', 'Reyes', 'Lopez', NULL, 'Female', '2003-02-02', 23, '09170000032', 'University of San Jose-Recoletos', 'BSCS', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(62, 93, 'Victor', 'Cruz', 'Flores', NULL, 'Male', '2001-08-26', 24, '09170000033', 'Cebu Technological University', 'BSIT', '4th Year', '2026-04-17 14:04:27', 'default.png'),
(63, 94, 'Sheila', 'Navarro', 'Torres', NULL, 'Female', '2002-09-17', 23, '09170000034', 'University of Cebu', 'BSIS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(64, 95, 'Henry', 'Garcia', 'Reyes', NULL, 'Male', '2003-11-20', 22, '09170000035', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(65, 96, 'Paula', 'Lopez', 'Santos', NULL, 'Female', '2002-12-03', 23, '09170000036', 'University of San Carlos', 'BSCS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(66, 97, 'Ramon', 'Flores', 'Cruz', NULL, 'Male', '2001-04-15', 24, '09170000037', 'University of Cebu', 'BSIT', '4th Year', '2026-04-17 14:04:27', 'default.png'),
(67, 98, 'Tina', 'Mendoza', 'Garcia', NULL, 'Female', '2003-03-08', 22, '09170000038', 'Cebu Technological University', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(68, 99, 'Oscar', 'Perez', 'Lopez', NULL, 'Male', '2002-07-27', 23, '09170000039', 'University of San Jose-Recoletos', 'BSIS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(69, 100, 'Lara', 'Gomez', 'Reyes', NULL, 'Female', '2003-10-10', 22, '09170000040', 'CIT-U', 'BSCS', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(70, 101, 'Ethan', 'Castillo', 'Garcia', NULL, 'Male', '2002-01-18', 24, '09170000041', 'University of Cebu', 'BSIT', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(71, 102, 'Sophia', 'Ortega', 'Lopez', NULL, 'Female', '2003-04-25', 22, '09170000042', 'University of San Carlos', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(72, 103, 'Daniel', 'Ramos', 'Reyes', NULL, 'Male', '2001-06-12', 24, '09170000043', 'Cebu Technological University', 'BSCS', '4th Year', '2026-04-17 14:04:27', 'default.png'),
(73, 104, 'Alyssa', 'Domingo', 'Flores', NULL, 'Female', '2002-08-30', 23, '09170000044', 'University of Cebu', 'BSIS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(74, 105, 'Miguel', 'Santiago', 'Garcia', NULL, 'Male', '2003-09-06', 22, '09170000045', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(75, 106, 'Bianca', 'Villanueva', 'Lopez', NULL, 'Female', '2002-11-21', 23, '09170000046', 'University of San Jose-Recoletos', 'BSCS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(76, 107, 'Adrian', 'Diaz', 'Reyes', NULL, 'Male', '2001-02-14', 25, '09170000047', 'University of Cebu', 'BSIT', '4th Year', '2026-04-17 14:04:27', 'default.png'),
(77, 108, 'Trisha', 'Aquino', 'Cruz', NULL, 'Female', '2003-05-03', 22, '09170000048', 'Cebu Technological University', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(78, 109, 'Nathan', 'Fernandez', 'Flores', NULL, 'Male', '2002-03-28', 23, '09170000049', 'University of San Carlos', 'BSCS', '3rd Year', '2026-04-17 14:04:27', 'default.png'),
(79, 110, 'Chloe', 'Morales', 'Garcia', NULL, 'Female', '2003-07-12', 22, '09170000050', 'CIT-U', 'BSIT', '2nd Year', '2026-04-17 14:04:27', 'default.png'),
(83, 4, 'Xavier Ace Clark', 'Salazar', 'Azcona', NULL, 'Male', '2001-04-22', 25, '09123456789', 'NMSC', 'BSIT', '3rd', '2026-04-30 14:10:06', 'default.png');

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
(24, 17, 'Web Developer Intern', 'Build and maintain web applications using modern frameworks.', 'HTML, CSS, JS, PHP', '3 months', 5000.00, '2026-05-15', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(25, 18, 'Cloud Support Intern', 'Assist in cloud infrastructure monitoring and deployment.', 'Basic AWS/Azure knowledge', '4 months', 6000.00, '2026-05-20', 'pending', '2026-06-05', '2026-10-05', '2026-04-17 14:53:04'),
(26, 19, 'Backend Developer Intern', 'Develop REST APIs and database systems.', 'PHP, MySQL', '3 months', 5500.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(27, 20, 'IT Support Intern', 'Provide technical support and troubleshooting.', 'Basic networking', '2 months', 4000.00, '2026-05-10', 'pending', '2026-06-01', '2026-08-01', '2026-04-17 14:53:04'),
(28, 21, 'Software Engineer Intern', 'Work on internal enterprise systems.', 'OOP, Java/PHP', '4 months', 7000.00, '2026-05-25', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(29, 22, 'Frontend Developer Intern', 'Design responsive UI/UX interfaces.', 'HTML, CSS, JS', '3 months', 5000.00, '2026-05-12', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(30, 23, 'Full Stack Intern', 'Handle both frontend and backend tasks.', 'JS, PHP, MySQL', '4 months', 6500.00, '2026-05-22', 'pending', '2026-06-05', '2026-10-05', '2026-04-17 14:53:04'),
(31, 24, 'Network Intern', 'Assist in managing network infrastructure.', 'Networking basics', '3 months', 4500.00, '2026-05-15', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(32, 25, 'Data Analyst Intern', 'Analyze datasets and generate reports.', 'Excel, SQL', '3 months', 6000.00, '2026-05-20', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(33, 26, 'IT Assistant Intern', 'Support IT operations.', 'Basic IT skills', '2 months', 3500.00, '2026-05-08', 'pending', '2026-06-01', '2026-08-01', '2026-04-17 14:53:04'),
(34, 27, 'System Developer Intern', 'Develop system modules and features.', 'PHP, MySQL', '4 months', 6500.00, '2026-05-25', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(35, 28, 'Startup Tech Intern', 'Assist in startup development projects.', 'Flexible skills', '3 months', 5000.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(36, 29, 'R&D Intern', 'Work on experimental tech solutions.', 'Analytical thinking', '4 months', 7000.00, '2026-05-28', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(37, 30, 'Junior Developer Intern', 'Assist senior developers.', 'Basic coding', '3 months', 4500.00, '2026-05-15', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(38, 31, 'Digital Marketing Intern', 'Help manage digital campaigns.', 'Social media skills', '2 months', 4000.00, '2026-05-10', 'pending', '2026-06-01', '2026-08-01', '2026-04-17 14:53:04'),
(39, 32, 'IT Intern', 'General IT tasks.', 'Basic IT knowledge', '3 months', 4500.00, '2026-05-12', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(40, 33, 'Network Assistant Intern', 'Support networking team.', 'Networking basics', '3 months', 4500.00, '2026-05-14', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(41, 34, 'Cloud Intern', 'Assist cloud engineers.', 'Cloud basics', '4 months', 6500.00, '2026-05-22', 'pending', '2026-06-05', '2026-10-05', '2026-04-17 14:53:04'),
(42, 35, 'Data Science Intern', 'Work on analytics and ML.', 'Python, SQL', '4 months', 7000.00, '2026-05-25', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(43, 36, 'Software Intern', 'Develop applications.', 'Programming basics', '3 months', 5000.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(44, 37, 'AI Intern', 'Assist AI model development.', 'Python', '4 months', 7500.00, '2026-05-30', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(45, 38, 'IT Consultant Intern', 'Support consulting tasks.', 'Communication skills', '3 months', 5000.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(46, 39, 'Software Dev Intern', 'Develop applications.', 'Java/PHP', '3 months', 5500.00, '2026-05-20', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(47, 40, 'Logic Analyst Intern', 'Analyze business logic systems.', 'Analytical skills', '3 months', 5000.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(48, 41, 'Innovation Intern', 'Work on new ideas.', 'Creative thinking', '4 months', 6500.00, '2026-05-25', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(49, 42, 'Web Dev Intern', 'Build websites.', 'HTML/CSS', '3 months', 5000.00, '2026-05-12', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(50, 43, 'Backend Intern', 'Develop APIs.', 'PHP/MySQL', '3 months', 5500.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(51, 44, 'Cloud Infra Intern', 'Work with cloud systems.', 'Cloud basics', '4 months', 6500.00, '2026-05-22', 'pending', '2026-06-05', '2026-10-05', '2026-04-17 14:53:04'),
(52, 45, 'Software Tester Intern', 'Test applications.', 'Attention to detail', '2 months', 4000.00, '2026-05-10', 'pending', '2026-06-01', '2026-08-01', '2026-04-17 14:53:04'),
(53, 46, 'IT Intern', 'General IT support.', 'Basic IT skills', '3 months', 4500.00, '2026-05-12', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(54, 47, 'DevOps Intern', 'Assist CI/CD pipelines.', 'Linux basics', '4 months', 7000.00, '2026-05-28', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(55, 48, 'Software Intern', 'Coding tasks.', 'Programming basics', '3 months', 5000.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(56, 49, 'System Intern', 'Maintain systems.', 'Basic IT', '3 months', 4500.00, '2026-05-15', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(57, 50, 'Network Intern', 'Assist network team.', 'Networking', '3 months', 4500.00, '2026-05-15', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(58, 51, 'Developer Intern', 'Software dev tasks.', 'Coding', '3 months', 5500.00, '2026-05-20', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(59, 52, 'R&D Intern', 'Research tech.', 'Analytical', '4 months', 6500.00, '2026-05-25', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(60, 53, 'Backend Intern', 'Server-side coding.', 'PHP', '3 months', 5500.00, '2026-05-18', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(61, 54, 'Cloud Intern', 'Cloud systems.', 'Cloud basics', '4 months', 6500.00, '2026-05-22', 'pending', '2026-06-05', '2026-10-05', '2026-04-17 14:53:04'),
(62, 55, 'Cybersecurity Intern', 'Security tasks.', 'Security basics', '4 months', 7000.00, '2026-05-25', 'pending', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04'),
(63, 56, 'IT Intern', 'General tasks.', 'Basic IT', '3 months', 4500.00, '2026-05-12', 'approved', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(64, 57, 'Software Intern', 'Coding tasks.', 'Programming', '3 months', 5000.00, '2026-05-18', 'rejected', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(65, 58, 'IT Intern', 'Support tasks.', 'Basic IT', '3 months', 4500.00, '2026-05-15', 'pending', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(66, 59, 'Network Intern', 'Networking tasks.', 'Networking', '3 months', 4500.00, '2026-05-15', 'rejected', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(67, 60, 'Developer Intern', 'Dev tasks.', 'Coding', '3 months', 5500.00, '2026-05-20', 'approved', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(68, 61, 'Software Intern', 'Build apps.', 'Programming', '3 months', 5000.00, '2026-05-18', 'rejected', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(69, 62, 'Backend Intern', 'Server dev.', 'PHP', '3 months', 5500.00, '2026-05-18', 'approved', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(70, 63, 'Frontend Intern', 'UI dev.', 'HTML/CSS', '3 months', 5000.00, '2026-05-12', 'rejected', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(71, 64, 'Cloud Intern', 'Cloud tasks.', 'Cloud basics', '4 months', 6500.00, '2026-05-22', 'pending', '2026-06-05', '2026-10-05', '2026-04-17 14:53:04'),
(72, 65, 'System Intern', 'System maintenance.', 'IT basics', '3 months', 4500.00, '2026-05-15', 'rejected', '2026-06-01', '2026-09-01', '2026-04-17 14:53:04'),
(73, 66, 'Full Stack Intern', 'Frontend + Backend.', 'JS, PHP', '4 months', 7000.00, '2026-05-25', 'approved', '2026-06-10', '2026-10-10', '2026-04-17 14:53:04');

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
(10, 156, 'Your internship has been approved by the admin.', NULL, NULL, NULL, 0, '2026-04-17 14:57:03', ''),
(11, 155, 'Your internship \'Software Intern\' was rejected by the admin.', 'internships.php', 'View Details', NULL, 0, '2026-04-17 14:57:08', ''),
(12, 153, 'Your internship \'Network Intern\' was rejected by the admin.', 'internships.php', 'View Details', NULL, 0, '2026-04-17 14:57:13', ''),
(13, 154, 'Your internship has been approved by the admin.', NULL, NULL, NULL, 0, '2026-04-17 14:57:19', ''),
(14, 151, 'Your internship \'Software Intern\' was rejected by the admin.', 'internships.php', 'View Details', NULL, 0, '2026-04-17 14:57:33', ''),
(15, 150, 'Your internship has been approved by the admin.', NULL, NULL, NULL, 1, '2026-04-17 14:57:45', ''),
(19, 150, 'A new intern applied for your internship: IT Intern', 'applications.php', 'View Application', NULL, 1, '2026-04-19 14:51:40', ''),
(20, 61, 'Your application for \'IT Intern\' has been offered', NULL, NULL, NULL, 1, '2026-04-19 14:55:54', ''),
(21, 150, 'Juan Dela Cruz accepted your internship offer for IT Intern.', 'applications.php', 'View Applications', NULL, 0, '2026-04-19 14:59:16', ''),
(22, 61, 'A new contract for \'IT Intern\' is ready for your signature.', NULL, NULL, NULL, 1, '2026-04-19 15:00:21', 'intern/contracts.php'),
(23, 150, 'An intern has signed a contract for \'IT Intern\'. Please review and confirm.', NULL, NULL, NULL, 0, '2026-04-19 15:07:22', 'company/contracts.php'),
(24, 61, 'Your contract has been confirmed by HyperTech Systems', NULL, NULL, NULL, 1, '2026-04-19 15:08:03', ''),
(25, 156, 'A new intern applied for your internship: Backend Intern', 'applications.php', 'View Application', NULL, 0, '2026-04-20 06:45:43', ''),
(26, 61, 'Your application for \'Backend Intern\' has been offered', NULL, NULL, NULL, 0, '2026-04-20 06:48:13', ''),
(27, 156, 'Juan Dela Cruz accepted your internship offer for Backend Intern.', 'applications.php', 'View Applications', NULL, 0, '2026-04-20 06:48:37', ''),
(28, 61, 'A new contract for \'Backend Intern\' is ready for your signature.', NULL, NULL, NULL, 0, '2026-04-20 06:49:27', 'intern/contracts.php');

-- --------------------------------------------------------

--
-- Table structure for table `report_logs`
--

CREATE TABLE `report_logs` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `export_format` varchar(10) DEFAULT NULL,
  `record_count` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_logs`
--

INSERT INTO `report_logs` (`report_id`, `user_id`, `company_id`, `report_type`, `filters`, `export_format`, `record_count`, `generated_at`) VALUES
(1, 150, 56, 'Application Report', '{\"status\":\"Accepted\",\"internship_id\":\"all\",\"start_date\":\"2026-05-08\",\"end_date\":\"2026-04-20\",\"export_type\":\"pdf\"}', 'PDF', 0, '2026-04-20 15:46:07'),
(2, 150, 56, 'Application Report', '{\"status\":\"Accepted\",\"internship_id\":\"all\",\"start_date\":\"2026-05-08\",\"end_date\":\"2026-04-20\",\"export_type\":\"csv\"}', 'CSV', 0, '2026-04-20 15:47:33'),
(3, 150, 56, 'Application Report', '{\"status\":\"all\",\"internship_id\":\"all\",\"start_date\":\"\",\"end_date\":\"\",\"export_type\":\"pdf\"}', 'PDF', 1, '2026-04-20 15:47:58'),
(4, 150, 56, 'Application Report', '{\"status\":\"all\",\"internship_id\":\"all\",\"start_date\":\"\",\"end_date\":\"\",\"export_type\":\"pdf\"}', 'PDF', 1, '2026-04-20 15:50:53'),
(5, 150, 56, 'Application Report', '{\"status\":\"all\",\"internship_id\":\"all\",\"start_date\":\"\",\"end_date\":\"\",\"export_type\":\"pdf\"}', 'PDF', 1, '2026-04-20 15:51:14'),
(6, 150, 56, 'Application Report', '{\"status\":\"all\",\"internship_id\":\"all\",\"start_date\":\"\",\"end_date\":\"\",\"export_type\":\"print\"}', 'PRINT', 1, '2026-04-20 16:03:31');

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
(10, 111, 17, 'Alex', 'Rivera', 'alex.rivera@nexora.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(11, 112, 18, 'Jasmine', 'Lee', 'jasmine.lee@cloudspire.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(12, 113, 19, 'Kevin', 'Tan', 'kevin.tan@byteforge.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(13, 114, 20, 'Michelle', 'Sy', 'michelle.sy@zenithworks.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(14, 115, 21, 'Daniel', 'Ong', 'daniel.ong@infinitix.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(15, 116, 22, 'Karen', 'Lim', 'karen.lim@pixelcraft.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(16, 117, 23, 'Ryan', 'Chua', 'ryan.chua@codewave.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(17, 118, 24, 'Sophia', 'Nguyen', 'sophia.nguyen@skygrid.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(18, 119, 25, 'Ethan', 'Kim', 'ethan.kim@vertexlabs.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(19, 120, 26, 'Louis', 'Garcia', 'louis.garcia@brightpath.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(20, 121, 27, 'Chris', 'Morales', 'chris.morales@logicore.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(21, 122, 28, 'Anna', 'Reyes', 'anna.reyes@nextgenhub.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(22, 123, 29, 'Paul', 'Dizon', 'paul.dizon@techtonic.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(23, 124, 30, 'Grace', 'Velasco', 'grace.velasco@softspark.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(24, 125, 31, 'Mark', 'Bautista', 'mark.bautista@digisphere.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(25, 126, 32, 'Liza', 'Fuentes', 'liza.fuentes@alphatech.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(26, 127, 33, 'Ian', 'Santos', 'ian.santos@corelink.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(27, 128, 34, 'Ella', 'Perez', 'ella.perez@cloudnova.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(28, 129, 35, 'Ronnie', 'Castro', 'ronnie.castro@datasurge.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(29, 130, 36, 'Mia', 'Ramos', 'mia.ramos@futurestack.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(30, 131, 37, 'Noah', 'Lopez', 'noah.lopez@quantix.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(31, 132, 38, 'Ivy', 'Torres', 'ivy.torres@bluecore.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(32, 133, 39, 'Joshua', 'Gomez', 'joshua.gomez@orbitware.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(33, 134, 40, 'Nicole', 'Mendoza', 'nicole.mendoza@peaklogic.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(34, 135, 41, 'Adrian', 'Aquino', 'adrian.aquino@innovix.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(35, 136, 42, 'Trisha', 'Valdez', 'trisha.valdez@webworks.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(36, 137, 43, 'Carl', 'Domingo', 'carl.domingo@softforge.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(37, 138, 44, 'Bianca', 'Castillo', 'bianca.castillo@cloudmesh.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(38, 139, 45, 'Vince', 'Ortega', 'vince.ortega@bitlabs.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(39, 140, 46, 'Diana', 'Pascual', 'diana.pascual@digicore.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(40, 141, 47, 'Leo', 'Ferrer', 'leo.ferrer@techflow.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(41, 142, 48, 'Sarah', 'De Guzman', 'sarah.deguzman@codexia.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(42, 143, 49, 'Miguel', 'Navarro', 'miguel.navarro@infonity.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(43, 144, 50, 'Kylie', 'Salazar', 'kylie.salazar@netprime.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(44, 145, 51, 'Omar', 'Dela Cruz', 'omar.delacruz@devspire.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(45, 146, 52, 'Angelica', 'Martinez', 'angelica.martinez@brightlabs.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(46, 147, 53, 'Jay', 'Rivero', 'jay.rivero@logicflow.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(47, 148, 54, 'Shane', 'Fernandez', 'shane.fernandez@cloudaxis.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(48, 149, 55, 'Nina', 'Villanueva', 'nina.villanueva@cybercore.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(49, 150, 56, 'Enzo', 'Cruz', 'enzo.cruz@hypertech.com', NULL, 'HR Manager', 'staff_2d19a326216063aa.jpg', '2026-04-17 14:29:26'),
(50, 151, 57, 'Rafael', 'Dela Cruz', 'rafael.dela.cruz@novasys.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(51, 152, 58, 'Patricia', 'Garcia', 'patricia.garcia@smartware.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(52, 153, 59, 'Keith', 'Moreno', 'keith.moreno@netcore.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(53, 154, 60, 'Alyssa', 'Chavez', 'alyssa.chavez@infoworks.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(54, 155, 61, 'Brandon', 'Pineda', 'brandon.pineda@techverse.com', NULL, 'HR Manager', 'default.png', '2026-04-17 14:29:26'),
(55, 156, 62, 'Camille', 'Soriano', 'camille.soriano@bytecore.com', NULL, 'HR Manager', 'staff_60d63572b689eebd.jpg', '2026-04-17 14:29:26'),
(63, 3, 69, 'Rei', 'Vax', 'reivax0525@gmail.com', '09123456789', 'Contact Person', 'default.png', '2026-04-30 14:18:41');

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
(1, NULL, 'Create internship', 'Created new internship: qwdqwdqwdqw (ID: 23)', '2026-04-17 02:05:29'),
(2, 2, 'Approve Internship', 'Approved internship \'qwdqwdqwdqw\' (ID 23)', '2026-04-17 02:06:03'),
(3, NULL, 'Update application status', 'Changed application #16 for Xavier Ace Clark Jucel Azcona from Pending to Offered', '2026-04-17 02:07:19'),
(4, 2, 'Delete Internship', 'Deleted internship ID 23', '2026-04-17 14:21:33'),
(5, 2, 'Delete Internship', 'Deleted internship ID 22', '2026-04-17 14:21:35'),
(6, 2, 'Approve Internship', 'Approved internship \'Full Stack Intern\' (ID 73)', '2026-04-17 14:56:41'),
(7, 2, 'Reject Internship', 'Rejected internship \'System Intern\' (ID 72)', '2026-04-17 14:56:44'),
(8, 2, 'Reject Internship', 'Rejected internship \'Frontend Intern\' (ID 70)', '2026-04-17 14:56:57'),
(9, 2, 'Approve Internship', 'Approved internship \'Backend Intern\' (ID 69)', '2026-04-17 14:57:03'),
(10, 2, 'Reject Internship', 'Rejected internship \'Software Intern\' (ID 68)', '2026-04-17 14:57:08'),
(11, 2, 'Reject Internship', 'Rejected internship \'Network Intern\' (ID 66)', '2026-04-17 14:57:13'),
(12, 2, 'Approve Internship', 'Approved internship \'Developer Intern\' (ID 67)', '2026-04-17 14:57:19'),
(13, 2, 'Reject Internship', 'Rejected internship \'Software Intern\' (ID 64)', '2026-04-17 14:57:33'),
(14, 2, 'Approve Internship', 'Approved internship \'IT Intern\' (ID 63)', '2026-04-17 14:57:45'),
(15, 150, 'Update application status', 'Changed application #17 for Juan Dela Cruz from Pending to Offered', '2026-04-19 14:55:54'),
(16, 156, 'Update application status', 'Changed application #18 for Juan Dela Cruz from Pending to Offered', '2026-04-20 06:48:13'),
(17, 1, 'Create user', 'Created new staff user: reivax0525@gmail.com', '2026-04-30 13:55:11'),
(18, 2, 'Create user', 'Created new staff user: gg@gmail.com', '2026-05-04 03:39:00'),
(19, 2, 'Create user', 'Created new staff user: verify@gmail.com', '2026-05-04 03:39:30'),
(20, 2, 'Create user', 'Created new staff user: hash@gmail.com', '2026-05-04 03:40:04');

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
  `status` enum('active','banned','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_login_count` int(11) DEFAULT 0,
  `login_attempt_count` int(11) DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT current_timestamp(),
  `mfa_enabled` tinyint(1) DEFAULT 0,
  `mfa_secret` varchar(255) DEFAULT NULL,
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `login_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `user_type`, `verified`, `created_at`, `updated_at`, `first_login`, `reset_token`, `token_expiry`, `status`, `last_login`, `last_login_ip`, `failed_login_count`, `login_attempt_count`, `last_failed_login`, `password_changed_at`, `mfa_enabled`, `mfa_secret`, `is_locked`, `locked_until`, `login_count`) VALUES
(1, 'superadmin@gmail.com', '$2y$10$oIGKeUK8PO0.sxiKA/ZcKOp28LFZV8y20mCvdnQLH2NcDrJblKkCi', 'superadmin', 1, '2026-04-01 14:42:54', '2026-04-03 11:31:29', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(2, 'admin@gmail.com', '$2y$10$tR3NJ0CkGP903/yCN1SwBePZW/jSLK4Se4LCu6crziPPEdkp3/fXi', 'admin', 1, '2026-04-01 14:43:48', '2026-04-01 14:43:48', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(3, 'reivax0525@gmail.com', '$2y$10$cCsgiKdA3c1lXVuRdGn/Gej/hdodAxHtzJzkG0SuBM3L69nMSXqP6', 'staff', 1, '2026-04-30 13:56:11', '2026-04-30 13:59:03', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-30 13:56:11', 0, NULL, 0, NULL, 0),
(4, 'xavierazcona0422@gmail.com', '$2y$10$tm1BLnCQuUqLufMCbOfdIOmMtIlhcjBQB7/crOPNAMlkE.Tt6KMbu', 'intern', 1, '2026-04-30 13:57:04', '2026-04-30 13:59:03', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-30 13:57:04', 0, NULL, 0, NULL, 0),
(61, 'juan.delacruz@gmail.com', '$2y$10$2DETqKVvRVNtE84RdS9tnOA6t/TaIBuYjnSnDo5kgcOJ2apKIYb2K', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(62, 'maria.garcia@gmail.com', '$2y$10$.fZmXdDL6/x99MFLAUkZHOWquhDFeh6U1K0Lk.pYsLKnWYBEixAqS', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(64, 'angela.flores@gmail.com', '$2y$10$Hv/c97177Opw7/VHwhc.p.M.wNr0EIhwqq3En32qfihMT5AH1xDb.', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(65, 'mark.santos@gmail.com', '$2y$10$a9ueGOJ5fIraIuId9rL3o.aKFKLUZnomDpjiAPyYpLsAsGtea8PMS', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(66, 'claire.mendoza@gmail.com', '$2y$10$lzaXWSM/wVqrKlsE9qNtH.ngIHBT75CME01IXCJjn3OUuXKd8Gvce', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(67, 'kevin.navarro@gmail.com', '$2y$10$3IZAHBGXvs6M5ZANl8zQUel4cpVXuKXORHB0/beXyynXJ6BgVgk5q', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(68, 'jessa.perez@gmail.com', '$2y$10$Hw7tDA8ofVBFrY0M3Y2XoeVdS6zRuME/fwo3tAK0AQsbMNPuegQLq', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(69, 'paul.gomez@gmail.com', '$2y$10$LWionyXxgPR0JdeS3ACtp.C8YAZH5EHZ53i/Yf9pLKAjgKHbA69Je', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(70, 'liza.morales@gmail.com', '$2y$10$0Zdz5qT3zDSc3vyPy.aRAOdYQH1kLN/I6gb7fZ5Gv48QabXCUXjWm', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(71, 'rico.lopez@gmail.com', '$2y$10$K6.AWEoIHoPYICJyRv90Be874WKEAYfrFZlZfNTVDo3WvJBl0vXYi', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(72, 'anne.reyes@gmail.com', '$2y$10$y5mFMBvWU/h/GEJf6j7cse0MIV/RA.rG2QHsQXT6nHYY815xESlJC', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(73, 'bryan.cruz@gmail.com', '$2y$10$UlOVyfm/WNR2mvntyh8GV.Ay5l58J814CP3PE20OacqqkOewV5CR6', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:11:10', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(74, 'diane.flores@gmail.com', '$2y$10$F5pi1DhQsZ/mmhi6tLpYeuQpSWSWXBV04pT.Sg3fLc8XorA6jWRue', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(75, 'leo.garcia@gmail.com', '$2y$10$UcNvZitzbaPGvqCXbK0hVePgDvbPBYPIiGUMdhkbgPBtbznGyar2O', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(76, 'mika.torres@gmail.com', '$2y$10$TQqoytTP4QNDsNghnBuI0uGnDQB7AGw6E7NFTGtzqgQehsfBwqmVO', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(77, 'noel.reyes@gmail.com', '$2y$10$1M0pf7LJYLi.Rm3l5spwgeFP.6Mk1ocG72ltaXThA0RhdeQqYXZ4W', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(78, 'ella.santos@gmail.com', '$2y$10$Q.ljlRWIDLKeXUtoj43DK.2QYCd2QwICYI.ffjAR/3OPe.fH2qgem', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(79, 'jason.flores@gmail.com', '$2y$10$1NnbZFKedpknmakveqSFkuL9wPDgDeKWQ4ZRiQNpb4G8BPeVL6yp2', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(80, 'kim.navarro@gmail.com', '$2y$10$ybmc.maJTLe7I072jZqSoOKmqKBH2V3jDi5H5OaMtHExovRM4k05e', 'intern', 1, '2026-04-17 14:00:26', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(81, 'carlo.garcia@gmail.com', '$2y$10$t9JlYYLA99WZvl7jYrJW0e9GQFH0nDm0DThoyDuSTvbAzusNYZnNy', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(82, 'faith.lopez@gmail.com', '$2y$10$lr/ML7mcaDDiBAG2sJ81dOB9SD.q04Xu0W2KqAyLnyP8ue1w2QYEO', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(83, 'allan.reyes@gmail.com', '$2y$10$4NpbA8S4YP3A7uVGi48Fz.48y4uw5wtq6Mp32M4o1t5qMmAKFyACu', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(84, 'joy.cruz@gmail.com', '$2y$10$/QSbYupZurleZbM0rI9VVeWE2QYVSmRv4/L7dn2zsc21mf2ChYijm', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(85, 'peter.flores@gmail.com', '$2y$10$KllLBx9GIo6MTO/p189RT.Guf42TYKIo.0KA/jh0Q5w2drfVBguOy', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:13:33', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(86, 'rose.torres@gmail.com', '$2y$10$2w0wbGXT2r1g8T4YXnWqFuxUbYKEZ.FNfLB8/fDnSF9LqqU9NYRFe', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(87, 'dennis.garcia@gmail.com', '$2y$10$N7uRQV3Esh4RH/E8bs66te3begIUbIdyeHdXfRN/XWXlQUPmkj/5i', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(88, 'ivy.reyes@gmail.com', '$2y$10$0NEuxn.x/lbQJS.3i4k0ZueqDoCgmMqtZCnrjcjUPxCI9o73sRYIu', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(89, 'joel.lopez@gmail.com', '$2y$10$tkPoSLi5PZw2u4eY2ytXH.LkxxLZzyJSQpV.YdHjwGdKCPbInwU0e', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(90, 'nina.flores@gmail.com', '$2y$10$OglHX4ETglahN3bVsM0pqela87ltJZXpNGEFPe8K21botJv9easaa', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(91, 'arvin.garcia@gmail.com', '$2y$10$2cifNxL66YCi7DI.iTzkKOqcR5/B1NN81FXB.QyFMM.bdFisp9iVK', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(92, 'grace.lopez@gmail.com', '$2y$10$tFLuTaX8HWVOSe.LpDvWnO14kwsmENA2jsw/BnA3ZawZp1wb1Mk1.', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(93, 'victor.flores@gmail.com', '$2y$10$f9o4.B2WqT8rtrkXnL0DK.96qEiWPW8wzYurgWB5AaKMKjrtbzrci', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(94, 'sheila.torres@gmail.com', '$2y$10$OibhlBLAgqIWLU5ICsXiXeTsCMN4UEJSaObCA3BCFpqI4T9QXVExq', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(95, 'henry.reyes@gmail.com', '$2y$10$bR8LRRS9McK.0ep/DZvqwOa6EhkbXldc1izESskXP9e47pkYIMW/i', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(96, 'paula.santos@gmail.com', '$2y$10$HbDNHgfUnfvmMWphBspZKettxC3OYstjOOBBRzkQtXw1r9zyA9x8i', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(97, 'ramon.cruz@gmail.com', '$2y$10$U4T6RTwSAWnI0l8iOorT8eHnKrBDfuVfH9KepUZEhL4OInuIZHWXa', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(98, 'tina.garcia@gmail.com', '$2y$10$g7l0QyfoyrjnWBt4n0Rixu5SGYFLm13tTh5WECdnE9Bdc112Pd43a', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:17:11', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(99, 'oscar.lopez@gmail.com', '$2y$10$xNqN6QLdqX2DPJzMm2itsOLx.YRG71xuOXyQU/ujwPZyYqwK7Ryzq', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(100, 'lara.reyes@gmail.com', '$2y$10$RPSRfRgWtdqkMfMr.ick5.Sbv8uw4A4.C1OKYCPfisvGlUedR/6WW', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(101, 'ethan.garcia@gmail.com', '$2y$10$l2mVamLB.8FI9WdrxDfPr.5Yl03LZZXLF4qqayAS0luzUxe9PdEOu', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(102, 'sophia.lopez@gmail.com', '$2y$10$EdNiwhssGg1KwFo/m3UAeexkJdDVAs38AgIMWoJWVDsy2/tyu2uuW', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(103, 'daniel.reyes@gmail.com', '$2y$10$YgocsM26SNbDLTY3M.hMD.FOK7n/N7zMUW97Uf/2uFwUEFvvk4B6O', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(104, 'alyssa.flores@gmail.com', '$2y$10$NZLlasZT4zqc0vH5FslYu.6cniPDHdFxQ5UQ4C/0r3F7wv8C6SLku', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(105, 'miguel.garcia@gmail.com', '$2y$10$g0ni1zUkH2gfkeoQGzUTqeNrSa5nT/3tXms0Ah5D59jKM4cnwfXXO', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(106, 'bianca.lopez@gmail.com', '$2y$10$sx9inr3/SKJ8cYogi4RAoOVoQYzUbNZyN/xVscCfg4W3GgeKgLl6K', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(107, 'adrian.reyes@gmail.com', '$2y$10$78CTmGpmTB/N0G7mRWEmJuR7G/fUMf2pNscF8GIRjWx71nMzzdh1u', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(108, 'trisha.cruz@gmail.com', '$2y$10$puaQa9NdCbUFbPP9zPKKBuYuyFiiSVxpiRRfs0GlC5aPRNWbt/nQy', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(109, 'nathan.flores@gmail.com', '$2y$10$XEFxeuzdrgV1ZU0lgZPd5OR6hTDBdFUPFoc2zc4PR2xVjuQYhpfO2', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(110, 'chloe.garcia@gmail.com', '$2y$10$wySLNBVOA5q9x.WWwCYg7.Ki6HhZEYf7nZ6MIBTTXNMK9PfZNyFmm', 'intern', 1, '2026-04-17 14:04:18', '2026-04-17 14:18:59', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(111, 'alex.rivera@nexora.com', '$2y$10$AOeH4/IX5F2oDlTWmxmsHedOm.AkpXvw8YTV0QCrzm5SHPzYs67pm', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(112, 'jasmine.lee@cloudspire.com', '$2y$10$7LpN.w7Cm2IjZ6xeyGkhYeBgq0Uy.asSYa4J3iIkM0TYvNTiOUX.m', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(113, 'kevin.tan@byteforge.com', '$2y$10$ulCS0XW8ERuMkMKuJTzAiuGyolT/kT9nNivpxNOil6UDfEb.mh4X6', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(114, 'michelle.sy@zenithworks.com', '$2y$10$vJuzK.ff51DVoC6xXR.paehAip8ptNRjJ4DiJR2cqHGNj8Z1OdJ.a', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(115, 'daniel.ong@infinitix.com', '$2y$10$Pqf0CBqacvA2n4tREjkvbOuFqAk18JREEr23ZpJuvB8mUKyEpa3RK', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(116, 'karen.lim@pixelcraft.com', '$2y$10$Dn/maVAzPs4/II2lVDfTLe2aIqI/1olb5EdjIHg32joluvWy0pqEC', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(117, 'ryan.chua@codewave.com', '$2y$10$Gf0hd3roMbIjwGRZCo7Hpulo49nLaUxs0V7W0YkRy1QtmnKTYWfAS', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(118, 'sophia.nguyen@skygrid.com', '$2y$10$3N62bDn/6jxMJ9rGqGFFzODFrqgwVgIOCRheeMDgZMd1IoXbHL4lW', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(119, 'ethan.kim@vertexlabs.com', '$2y$10$KQrKRbIm1M8SczeRqXrVl.FQQyPK1Kyys0QqWfKbdW.ekNp85Rz5S', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(120, 'louis.garcia@brightpath.com', '$2y$10$8d1PJ5Yh0xgtuFCbxwNCBujWJhLRyYLjk.rCVEnAt4kG.P6uP5ko6', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(121, 'chris.morales@logicore.com', '$2y$10$mv5zHYKMQUMWParynG/Vo.wwTureb3K2MpImlGkN3YieOJv37/qCW', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(122, 'anna.reyes@nextgenhub.com', '$2y$10$iZzRx2ixNFEeODhw25IQjeLrTryTezqY7gA/rI5gDlfoOzpOV4odG', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(123, 'paul.dizon@techtonic.com', '$2y$10$PQCKupQlfAuBGTCVnduFkONB9.c5Zy7sIac2AOOibgfaoNIypJ14u', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:36:06', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(124, 'grace.velasco@softspark.com', '$2y$10$w25Y13WWYakLUFxMItRWKuCa2KZM4WdeEcS.QwIr2Ea6rpJLRrfn.', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(125, 'mark.bautista@digisphere.com', '$2y$10$cODJ4D2z.iRWDXUViUkwXe9iKV/CQU0fYWeTLrOivj351xYnSB0Ye', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(126, 'liza.fuentes@alphatech.com', '$2y$10$bFNLaw/nrUINo3ELkUR39.OgvVNJpM.MjECgzQPIvSHARzkIFqEma', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(127, 'ian.santos@corelink.com', '$2y$10$Tyx2VcGe1Yfhopyp9VwcZO.4yRnpaauxSe8eLjEKLAQfH/Ktfycqa', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(128, 'ella.perez@cloudnova.com', '$2y$10$psBO4g1VjzUnxKTFkXLMVOAA7GdK9N3O.W4aiMR1s44kwdDCiVAYC', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(129, 'ronnie.castro@datasurge.com', '$2y$10$aoVVcZLRSkXDh/wK2OII0.Y6iHV0E3x.nvf5xfxMCIMixcMF.T85m', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(130, 'mia.ramos@futurestack.com', '$2y$10$N9wBATvJZiSJQDLB4T8SZuc4XP5jAaryq8xICGddCoqmb7sjOg9DK', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(131, 'noah.lopez@quantix.com', '$2y$10$fGd6aBmkzsVMtAhZ3Imny.ApxsnjdBatmTujwiORYpkn0h9DYaZdy', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(132, 'ivy.torres@bluecore.com', '$2y$10$8ZjKXhOyctId/q6qkEOli.VB3glD69UJOlaKBf8YQEZGBNGFJ.Ko2', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(133, 'joshua.gomez@orbitware.com', '$2y$10$StdRO6g7jFt8ql8mGcmEKuza0xalYCps4eX9yvjcturdDCDe5d29q', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(134, 'nicole.mendoza@peaklogic.com', '$2y$10$d8kypI7sn4NpbOfgt/NqtueKZxCK1dLEfz8YdT1x0wU88M.JJwoC2', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(135, 'adrian.aquino@innovix.com', '$2y$10$1VgOp/SSKUun4Hhr9H2CEutyzDGHz8z./XVw.rP1SUwHUsQUc2C3K', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(136, 'trisha.valdez@webworks.com', '$2y$10$s1oKQ6laghId905bxI3NQ.i7pvvaaflImIGqqWuPBM0Rzi0e4.U3m', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:38:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(137, 'carl.domingo@softforge.com', '$2y$10$TyvQikSENccOqABpcofAmeHcn4qTiIvqdfVViaMx1o7oFzwNsv0Mm', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(138, 'bianca.castillo@cloudmesh.com', '$2y$10$kE692apjyQiJav8Boj8hu.05YByrzwleHebm/s6VZSRXJGoNNYhh6', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(139, 'vince.ortega@bitlabs.com', '$2y$10$oDC7nPV9QEALKSaAiVNikOuPcSlt8XB0DNUQ2KUdvJmgZym9CiLQu', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(140, 'diana.pascual@digicore.com', '$2y$10$zWgUfdlO/C7cxLn7CrkY/OWPMOF3fKRgbnkX23IMaLSZfpM8j.Uie', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(141, 'leo.ferrer@techflow.com', '$2y$10$ZkEy5O1bhdy.eUi1tVLQOugiYvxUDUO72Zuz6EgPPMkoeYcbOJr2G', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(142, 'sarah.deguzman@codexia.com', '$2y$10$jb1NKErEVJNmcGAdKgsho.WibgvNWNTg6Hy0ynyG6/POlX5GqyFnC', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(143, 'miguel.navarro@infonity.com', '$2y$10$huzVKx1AuplerCgl7Hm2pOsCt2w96Ihw7KrBYZxN8I2H2HdBktFUG', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(144, 'kylie.salazar@netprime.com', '$2y$10$RwCWCW9/CmXqKQW5QiWOUecxmwQRtVP4aqNjDBR4yzUmn8mX4tyWC', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(145, 'omar.delacruz@devspire.com', '$2y$10$L/nq1S5PUVLZMTlXK8Rcu.9sn7jjcqbQDZAvdRUNzR/qjyf8Grz1q', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(146, 'angelica.martinez@brightlabs.com', '$2y$10$6XFs/fY5h1tf6bhZmNVm0ezaYz4TpW1/C5wNnu7z0DsIRwMGzoUby', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(147, 'jay.rivero@logicflow.com', '$2y$10$69IQpXkcNYyVasCn/mgDGe0H05.CQSX/.49K09d3LcMZS7JDtuFBe', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(148, 'shane.fernandez@cloudaxis.com', '$2y$10$8z5GMril55gh6qL7Y234puctJqAU1S6MZXTfEib51aexylqeI6RNe', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(149, 'nina.villanueva@cybercore.com', '$2y$10$Kp/U68sl2z0pWmv4scU7ReLecPOrUtPD81sZ4LN6.kaYHoG7G8fha', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:40:51', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(150, 'enzo.cruz@hypertech.com', '$2y$10$Jr5LPESvOZlRSk3RGQmy8..2GxiOdnclrL.7/huedk2xVx6ws0Xca', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(151, 'rafael.dela.cruz@novasys.com', '$2y$10$6olCVK4OT1AdA903ktK6AeOx9xDSyoeepWWrmjXq1shmvkTz5q8Rm', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(152, 'patricia.garcia@smartware.com', '$2y$10$586Yia4D8eKU35BMgT8QLum3X67yBRdf.5PQFPqL.Z2DjsT0Jnrya', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(153, 'keith.moreno@netcore.com', '$2y$10$1OuAw1DquSOP9XDVXiHmQuWsEyJKcWAZf3Ej4UuUJXM0ravy8ypCO', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(154, 'alyssa.chavez@infoworks.com', '$2y$10$sWY4TzUSrl.L4fAt5PSbBORF85JdmzFA3/HEs8aaBYp8BJT6a2.kq', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(155, 'brandon.pineda@techverse.com', '$2y$10$GQ99I4cjhEkJNONWZjxLPuLTctLaynQ2EHJNqnC6UsYeo1qDkcVEu', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(156, 'camille.soriano@bytecore.com', '$2y$10$85feup1qAz2c/EPmqIlXteM07iRwipdtfmwgjAS30nL7vsOHc8KOe', 'staff', 1, '2026-04-17 14:24:23', '2026-04-17 14:42:02', 0, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-04-20 21:38:45', 0, NULL, 0, NULL, 0),
(167, 'gg@gmail.com', '$2y$10$5CYhCs/LXrUxqHV8Ja3cNOBEGcaMEWMZ9oE2LexPLgQARXqIQVa5W', 'staff', 1, '2026-05-04 03:39:00', '2026-05-04 03:39:00', 1, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-05-04 03:39:00', 0, NULL, 0, NULL, 0),
(168, 'verify@gmail.com', '$2y$10$l7.wXi8VeVFkcVd0zPt0ceVSaaV5whzC7Tne7xsk.qyLWw1ADIMsG', 'staff', 1, '2026-05-04 03:39:30', '2026-05-04 03:39:30', 1, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-05-04 03:39:30', 0, NULL, 0, NULL, 0),
(169, 'hash@gmail.com', '$2y$10$jB3AGIgXVuRhBywvKen0puNGQq.dJtzBoDA5V/nVjQFuy67bG.6Uq', 'staff', 1, '2026-05-04 03:40:04', '2026-05-04 03:40:04', 1, NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-05-04 03:40:04', 0, NULL, 0, NULL, 0);

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
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_entity` (`entity_id`,`entity_type`),
  ADD KEY `idx_primary` (`entity_id`,`entity_type`,`is_primary`),
  ADD KEY `idx_addresses_intern_id` (`intern_id`),
  ADD KEY `idx_addresses_company_id` (`company_id`);

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
-- Indexes for table `report_logs`
--
ALTER TABLE `report_logs`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `interns`
--
ALTER TABLE `interns`
  MODIFY `intern_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `report_logs`
--
ALTER TABLE `report_logs`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

UPDATE `addresses` a
INNER JOIN `interns` i ON i.`intern_id` = a.`entity_id`
SET a.`intern_id` = a.`entity_id`
WHERE a.`entity_type` = 'intern';

UPDATE `addresses` a
INNER JOIN `companies` c ON c.`company_id` = a.`entity_id`
SET a.`company_id` = a.`entity_id`
WHERE a.`entity_type` = 'company';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`intern_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addresses_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

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
-- Constraints for table `report_logs`
--
ALTER TABLE `report_logs`
  ADD CONSTRAINT `report_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `report_logs_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`);

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
