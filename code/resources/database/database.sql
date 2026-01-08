-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Aug 17, 2025 at 07:56 AM
-- Server version: 8.0.42
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `beepost`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_settings` longtext COLLATE utf8mb4_unicode_ci,
  `permissions` longtext COLLATE utf8mb4_unicode_ci,
  `address` longtext COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Deactive: 0',
  `super_admin` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `created_by`, `updated_by`, `uid`, `role_id`, `username`, `name`, `phone`, `email`, `notification_settings`, `permissions`, `address`, `email_verified_at`, `password`, `status`, `super_admin`, `last_login`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, '30bc94bd-76e2-45d8-b704-d15a352a6199', NULL, 'admin', 'SuperAdmin', NULL, 'admin@beepost.com', NULL, NULL, NULL, '2025-08-17 02:56:35', '$2y$10$HeOZKM/hz3Ca4vLPMfDso.ZVLnBRCyLyeteq0fMjrSdjAe7PUHPTK', '1', '1', NULL, NULL, NULL, '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_logs`
--

CREATE TABLE `affiliate_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `referred_to` bigint UNSIGNED DEFAULT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `commission_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `commission_rate` double(25,5) NOT NULL DEFAULT '0.00000',
  `trx_code` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_templates`
--

CREATE TABLE `ai_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `sub_category_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt_fields` longtext COLLATE utf8mb4_unicode_ci,
  `custom_prompt` text COLLATE utf8mb4_unicode_ci,
  `total_words` int NOT NULL DEFAULT '0',
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active : 1,Inactive : 0',
  `is_default` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Yes : 1,No : 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `is_feature` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Yes: 1, No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `uid`, `created_by`, `updated_by`, `category_id`, `title`, `slug`, `description`, `meta_title`, `meta_description`, `meta_keywords`, `status`, `is_feature`, `created_at`, `updated_at`) VALUES
(1, 'b54644f0-e8e3-4420-8624-eace28d97bb1', NULL, NULL, NULL, 'We launch pulsar template this week', 'we-launch-pulsar-template-this-week', 'description', NULL, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, 'd5e2a1dc-2bd9-41a6-b621-5d351bbfd3b4', NULL, NULL, NULL, 'Template this week', 'template-this-week', 'description', NULL, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, 'a2b5b8b2-af93-4a6d-9601-8e554f17876d', NULL, NULL, NULL, 'AI content', 'AI-content', 'description', NULL, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, '82fb45a5-6c1c-413b-8163-47b6088a2bc2', NULL, NULL, NULL, 'Social posting', 'social-posting', 'description', NULL, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(5, '0ff96c70-ace7-454f-8c0e-1b00f989aab1', NULL, NULL, NULL, 'Post management', 'post-management', 'description', NULL, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ai_module_type` enum('text','image','video') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `display_in` enum('0','1','2') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '0: Blog, 1: Template, 2: Both',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Active : 1,Inactive : 0',
  `type` enum('text','image','video') COLLATE utf8mb4_unicode_ci DEFAULT 'text' COMMENT 'TEXT : text, IMAGE : image, VIDEO : video',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint UNSIGNED NOT NULL,
  `updated_by` mediumint UNSIGNED DEFAULT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(155) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(155) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_code` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_blocked` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'No: 0, Yes: 1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `updated_by`, `uid`, `name`, `code`, `phone_code`, `is_blocked`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'Afghanistan', 'AF', '93', '0', NULL, NULL),
(2, NULL, NULL, 'Albania', 'AL', '355', '0', NULL, NULL),
(3, NULL, NULL, 'Algeria', 'DZ', '213', '0', NULL, NULL),
(4, NULL, NULL, 'American Samoa', 'AS', '1684', '0', NULL, NULL),
(5, NULL, NULL, 'Andorra', 'AD', '376', '0', NULL, NULL),
(6, NULL, NULL, 'Angola', 'AO', '244', '0', NULL, NULL),
(7, NULL, NULL, 'Anguilla', 'AI', '1264', '0', NULL, NULL),
(8, NULL, NULL, 'Antarctica', 'AQ', '0', '0', NULL, NULL),
(9, NULL, NULL, 'Antigua And Barbuda', 'AG', '1268', '0', NULL, NULL),
(10, NULL, NULL, 'Argentina', 'AR', '54', '0', NULL, NULL),
(11, NULL, NULL, 'Armenia', 'AM', '374', '0', NULL, NULL),
(12, NULL, NULL, 'Aruba', 'AW', '297', '0', NULL, NULL),
(13, NULL, NULL, 'Australia', 'AU', '61', '0', NULL, NULL),
(14, NULL, NULL, 'Austria', 'AT', '43', '0', NULL, NULL),
(15, NULL, NULL, 'Azerbaijan', 'AZ', '994', '0', NULL, NULL),
(16, NULL, NULL, 'Bahamas The', 'BS', '1242', '0', NULL, NULL),
(17, NULL, NULL, 'Bahrain', 'BH', '973', '0', NULL, NULL),
(18, NULL, NULL, 'Bangladesh', 'BD', '880', '0', NULL, NULL),
(19, NULL, NULL, 'Barbados', 'BB', '1246', '0', NULL, NULL),
(20, NULL, NULL, 'Belarus', 'BY', '375', '0', NULL, NULL),
(21, NULL, NULL, 'Belgium', 'BE', '32', '0', NULL, NULL),
(22, NULL, NULL, 'Belize', 'BZ', '501', '0', NULL, NULL),
(23, NULL, NULL, 'Benin', 'BJ', '229', '0', NULL, NULL),
(24, NULL, NULL, 'Bermuda', 'BM', '1441', '0', NULL, NULL),
(25, NULL, NULL, 'Bhutan', 'BT', '975', '0', NULL, NULL),
(26, NULL, NULL, 'Bolivia', 'BO', '591', '0', NULL, NULL),
(27, NULL, NULL, 'Bosnia and Herzegovina', 'BA', '387', '0', NULL, NULL),
(28, NULL, NULL, 'Botswana', 'BW', '267', '0', NULL, NULL),
(29, NULL, NULL, 'Bouvet Island', 'BV', '0', '0', NULL, NULL),
(30, NULL, NULL, 'Brazil', 'BR', '55', '0', NULL, NULL),
(31, NULL, NULL, 'British Indian Ocean Territory', 'IO', '246', '0', NULL, NULL),
(32, NULL, NULL, 'Brunei', 'BN', '673', '0', NULL, NULL),
(33, NULL, NULL, 'Bulgaria', 'BG', '359', '0', NULL, NULL),
(34, NULL, NULL, 'Burkina Faso', 'BF', '226', '0', NULL, NULL),
(35, NULL, NULL, 'Burundi', 'BI', '257', '0', NULL, NULL),
(36, NULL, NULL, 'Cambodia', 'KH', '855', '0', NULL, NULL),
(37, NULL, NULL, 'Cameroon', 'CM', '237', '0', NULL, NULL),
(38, NULL, NULL, 'Canada', 'CA', '1', '0', NULL, NULL),
(39, NULL, NULL, 'Cape Verde', 'CV', '238', '0', NULL, NULL),
(40, NULL, NULL, 'Cayman Islands', 'KY', '1345', '0', NULL, NULL),
(41, NULL, NULL, 'Central African Republic', 'CF', '236', '0', NULL, NULL),
(42, NULL, NULL, 'Chad', 'TD', '235', '0', NULL, NULL),
(43, NULL, NULL, 'Chile', 'CL', '56', '0', NULL, NULL),
(44, NULL, NULL, 'China', 'CN', '86', '0', NULL, NULL),
(45, NULL, NULL, 'Christmas Island', 'CX', '61', '0', NULL, NULL),
(46, NULL, NULL, 'Cocos (Keeling) Islands', 'CC', '672', '0', NULL, NULL),
(47, NULL, NULL, 'Colombia', 'CO', '57', '0', NULL, NULL),
(48, NULL, NULL, 'Comoros', 'KM', '269', '0', NULL, NULL),
(49, NULL, NULL, 'Republic Of The Congo', 'CG', '242', '0', NULL, NULL),
(50, NULL, NULL, 'Democratic Republic Of The Congo', 'CD', '242', '0', NULL, NULL),
(51, NULL, NULL, 'Cook Islands', 'CK', '682', '0', NULL, NULL),
(52, NULL, NULL, 'Costa Rica', 'CR', '506', '0', NULL, NULL),
(53, NULL, NULL, 'Cote D\'\'Ivoire (Ivory Coast)', 'CI', '225', '0', NULL, NULL),
(54, NULL, NULL, 'Croatia (Hrvatska)', 'HR', '385', '0', NULL, NULL),
(55, NULL, NULL, 'Cuba', 'CU', '53', '0', NULL, NULL),
(56, NULL, NULL, 'Cyprus', 'CY', '357', '0', NULL, NULL),
(57, NULL, NULL, 'Czech Republic', 'CZ', '420', '0', NULL, NULL),
(58, NULL, NULL, 'Denmark', 'DK', '45', '0', NULL, NULL),
(59, NULL, NULL, 'Djibouti', 'DJ', '253', '0', NULL, NULL),
(60, NULL, NULL, 'Dominica', 'DM', '1767', '0', NULL, NULL),
(61, NULL, NULL, 'Dominican Republic', 'DO', '1809', '0', NULL, NULL),
(62, NULL, NULL, 'East Timor', 'TP', '670', '0', NULL, NULL),
(63, NULL, NULL, 'Ecuador', 'EC', '593', '0', NULL, NULL),
(64, NULL, NULL, 'Egypt', 'EG', '20', '0', NULL, NULL),
(65, NULL, NULL, 'El Salvador', 'SV', '503', '0', NULL, NULL),
(66, NULL, NULL, 'Equatorial Guinea', 'GQ', '240', '0', NULL, NULL),
(67, NULL, NULL, 'Eritrea', 'ER', '291', '0', NULL, NULL),
(68, NULL, NULL, 'Estonia', 'EE', '372', '0', NULL, NULL),
(69, NULL, NULL, 'Ethiopia', 'ET', '251', '0', NULL, NULL),
(70, NULL, NULL, 'External Territories of Australia', 'XA', '61', '0', NULL, NULL),
(71, NULL, NULL, 'Falkland Islands', 'FK', '500', '0', NULL, NULL),
(72, NULL, NULL, 'Faroe Islands', 'FO', '298', '0', NULL, NULL),
(73, NULL, NULL, 'Fiji Islands', 'FJ', '679', '0', NULL, NULL),
(74, NULL, NULL, 'Finland', 'FI', '358', '0', NULL, NULL),
(75, NULL, NULL, 'France', 'FR', '33', '0', NULL, NULL),
(76, NULL, NULL, 'French Guiana', 'GF', '594', '0', NULL, NULL),
(77, NULL, NULL, 'French Polynesia', 'PF', '689', '0', NULL, NULL),
(78, NULL, NULL, 'French Southern Territories', 'TF', '0', '0', NULL, NULL),
(79, NULL, NULL, 'Gabon', 'GA', '241', '0', NULL, NULL),
(80, NULL, NULL, 'Gambia The', 'GM', '220', '0', NULL, NULL),
(81, NULL, NULL, 'Georgia', 'GE', '995', '0', NULL, NULL),
(82, NULL, NULL, 'Germany', 'DE', '49', '0', NULL, NULL),
(83, NULL, NULL, 'Ghana', 'GH', '233', '0', NULL, NULL),
(84, NULL, NULL, 'Gibraltar', 'GI', '350', '0', NULL, NULL),
(85, NULL, NULL, 'Greece', 'GR', '30', '0', NULL, NULL),
(86, NULL, NULL, 'Greenland', 'GL', '299', '0', NULL, NULL),
(87, NULL, NULL, 'Grenada', 'GD', '1473', '0', NULL, NULL),
(88, NULL, NULL, 'Guadeloupe', 'GP', '590', '0', NULL, NULL),
(89, NULL, NULL, 'Guam', 'GU', '1671', '0', NULL, NULL),
(90, NULL, NULL, 'Guatemala', 'GT', '502', '0', NULL, NULL),
(91, NULL, NULL, 'Guernsey and Alderney', 'XU', '44', '0', NULL, NULL),
(92, NULL, NULL, 'Guinea', 'GN', '224', '0', NULL, NULL),
(93, NULL, NULL, 'Guinea-Bissau', 'GW', '245', '0', NULL, NULL),
(94, NULL, NULL, 'Guyana', 'GY', '592', '0', NULL, NULL),
(95, NULL, NULL, 'Haiti', 'HT', '509', '0', NULL, NULL),
(96, NULL, NULL, 'Heard and McDonald Islands', 'HM', '0', '0', NULL, NULL),
(97, NULL, NULL, 'Honduras', 'HN', '504', '0', NULL, NULL),
(98, NULL, NULL, 'Hong Kong S.A.R.', 'HK', '852', '0', NULL, NULL),
(99, NULL, NULL, 'Hungary', 'HU', '36', '0', NULL, NULL),
(100, NULL, NULL, 'Iceland', 'IS', '354', '0', NULL, NULL),
(101, NULL, NULL, 'India', 'IN', '91', '0', NULL, NULL),
(102, NULL, NULL, 'Indonesia', 'ID', '62', '0', NULL, NULL),
(103, NULL, NULL, 'Iran', 'IR', '98', '0', NULL, NULL),
(104, NULL, NULL, 'Iraq', 'IQ', '964', '0', NULL, NULL),
(105, NULL, NULL, 'Ireland', 'IE', '353', '0', NULL, NULL),
(106, NULL, NULL, 'Israel', 'IL', '972', '0', NULL, NULL),
(107, NULL, NULL, 'Italy', 'IT', '39', '0', NULL, NULL),
(108, NULL, NULL, 'Jamaica', 'JM', '1876', '0', NULL, NULL),
(109, NULL, NULL, 'Japan', 'JP', '81', '0', NULL, NULL),
(110, NULL, NULL, 'Jersey', 'XJ', '44', '0', NULL, NULL),
(111, NULL, NULL, 'Jordan', 'JO', '962', '0', NULL, NULL),
(112, NULL, NULL, 'Kazakhstan', 'KZ', '7', '0', NULL, NULL),
(113, NULL, NULL, 'Kenya', 'KE', '254', '0', NULL, NULL),
(114, NULL, NULL, 'Kiribati', 'KI', '686', '0', NULL, NULL),
(115, NULL, NULL, 'Korea North', 'KP', '850', '0', NULL, NULL),
(116, NULL, NULL, 'Korea South', 'KR', '82', '0', NULL, NULL),
(117, NULL, NULL, 'Kuwait', 'KW', '965', '0', NULL, NULL),
(118, NULL, NULL, 'Kyrgyzstan', 'KG', '996', '0', NULL, NULL),
(119, NULL, NULL, 'Laos', 'LA', '856', '0', NULL, NULL),
(120, NULL, NULL, 'Latvia', 'LV', '371', '0', NULL, NULL),
(121, NULL, NULL, 'Lebanon', 'LB', '961', '0', NULL, NULL),
(122, NULL, NULL, 'Lesotho', 'LS', '266', '0', NULL, NULL),
(123, NULL, NULL, 'Liberia', 'LR', '231', '0', NULL, NULL),
(124, NULL, NULL, 'Libya', 'LY', '218', '0', NULL, NULL),
(125, NULL, NULL, 'Liechtenstein', 'LI', '423', '0', NULL, NULL),
(126, NULL, NULL, 'Lithuania', 'LT', '370', '0', NULL, NULL),
(127, NULL, NULL, 'Luxembourg', 'LU', '352', '0', NULL, NULL),
(128, NULL, NULL, 'Macau S.A.R.', 'MO', '853', '0', NULL, NULL),
(129, NULL, NULL, 'Macedonia', 'MK', '389', '0', NULL, NULL),
(130, NULL, NULL, 'Madagascar', 'MG', '261', '0', NULL, NULL),
(131, NULL, NULL, 'Malawi', 'MW', '265', '0', NULL, NULL),
(132, NULL, NULL, 'Malaysia', 'MY', '60', '0', NULL, NULL),
(133, NULL, NULL, 'Maldives', 'MV', '960', '0', NULL, NULL),
(134, NULL, NULL, 'Mali', 'ML', '223', '0', NULL, NULL),
(135, NULL, NULL, 'Malta', 'MT', '356', '0', NULL, NULL),
(136, NULL, NULL, 'Man (Isle of)', 'XM', '44', '0', NULL, NULL),
(137, NULL, NULL, 'Marshall Islands', 'MH', '692', '0', NULL, NULL),
(138, NULL, NULL, 'Martinique', 'MQ', '596', '0', NULL, NULL),
(139, NULL, NULL, 'Mauritania', 'MR', '222', '0', NULL, NULL),
(140, NULL, NULL, 'Mauritius', 'MU', '230', '0', NULL, NULL),
(141, NULL, NULL, 'Mayotte', 'YT', '269', '0', NULL, NULL),
(142, NULL, NULL, 'Mexico', 'MX', '52', '0', NULL, NULL),
(143, NULL, NULL, 'Micronesia', 'FM', '691', '0', NULL, NULL),
(144, NULL, NULL, 'Moldova', 'MD', '373', '0', NULL, NULL),
(145, NULL, NULL, 'Monaco', 'MC', '377', '0', NULL, NULL),
(146, NULL, NULL, 'Mongolia', 'MN', '976', '0', NULL, NULL),
(147, NULL, NULL, 'Montserrat', 'MS', '1664', '0', NULL, NULL),
(148, NULL, NULL, 'Morocco', 'MA', '212', '0', NULL, NULL),
(149, NULL, NULL, 'Mozambique', 'MZ', '258', '0', NULL, NULL),
(150, NULL, NULL, 'Myanmar', 'MM', '95', '0', NULL, NULL),
(151, NULL, NULL, 'Namibia', 'NA', '264', '0', NULL, NULL),
(152, NULL, NULL, 'Nauru', 'NR', '674', '0', NULL, NULL),
(153, NULL, NULL, 'Nepal', 'NP', '977', '0', NULL, NULL),
(154, NULL, NULL, 'Netherlands Antilles', 'AN', '599', '0', NULL, NULL),
(155, NULL, NULL, 'Netherlands The', 'NL', '31', '0', NULL, NULL),
(156, NULL, NULL, 'New Caledonia', 'NC', '687', '0', NULL, NULL),
(157, NULL, NULL, 'New Zealand', 'NZ', '64', '0', NULL, NULL),
(158, NULL, NULL, 'Nicaragua', 'NI', '505', '0', NULL, NULL),
(159, NULL, NULL, 'Niger', 'NE', '227', '0', NULL, NULL),
(160, NULL, NULL, 'Nigeria', 'NG', '234', '0', NULL, NULL),
(161, NULL, NULL, 'Niue', 'NU', '683', '0', NULL, NULL),
(162, NULL, NULL, 'Norfolk Island', 'NF', '672', '0', NULL, NULL),
(163, NULL, NULL, 'Northern Mariana Islands', 'MP', '1670', '0', NULL, NULL),
(164, NULL, NULL, 'Norway', 'NO', '47', '0', NULL, NULL),
(165, NULL, NULL, 'Oman', 'OM', '968', '0', NULL, NULL),
(166, NULL, NULL, 'Pakistan', 'PK', '92', '0', NULL, NULL),
(167, NULL, NULL, 'Palau', 'PW', '680', '0', NULL, NULL),
(168, NULL, NULL, 'Palestinian Territory Occupied', 'PS', '970', '0', NULL, NULL),
(169, NULL, NULL, 'Panama', 'PA', '507', '0', NULL, NULL),
(170, NULL, NULL, 'Papua new Guinea', 'PG', '675', '0', NULL, NULL),
(171, NULL, NULL, 'Paraguay', 'PY', '595', '0', NULL, NULL),
(172, NULL, NULL, 'Peru', 'PE', '51', '0', NULL, NULL),
(173, NULL, NULL, 'Philippines', 'PH', '63', '0', NULL, NULL),
(174, NULL, NULL, 'Pitcairn Island', 'PN', '0', '0', NULL, NULL),
(175, NULL, NULL, 'Poland', 'PL', '48', '0', NULL, NULL),
(176, NULL, NULL, 'Portugal', 'PT', '351', '0', NULL, NULL),
(177, NULL, NULL, 'Puerto Rico', 'PR', '1787', '0', NULL, NULL),
(178, NULL, NULL, 'Qatar', 'QA', '974', '0', NULL, NULL),
(179, NULL, NULL, 'Reunion', 'RE', '262', '0', NULL, NULL),
(180, NULL, NULL, 'Romania', 'RO', '40', '0', NULL, NULL),
(181, NULL, NULL, 'Russia', 'RU', '70', '0', NULL, NULL),
(182, NULL, NULL, 'Rwanda', 'RW', '250', '0', NULL, NULL),
(183, NULL, NULL, 'Saint Helena', 'SH', '290', '0', NULL, NULL),
(184, NULL, NULL, 'Saint Kitts And Nevis', 'KN', '1869', '0', NULL, NULL),
(185, NULL, NULL, 'Saint Lucia', 'LC', '1758', '0', NULL, NULL),
(186, NULL, NULL, 'Saint Pierre and Miquelon', 'PM', '508', '0', NULL, NULL),
(187, NULL, NULL, 'Saint Vincent And The Grenadines', 'VC', '1784', '0', NULL, NULL),
(188, NULL, NULL, 'Samoa', 'WS', '684', '0', NULL, NULL),
(189, NULL, NULL, 'San Marino', 'SM', '378', '0', NULL, NULL),
(190, NULL, NULL, 'Sao Tome and Principe', 'ST', '239', '0', NULL, NULL),
(191, NULL, NULL, 'Saudi Arabia', 'SA', '966', '0', NULL, NULL),
(192, NULL, NULL, 'Senegal', 'SN', '221', '0', NULL, NULL),
(193, NULL, NULL, 'Serbia', 'RS', '381', '0', NULL, NULL),
(194, NULL, NULL, 'Seychelles', 'SC', '248', '0', NULL, NULL),
(195, NULL, NULL, 'Sierra Leone', 'SL', '232', '0', NULL, NULL),
(196, NULL, NULL, 'Singapore', 'SG', '65', '0', NULL, NULL),
(197, NULL, NULL, 'Slovakia', 'SK', '421', '0', NULL, NULL),
(198, NULL, NULL, 'Slovenia', 'SI', '386', '0', NULL, NULL),
(199, NULL, NULL, 'Smaller Territories of the UK', 'XG', '44', '0', NULL, NULL),
(200, NULL, NULL, 'Solomon Islands', 'SB', '677', '0', NULL, NULL),
(201, NULL, NULL, 'Somalia', 'SO', '252', '0', NULL, NULL),
(202, NULL, NULL, 'South Africa', 'ZA', '27', '0', NULL, NULL),
(203, NULL, NULL, 'South Georgia', 'GS', '0', '0', NULL, NULL),
(204, NULL, NULL, 'South Sudan', 'SS', '211', '0', NULL, NULL),
(205, NULL, NULL, 'Spain', 'ES', '34', '0', NULL, NULL),
(206, NULL, NULL, 'Sri Lanka', 'LK', '94', '0', NULL, NULL),
(207, NULL, NULL, 'Sudan', 'SD', '249', '0', NULL, NULL),
(208, NULL, NULL, 'Suriname', 'SR', '597', '0', NULL, NULL),
(209, NULL, NULL, 'Svalbard And Jan Mayen Islands', 'SJ', '47', '0', NULL, NULL),
(210, NULL, NULL, 'Swaziland', 'SZ', '268', '0', NULL, NULL),
(211, NULL, NULL, 'Sweden', 'SE', '46', '0', NULL, NULL),
(212, NULL, NULL, 'Switzerland', 'CH', '41', '0', NULL, NULL),
(213, NULL, NULL, 'Syria', 'SY', '963', '0', NULL, NULL),
(214, NULL, NULL, 'Taiwan', 'TW', '886', '0', NULL, NULL),
(215, NULL, NULL, 'Tajikistan', 'TJ', '992', '0', NULL, NULL),
(216, NULL, NULL, 'Tanzania', 'TZ', '255', '0', NULL, NULL),
(217, NULL, NULL, 'Thailand', 'TH', '66', '0', NULL, NULL),
(218, NULL, NULL, 'Togo', 'TG', '228', '0', NULL, NULL),
(219, NULL, NULL, 'Tokelau', 'TK', '690', '0', NULL, NULL),
(220, NULL, NULL, 'Tonga', 'TO', '676', '0', NULL, NULL),
(221, NULL, NULL, 'Trinidad And Tobago', 'TT', '1868', '0', NULL, NULL),
(222, NULL, NULL, 'Tunisia', 'TN', '216', '0', NULL, NULL),
(223, NULL, NULL, 'Turkey', 'TR', '90', '0', NULL, NULL),
(224, NULL, NULL, 'Turkmenistan', 'TM', '7370', '0', NULL, NULL),
(225, NULL, NULL, 'Turks And Caicos Islands', 'TC', '1649', '0', NULL, NULL),
(226, NULL, NULL, 'Tuvalu', 'TV', '688', '0', NULL, NULL),
(227, NULL, NULL, 'Uganda', 'UG', '256', '0', NULL, NULL),
(228, NULL, NULL, 'Ukraine', 'UA', '380', '0', NULL, NULL),
(229, NULL, NULL, 'United Arab Emirates', 'AE', '971', '0', NULL, NULL),
(230, NULL, NULL, 'United Kingdom', 'GB', '44', '0', NULL, NULL),
(231, NULL, NULL, 'United States', 'US', '1', '0', NULL, NULL),
(232, NULL, NULL, 'United States Minor Outlying Islands', 'UM', '1', '0', NULL, NULL),
(233, NULL, NULL, 'Uruguay', 'UY', '598', '0', NULL, NULL),
(234, NULL, NULL, 'Uzbekistan', 'UZ', '998', '0', NULL, NULL),
(235, NULL, NULL, 'Vanuatu', 'VU', '678', '0', NULL, NULL),
(236, NULL, NULL, 'Vatican City State (Holy See)', 'VA', '39', '0', NULL, NULL),
(237, NULL, NULL, 'Venezuela', 'VE', '58', '0', NULL, NULL),
(238, NULL, NULL, 'Vietnam', 'VN', '84', '0', NULL, NULL),
(239, NULL, NULL, 'Virgin Islands (British)', 'VG', '1284', '0', NULL, NULL),
(240, NULL, NULL, 'Virgin Islands (US)', 'VI', '1340', '0', NULL, NULL),
(241, NULL, NULL, 'Wallis And Futuna Islands', 'WF', '681', '0', NULL, NULL),
(242, NULL, NULL, 'Western Sahara', 'EH', '212', '0', NULL, NULL),
(243, NULL, NULL, 'Yemen', 'YE', '967', '0', NULL, NULL),
(244, NULL, NULL, 'Yugoslavia', 'YU', '38', '0', NULL, NULL),
(245, NULL, NULL, 'Zambia', 'ZM', '260', '0', NULL, NULL),
(246, NULL, NULL, 'Zimbabwe', 'ZW', '26', '0', NULL, NULL),
(247, NULL, NULL, 'Catalonia', 'CT', '34', '0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `credit_logs`
--

CREATE TABLE `credit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `trx_code` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `type` enum('+','-') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` mediumint NOT NULL DEFAULT '0',
  `post_balance` mediumint NOT NULL DEFAULT '0',
  `remarks` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_rate` double(20,5) NOT NULL DEFAULT '0.00000',
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `default` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `base` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Active: 1, Inactive: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `uid`, `created_by`, `updated_by`, `name`, `code`, `symbol`, `exchange_rate`, `status`, `default`, `base`, `created_at`, `updated_at`) VALUES
(1, '559361b5-3ef8-4dd9-ac53-47fce19a687a', NULL, NULL, 'Us Dollar', 'USD', '$', 1.00000, '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint UNSIGNED NOT NULL,
  `fileable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `firewall_ips`
--

CREATE TABLE `firewall_ips` (
  `id` int UNSIGNED NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_id` int DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `firewall_logs`
--

CREATE TABLE `firewall_logs` (
  `id` int UNSIGNED NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `middleware` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `url` text COLLATE utf8mb4_unicode_ci,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `frontends`
--

CREATE TABLE `frontends` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `frontends`
--

INSERT INTO `frontends` (`id`, `uid`, `parent_id`, `updated_by`, `key`, `value`, `status`, `created_at`, `updated_at`) VALUES
(1, '89a906a2-51af-4a28-9d89-651634df1476', NULL, NULL, 'content_contact_us', '{\"support_title\":\"Are you an existing customer?\",\"support_description\":\"If so, please click the button on the right to open a support ticket.\",\"button_name\":\"Open Support Ticket\",\"button_url\":\"@@@\",\"section_title\":\"Contact us\",\"section_heading\":\"Growth is the only constant in our\",\"section_description\":\"We welcome all the questions & remarks. Growth is the only constant in our\",\"breadcrumb_title\":\"Get In Touch With us\",\"opening_hour_text\":\"08:00 - 17:00\",\"breadcrumb_description\":\"Our 24\\/7 support experts are here to assist you through tough times, so you get back to building exciting projects\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, '085fa875-e1a0-422f-8b28-2088a2b7cd15', NULL, NULL, 'content_feedback', '{\"breadcrumb_title\":\"Get In Touch With us\",\"breadcrumb_description\":\"Our 24\\/7 support experts are here to assist you through tough times, so you get back to building exciting projects\",\"heading\":\"We\'d love hear from you\",\"description\":\"We welcome all the questions & remarks. Growth is the only constant in our\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, 'df4daf04-941c-4b33-9b2c-efbd9e7d1998', NULL, NULL, 'content_cta', '{\"title\":\"Ready to get those mind-blowing ideas?\",\"description\":\"Track the engagement rate, comments, likes, shares, and impressions for each post, so you know whats working best for your audience. Once youve identified your high-performing posts, you can share them again.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, '8806a4ab-f9df-495d-a562-10fc539a1fdc', NULL, NULL, 'element_cta', '{\"button_name\":\"Get Started\",\"url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(5, 'a094b218-682e-4fc7-bf13-08630c6d2807', NULL, NULL, 'element_cta', '{\"button_name\":\"Contact Us\",\"url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(6, 'f57c87ab-e761-463f-bd24-c127b982c5d5', NULL, NULL, 'content_cookie', '{\"description\":\"We use cookies to enhance your browsing experience. By clicking \'Accept all, you agree to the use of cookies.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(7, '4fa0725c-ff7b-491e-a0c6-e2d62baaf828', NULL, NULL, 'content_banner', '{\"title\":\"Social <span>Media<\\/span> 10x Faster <br> With AI <span>\",\"description\":\"Our all-in-one social media management platform unlocks the full potential of social to transform not just your marketing strategy\\u2014but every area of your organization.\",\"button_name\":\"Discover more\",\"button_icon\":\"bi bi-arrow-up-right-circle\",\"button_URL\":\"@@\",\"video_URL\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(8, 'c0c2820d-6bf5-47e2-bcc5-0213dc74c886', NULL, NULL, 'element_banner', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(9, 'd39aab95-de63-4fc0-bdee-ee01786fecd6', NULL, NULL, 'element_banner', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(10, '49622505-608a-48fc-aa62-7b96f9de7294', NULL, NULL, 'element_banner', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(11, 'fc6c91e1-3054-461c-b864-4f7575bcc67f', NULL, NULL, 'element_banner', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(12, 'a370deb2-4fcc-44ef-9a8d-91f09b07ddba', NULL, NULL, 'element_banner', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(13, '69109484-9b45-429b-b3c6-0a440a8210da', NULL, NULL, 'content_about', '{\"title\":\"Our Values\",\"sub_title\":\"About us\",\"description\":\"Discover the power of our secure an rewarding credit cards. Discover th power of our secure and rewarding credit cards. Discover the power of our secure an rewarding credit cards. Discover th power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(14, '0d7a73a6-ef62-4980-ae95-04c386afb95d', NULL, NULL, 'element_about', '{\"icon\":\"bi bi-heart\",\"title\":\"Takeover\",\"description\":\"Discover the power of our secure an rewarding credit cards. Discover th power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(15, '4b208804-6072-4de1-aaf1-a65f066fabfd', NULL, NULL, 'element_about', '{\"icon\":\"bi bi-heart\",\"title\":\"Takeover\",\"description\":\"Discover the power of our secure an rewarding credit cards. Discover th power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(16, '9aedc469-11d0-44b6-919d-12ab7cbd4da7', NULL, NULL, 'element_about', '{\"icon\":\"bi bi-heart\",\"title\":\"Takeover\",\"description\":\"Discover the power of our secure an rewarding credit cards. Discover th power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(17, 'da76f28b-8e61-4f00-8c9c-6de2882435f6', NULL, NULL, 'element_about', '{\"icon\":\"bi bi-heart\",\"title\":\"Trustworthy\",\"description\":\"Discover the power of our secure an rewarding credit cards. Discover th power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(18, 'e69b27bc-a3a3-41e9-b21d-b9e7a6739dbc', NULL, NULL, 'element_about_counter', '{\"counter_value\":\"01\",\"counter_text\":\"300+Our Customers\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(19, 'c12e8a38-a0b2-4022-918c-931605e2c53f', NULL, NULL, 'element_about_counter', '{\"counter_value\":\"01\",\"counter_text\":\"300+Our Customers\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(20, 'ad3c5567-d3f2-49e6-869e-09b9137bc5c9', NULL, NULL, 'content_integration', '{\"title\":\"Our Intregration\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(21, '5a2aa22c-ea7b-4246-8a4a-1f76f2538f11', NULL, NULL, 'element_integration', '{\"title\":\"Linkedin\",\"short_description\":\"Excited to introduce our latest innovation! Discover the future of Linkedin\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(22, '4d267bdd-9bc4-405f-b9df-46991a088b3e', NULL, NULL, 'element_integration', '{\"title\":\"Twitter\",\"short_description\":\"Excited to introduce our latest innovation! Discover the future of Linkedin\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(23, '6ac6e8f5-d56d-40b9-93b8-e814e3c35240', NULL, NULL, 'element_integration', '{\"title\":\"Instragram\",\"short_description\":\"Excited to introduce our latest innovation! Discover the future of Linkedin\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(24, '1065dd3c-3b4c-4b44-a977-acc615944f62', NULL, NULL, 'element_integration', '{\"title\":\"Facebook\",\"short_description\":\"Excited to introduce our latest innovation! Discover the future of Linkedin\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(25, 'f42c9a9f-6496-4af4-874c-31e6f89d9242', NULL, NULL, 'content_feature', '{\"title\":\"Transforming Social With <span>Wealth Management<\\/span>\",\"sub_title\":\"Key Features\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(26, 'a750f12b-e439-4e6b-ad41-01991e8dec91', NULL, NULL, 'element_feature', '{\"title\":\"Social Media Calendar\",\"description\":\"Discover the power of our secure and rewarding credit cards. Discover the power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(27, '90cb209b-405d-4d91-9aa2-bbf892a3f3d8', NULL, NULL, 'element_feature', '{\"title\":\"Bulk Scheduling\",\"description\":\"Discover the power of our secure and rewarding credit cards. Discover the power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(28, '35e13ec5-3555-4619-85dd-7a7ca06dc4fe', NULL, NULL, 'element_feature', '{\"title\":\"AI Assistant\",\"description\":\"Discover the power of our secure and rewarding credit cards. Discover the power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(29, 'dc1893cd-9c4c-4d72-a8fb-4ac6492aa53c', NULL, NULL, 'element_feature', '{\"title\":\"Engagement\",\"description\":\"Discover the power of our secure and rewarding credit cards. Discover the power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(30, 'd1507185-d521-4fde-aa02-1a7fd42bb816', NULL, NULL, 'content_powerful_feature', '{\"title\":\"The best features for you\",\"sub_title\":\"Powerful features\",\"description\":\"Unlock the power of Social posting Enhance your experience and enjoy seamless [benefit]. Try it now and see the difference.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(31, '0e80edc4-782e-420d-8f0f-8e9ddc022103', NULL, NULL, 'element_powerful_feature', '{\"title\":\"Optimization Engine Rank\",\"description\":\"Discover our powerful features designed to elevate your experience. From cutting-edge technology to user-friendly interfaces, our features are crafted to provide maximum efficiency and unparalleled performance. Experience the difference today.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(32, '6fb0dcd1-47fd-4a19-8fef-a127316b8d50', NULL, NULL, 'element_powerful_feature', '{\"title\":\"Optimization Engine Rank\",\"description\":\"Discover our powerful features designed to elevate your experience. From cutting-edge technology to user-friendly interfaces, our features are crafted to provide maximum efficiency and unparalleled performance. Experience the difference today.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(33, 'e3a5df7b-123a-4803-9160-708ef749a038', NULL, NULL, 'element_powerful_feature', '{\"title\":\"Optimization Engine Rank\",\"description\":\"Discover our powerful features designed to elevate your experience. From cutting-edge technology to user-friendly interfaces, our features are crafted to provide maximum efficiency and unparalleled performance. Experience the difference today.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(34, '42460abf-a7a1-4531-9981-ea6e3ea528cf', NULL, NULL, 'element_powerful_feature', '{\"title\":\"Optimization Engine Rank\",\"description\":\"Discover our powerful features designed to elevate your experience. From cutting-edge technology to user-friendly interfaces, our features are crafted to provide maximum efficiency and unparalleled performance. Experience the difference today.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(35, '064e8d0d-5146-48dc-bb4e-936a1e014410', NULL, NULL, 'element_powerful_feature', '{\"title\":\"Optimization Engine Rank\",\"description\":\"Discover our powerful features designed to elevate your experience. From cutting-edge technology to user-friendly interfaces, our features are crafted to provide maximum efficiency and unparalleled performance. Experience the difference today.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(36, '9174bd6d-ce23-4e4d-a171-eaa5b6de35d0', NULL, NULL, 'content_service', '{\"title\":\"Empowering social media <span>insights<\\/span>\",\"sub_title\":\"Service\",\"description\":\"Discover the power of our secure and rewarding credit cards.\",\"section_top_title\":\"Unlock the power of social media <span>insights<\\/span> to drive your strategy forward. \",\"section_bottom_title\":\"Discover the game-changing impact of social media  <span>insights<\\/span> for your business growth\",\"section_top_description\":\"Discover the power of our secure and rewarding credit cards.\",\"section_bottom_description\":\"Discover the power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(37, '800c95b2-ea1c-4634-8685-a620041fc830', NULL, NULL, 'element_service', '{\"title\":\"Social Media Monitor\",\"description\":\"Social Media Monitor\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(38, '4962c27a-f11f-4267-a678-7d0e3bf74463', NULL, NULL, 'element_service', '{\"title\":\"Analytical Reports\",\"description\":\"Analytical Reports\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(39, 'e74dde70-01a6-42b7-8769-a109dba02bb9', NULL, NULL, 'element_service', '{\"title\":\"Template Management\",\"description\":\"Template Management\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(40, 'afc37363-824b-4798-8c9f-e8093c6426c7', NULL, NULL, 'element_service', '{\"title\":\"Feed Analytic\",\"description\":\"Feed Analytic\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(41, '0fe3dd9c-73f8-4589-844c-9eec6252fded', NULL, NULL, 'element_service', '{\"title\":\"AI Content Create\",\"description\":\"AI Content Create\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(42, '0fb3360c-7a64-4d39-be2f-8a7b9ecce644', NULL, NULL, 'element_service', '{\"title\":\"Feed Analytic\",\"description\":\"Feed Analytic\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(43, 'e8d49e37-e75f-47b8-9d8e-f785416c06a7', NULL, NULL, 'element_service', '{\"title\":\"Manage profile\",\"description\":\"Manage profile\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(44, '91b6f3f3-ffd7-4500-8442-002a239191b6', NULL, NULL, 'element_service', '{\"title\":\"Manage Post\",\"description\":\"Manage Post\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(45, 'a1fd7c69-079c-4ed3-87f5-bee4ea60c4f2', NULL, NULL, 'content_service_insight', '{\"title\":\"Empowering social media <span>insights<\\/span>\",\"description\":\"Discover the power of our secure and rewarding credit cards.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(46, 'a5bab603-c28a-4043-82c7-4388c5f6c1c4', NULL, NULL, 'element_service_insight', '{\"title\":\"Design visually appealing content for all your feeds\",\"sub_title\":\"Manage Accounts\",\"description\":\"Take advantage of the in-app integrations with platforms like Canva, Unsplash, and GIPHY. Boost your creative abilities and get access to a wide variety of design elements.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(47, '00a904ab-3b2f-4797-9755-cedca2a0d642', NULL, NULL, 'element_service_insight', '{\"title\":\"Design visually appealing content for all your feeds\",\"sub_title\":\"AI Content\",\"description\":\"Take advantage of the in-app integrations with platforms like Canva, Unsplash, and GIPHY. Boost your creative abilities and get access to a wide variety of design elements.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(48, '5ed7ae84-fd7b-4451-ab89-e0ddaa208ee5', NULL, NULL, 'element_service_insight', '{\"title\":\"Design visually appealing content for all your feeds\",\"sub_title\":\"Create post\",\"description\":\"Take advantage of the in-app integrations with platforms like Canva, Unsplash, and GIPHY. Boost your creative abilities and get access to a wide variety of design elements.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(49, '375ff5af-c412-4f47-90c8-bb89d560deb6', NULL, NULL, 'element_service_insight', '{\"title\":\"Design visually appealing content for all your feeds\",\"sub_title\":\"Content\",\"description\":\"Take advantage of the in-app integrations with platforms like Canva, Unsplash, and GIPHY. Boost your creative abilities and get access to a wide variety of design elements.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(50, 'b8bca405-bf16-4b9a-89e4-8b71329352e5', NULL, NULL, 'element_service_insight', '{\"title\":\"Design visually appealing content for all your feeds\",\"sub_title\":\"Insight\",\"description\":\"Take advantage of the in-app integrations with platforms like Canva, Unsplash, and GIPHY. Boost your creative abilities and get access to a wide variety of design elements.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(51, '293df24e-acb6-4806-abb0-4e7284ef0751', NULL, NULL, 'content_team', '{\"title\":\"Meet our <span>team<\\/span>\",\"sub_title\":\"Team\",\"description\":\"Meet our dedicated team of professionals, committed to delivering excellence and innovation. With diverse expertise and a shared passion for success, we work together to achieve our goals and drive our mission forward.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(52, '7cafd104-9eba-499e-b621-ec769d0951ef', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(53, 'a1b902b4-1516-4a54-bbdc-d53571530429', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(54, 'df256cc3-d9db-4f12-9b75-c66371add2f0', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(55, '7e00654c-717d-488e-8cbe-48edd0e9f2e3', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(56, 'b4771bfe-6544-45a5-8203-d79f455e60d1', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(57, 'e4d53234-e648-4484-88a6-8d29d1e4af10', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(58, '7e1b2321-0301-4ae2-934a-557379f2abc3', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(59, '6bb33561-8435-47ba-a57d-bab737f5b195', NULL, NULL, 'element_team', '[]', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(60, 'a8461b4b-abb0-4815-847b-bb80bb314246', NULL, NULL, 'content_template', '{\"title\":\"AI powered social media <span>template<\\/span>\",\"sub_title\":\"Templates\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(61, 'b53e63dc-7707-4f85-806c-6fb0a2950965', NULL, NULL, 'content_why_us', '{\"title\":\"Watch Your Accounts Grow\",\"sub_title\":\"Why Feedswiz\",\"button_name\":\"View More\",\"button_url\":\"@@\",\"description\":\"Track the engagement rate, comments, likes, shares, and impressions for each post, so you know what\\u2019s working best for your audience. Once you\\u2019ve identified your high-performing posts, you can share them again.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(62, '033a36f3-f9ad-4e4e-bd5a-a0ab205cc52d', NULL, NULL, 'element_why_us', '{\"icon\":\"bi bi-magic\",\"title\":\"AI Content Generation\",\"description\":\"Generate captions and images based on prompts, summarize complex content, and turn your product descriptions into highly converting posts.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(63, '43e20d72-2ab8-4387-9551-5d2ac8166ec7', NULL, NULL, 'element_why_us', '{\"icon\":\"bi bi-magic\",\"title\":\"AI Content Generation\",\"description\":\"Generate captions and images based on prompts, summarize complex content, and turn your product descriptions into highly converting posts.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(64, '2fa495a7-59c4-4670-994b-1428b0c0826b', NULL, NULL, 'element_why_us', '{\"icon\":\"bi bi-magic\",\"title\":\"AI Content Generation\",\"description\":\"Generate captions and images based on prompts, summarize complex content, and turn your product descriptions into highly converting posts.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(65, 'd5e83ca6-1393-49f4-b070-96ae523d8fda', NULL, NULL, 'element_why_us', '{\"icon\":\"bi bi-magic\",\"title\":\"AI Content Generation\",\"description\":\"Generate captions and images based on prompts, summarize complex content, and turn your product descriptions into highly converting posts.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(66, '5071b60a-21b9-4054-8d3f-e94522f9a04e', NULL, NULL, 'element_why_us', '{\"icon\":\"bi bi-magic\",\"title\":\"AI Content Generation\",\"description\":\"Generate captions and images based on prompts, summarize complex content, and turn your product descriptions into highly converting posts.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(67, '3e1c6ba4-d83c-4434-8ed5-12a4e0e01dea', NULL, NULL, 'element_why_us', '{\"icon\":\"bi bi-magic\",\"title\":\"AI Content Generation\",\"description\":\"Generate captions and images based on prompts, summarize complex content, and turn your product descriptions into highly converting posts.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(68, '063b2e16-1f86-4283-b96f-4a3148e2b6a8', NULL, NULL, 'content_faq', '{\"title\":\"Frequently ask <span>questions<\\/span>\",\"sub_title\":\"FAQS\",\"description\":\"We cant wait for you to explore all of our stories and create your own learning journeys. Before you do, here are the questions we get asked the most by our visitors.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(69, '6bc7c80f-a62c-403e-a93a-b3bdaa6ef942', NULL, NULL, 'element_faq', '{\"question\":\"Whats it like your job, grab a backpack, and travel the\\n                        world?\",\"answer\":\"Some of the strangest places on earth are also the most\\n                        sublime: from the UFO-like dragon\'s blood trees in Yemen\\n                        to a rainbow-colored hot spring in Yellowstone to a bridge\\n                        in Germany that looks like a leftover prop from Lord of\\n                        the Rings.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(70, '7362e01a-985e-4171-bccc-1095faea4af5', NULL, NULL, 'element_faq', '{\"question\":\"If I visit your country, whats\\n                        the one meal I shouldnt miss?\",\"answer\":\"Morbi aliquam quis quam in luctus. Nullam tincidunt\\n                        pulvinar imperdiet. Sed varius, diam vitae posuere semper,\\n                        libero ex hendrerit nunc, ac sagittis eros metus ut diam.\\n                        Donec a nibh in libero maximus vehicula. Etiam sit amet\\n                        condimentum erat. Pellentesque ultrices sagittis turpis,\\n                        quis tempus ante viverra et.Morbi aliquam quis quam in\\n                        luctus. Nullam tincidunt pulvinar imperdiet. Sed varius,\\n                        diam vitae posuere semper, tincidunt pulvinar imperdiet.\\n                        Sed varius, diam vitae posuere semper.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(71, 'c1877cb6-608b-4310-b3dd-3e75e9771a7d', NULL, NULL, 'element_faq', '{\"question\":\"What are the most beautiful beaches in the world?\",\"answer\":\"Morbi aliquam quis quam in luctus. Nullam tincidunt\\n                        pulvinar imperdiet. Sed varius, diam vitae posuere semper,\\n                        libero ex hendrerit nunc, ac sagittis eros metus ut diam.\\n                        Donec a nibh in libero maximus vehicula. Etiam sit amet\\n                        condimentum erat. Pellentesque ultrices sagittis turpis,\\n                        quis tempus ante viverra et.Morbi aliquam quis quam in\\n                        luctus. Nullam tincidunt pulvinar imperdiet. Sed varius,\\n                        diam vitae posuere semper, tincidunt pulvinar imperdiet.\\n                        Sed varius, diam vitae posuere semper.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(72, 'e81a4907-0ecd-49a1-9566-18e01288491a', NULL, NULL, 'element_faq', '{\"question\":\"Who s the most interesting person you\\u2019ve ever met on a\\n                        plane\",\"answer\":\"Morbi aliquam quis quam in luctus. Nullam tincidunt\\n                        pulvinar imperdiet. Sed varius, diam vitae posuere semper,\\n                        libero ex hendrerit nunc, ac sagittis eros metus ut diam.\\n                        Donec a nibh in libero maximus vehicula. Etiam sit amet\\n                        condimentum erat. Pellentesque ultrices sagittis turpis,\\n                        quis tempus ante viverra et.Morbi aliquam quis quam in\\n                        luctus. Nullam tincidunt pulvinar imperdiet. Sed varius,\\n                        diam vitae posuere semper, tincidunt pulvinar imperdiet.\\n                        Sed varius, diam vitae posuere semper.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(73, '9d086ce9-c879-4ce8-935e-22abc16b037c', NULL, NULL, 'content_plan', '{\"title\":\"Life Planning, Making Easy to Turn r <span>Dreams <\\/span> a Reality\",\"sub_title\":\"Pricing Plan\",\"button_name\":\"View All\",\"button_URL\":\"plans\",\"description\":\"We offer flexible pricing plans to suit the diverse needs of our clients.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(74, '8b1517a7-5dc3-4b24-898d-e7de6b2639e6', NULL, NULL, 'content_testimonial', '{\"title\":\"What our <span>Clients<\\/span> say.\",\"sub_title\":\"Reviews\",\"description\":\"Track the engagement rate, comments, likes, shares, and impressions for each post, so you know what\\u2019s working best for your audience\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(75, 'ea9f6089-1b22-4e3e-bd21-fa1a3b845596', NULL, NULL, 'element_testimonial', '{\"author\":\"Sam Wister\",\"designation\":\"Social media manager\",\"quote\":\"I recently got the XYZ Pro, and it\'s been a game-changer. The performance is top-notch\\u2014apps run smoothly, and multitasking is a breeze. The sleek design is a head-turner, and the camera captures stunning shots, even in low light. The battery easily lasts a day, and fast charging is a great perk. My only minor gripe is the fingerprint sensor placement. Overall, a fantastic investment for tech enthusiasts!\",\"rating\":3}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(76, '74c9a0bd-b04d-4705-9bc8-34ea0349ebc4', NULL, NULL, 'element_testimonial', '{\"author\":\"Charles Lucas\",\"designation\":\"Social media manager\",\"quote\":\"I recently got the XYZ Pro, and it\'s been a game-changer. The performance is top-notch\\u2014apps run smoothly, and multitasking is a breeze. The sleek design is a head-turner, and the camera captures stunning shots, even in low light. The battery easily lasts a day, and fast charging is a great perk. My only minor gripe is the fingerprint sensor placement. Overall, a fantastic investment for tech enthusiasts!\",\"rating\":4}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(77, 'a6504516-ac77-46d2-b41c-7125c79ab14a', NULL, NULL, 'element_testimonial', '{\"author\":\"Winstar\",\"designation\":\"Manager\",\"quote\":\"I recently got the XYZ Pro, and it\'s been a game-changer. The performance is top-notch\\u2014apps run smoothly, and multitasking is a breeze. The sleek design is a head-turner, and the camera captures stunning shots, even in low light. The battery easily lasts a day, and fast charging is a great perk. My only minor gripe is the fingerprint sensor placement. Overall, a fantastic investment for tech enthusiasts!\",\"rating\":2}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(78, 'f0415570-32ca-40c9-9d91-dac866f71356', NULL, NULL, 'element_testimonial', '{\"author\":\"Mac foster\",\"designation\":\"CEO\",\"quote\":\"I recently got the XYZ Pro, and it\'s been a game-changer. The performance is top-notch\\u2014apps run smoothly, and multitasking is a breeze. The sleek design is a head-turner, and the camera captures stunning shots, even in low light. The battery easily lasts a day, and fast charging is a great perk. My only minor gripe is the fingerprint sensor placement. Overall, a fantastic investment for tech enthusiasts!\",\"rating\":5}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(79, '89e73bcf-fcad-40e6-8a22-45023588d589', NULL, NULL, 'element_testimonial', '{\"author\":\"Sam Wister\",\"designation\":\"Social media manager\",\"quote\":\"I recently got the XYZ Pro, and it\'s been a game-changer. The performance is top-notch\\u2014apps run smoothly, and multitasking is a breeze. The sleek design is a head-turner, and the camera captures stunning shots, even in low light. The battery easily lasts a day, and fast charging is a great perk. My only minor gripe is the fingerprint sensor placement. Overall, a fantastic investment for tech enthusiasts!\",\"rating\":1}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(80, 'd86e0e8b-e589-45fc-8019-699aad0b4ad4', NULL, NULL, 'content_blog', '{\"title\":\"News & <span>Blogs<\\/span>\",\"sub_title\":\"Blogs\",\"button_name\":\"View More\",\"button_URL\":\"blogs\",\"description\":\"Track the engagement rate, comments, likes, shares, and impressions for each post, so you know what\\u2019s working best for your audience. Once you\\u2019ve identified your high-performing posts, you can share them again.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(81, 'ab14607f-417b-4b5d-9295-ed9d13b52f0e', NULL, NULL, 'element_social_icon', '{\"icon\":\"bi bi-facebook\",\"button_url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(82, '5561380b-23ed-4387-bcd5-0943851eb62f', NULL, NULL, 'element_social_icon', '{\"icon\":\"bi bi-linkedin\",\"button_url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(83, '0e433fa3-7df0-40af-97e0-f01804b0dade', NULL, NULL, 'element_social_icon', '{\"icon\":\"bi bi-instagram\",\"button_url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(84, 'e8ec73ee-77f8-424b-bd93-e955c0e32ef8', NULL, NULL, 'element_social_icon', '{\"icon\":\"bi bi-twitter\",\"button_url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(85, 'c8784a98-1d3a-4333-ad79-fa224005d534', NULL, NULL, 'element_social_icon', '{\"icon\":\"bi bi-youtube\",\"button_url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(86, '4a2fa93b-32ba-4178-9b16-fdb1db67b040', NULL, NULL, 'element_social_icon', '{\"icon\":\"bi bi-tiktok\",\"button_url\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(87, '03bad2ca-54ba-40bd-9309-62d2aadceb6d', NULL, NULL, 'content_footer', '{\"title\":\"Improve your social media content\",\"description\":\"Lorem ipsum dolor sit amet consectetur adipiscing elit dolor posuere vel venenatis eu sit massa volutpat\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(88, '41108d8f-06f9-4a92-b6f1-aff27859512e', NULL, NULL, 'element_footer', '{\"button_name\":\"Book a demo\",\"button_URL\":\"@@\",\"button_icon\":\"bi bi-arrow-up-right\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(89, '207398c8-d74b-4ae8-8551-ef08260e7801', NULL, NULL, 'element_footer', '{\"button_name\":\"Get Started Free\",\"button_URL\":\"@@\",\"button_icon\":\"bi bi-arrow-up-right\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(90, '46d6c2fc-43f9-4488-b535-f760235aa8c7', NULL, NULL, 'content_mega_menu', '{\"select_input\":{\"status\":\"1\"},\"title\":\"Intregration\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(91, 'a3456a4f-28d6-4454-949f-9f0845d99035', NULL, NULL, 'content_authentication_section', '{\"description\":\"Uncover the untapped potential of your growth to connect with clients.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(92, 'd38e3b44-158e-4c8f-b2b7-59a6d4f13425', NULL, NULL, 'element_authentication_section', '{\"title\":\"Easy to use dashboard\",\"description\":\"Choose the best of product\\/service and get a\\n                        bare metal server at the lowest prices.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(93, '5898e650-439d-4e3b-8b2a-b51db87ecbb6', NULL, NULL, 'element_authentication_section', '{\"title\":\"Easy to use dashboard\",\"description\":\"Choose the best of product\\/service and get a\\n                        bare metal server at the lowest prices.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(94, '93a8688c-bb43-4791-a546-8a061dad25d5', NULL, NULL, 'element_authentication_section', '{\"title\":\"Easy to use dashboard\",\"description\":\"Choose the best of product\\/service and get a\\n                        bare metal server at the lowest prices.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(95, '02da33c0-1fac-48b9-9188-a73dc2567ba4', NULL, NULL, 'element_authentication_section', '{\"title\":\"Easy to use dashboard\",\"description\":\"Choose the best of product\\/service and get a\\n                        bare metal server at the lowest prices.\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_logs`
--

CREATE TABLE `kyc_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `kyc_data` longtext COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Approved: 1, Requested: 2, Hold: 3 , Rejected: 3',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Yes: 1, No: 0',
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `ltr` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Yes: 1, No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `uid`, `created_by`, `updated_by`, `name`, `code`, `is_default`, `status`, `ltr`, `created_at`, `updated_at`) VALUES
(1, '5c469500-2607-4341-91e3-d423b3e2c1f9', NULL, NULL, 'English', 'en', '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `mail_gateways`
--

CREATE TABLE `mail_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credential` longtext COLLATE utf8mb4_unicode_ci,
  `default` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_gateways`
--

INSERT INTO `mail_gateways` (`id`, `uid`, `created_by`, `updated_by`, `code`, `name`, `credential`, `default`, `created_at`, `updated_at`) VALUES
(1, '255ffa81-c17d-4217-9735-ddd12f2ad303', NULL, NULL, '101SMTP', 'smtp', '{\"driver\":\"@@\",\"host\":\"@@\",\"port\":\"@@\",\"encryption\":\"@@\",\"username\":\"@@\",\"password\":\"@@\",\"from\":{\"address\":\"@@\",\"name\":\"@@\"}}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, '921fe768-e987-494e-945f-7d2069fd885b', NULL, NULL, '104PHP', 'phpmail', '[]', '0', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, 'c959f72a-50a3-4981-b1a0-d84a345bd3f3', NULL, NULL, '102SENDGRID', 'sendgrid', '{\"app_key\":\"@@\",\"from\":{\"address\":\"@@\",\"name\":\"@@\"}}', '0', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `media_platforms`
--

CREATE TABLE `media_platforms` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(155) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(155) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `configuration` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `is_feature` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `is_integrated` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_platforms`
--

INSERT INTO `media_platforms` (`id`, `uid`, `name`, `slug`, `url`, `description`, `configuration`, `status`, `is_feature`, `is_integrated`, `created_at`, `updated_at`) VALUES
(1, '71c67c4c-752c-40bb-b7bb-ee48a9351d77', 'Facebook', 'facebook', '@@', '@@', '{\"client_id\":\"@@\",\"client_secret\":\"@@\",\"app_version\":\"@@\",\"graph_api_url\":\"@@\",\"group_url\":\"https:\\/\\/www.facebook.com\\/groups\"}', '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, '68bf8cdf-961f-41e0-9bee-f43e9132e8cb', 'Instagram', 'instagram', '@@', '@@', '{\"client_id\":\"@@\",\"client_secret\":\"@@\",\"app_version\":\"@@\",\"graph_api_url\":\"@@\"}', '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, 'cc27d44c-fb41-4ca8-9da2-0336ad398471', 'Twitter', 'twitter', '@@', '@@', '{\"api_key\":\"-\",\"api_secret\":\"-\",\"access_token\":\"-\",\"access_token_secret\":\"-\",\"client_id\":\"@@\",\"client_secret\":\"@@\",\"app_version\":\"@@\"}', '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, 'fcd0945c-c168-4557-80b5-bf9713d47c9d', 'Linkedin', 'linkedin', '@@', '@@', '{\"client_id\":\"@@\",\"client_secret\":\"@@\"}', '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(5, '5a366949-f408-4850-bb88-464bcc5118d9', 'tikTok', 'tiktok', '@@', '@@', '{\"client_key\":\"@@\",\"client_secret\":\"@@\",\"app_version\":\"@@\"}', '1', '0', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(6, 'de46b35c-1095-4f1b-a055-309e98083694', 'youtube', 'youtube', '@@', '@@', '{\"client_id\":\"@@\",\"client_secret\":\"@@\",\"app_version\":\"@@\"}', '1', '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_id` int DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section` longtext COLLATE utf8mb4_unicode_ci,
  `menu_visibility` enum('0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT 'Header: 0, Footer: 1, Both: 2',
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1,Inactive: 0',
  `is_default` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1,No: 0',
  `meta_title` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `uid`, `serial_id`, `created_by`, `updated_by`, `name`, `url`, `section`, `menu_visibility`, `status`, `is_default`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 'a72e3279-2080-432a-bb9b-c33d2d58fe94', 0, NULL, NULL, 'Home', '/', '[\"about\",\"feature\",\"powerful_feature\",\"service\",\"template\",\"why_us\",\"faq\",\"team\",\"plan\",\"testimonial\",\"blog\"]', '2', '1', '1', 'Home', NULL, NULL, '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, '1839411f-0586-4bb8-9313-cf08988f859a', 1, NULL, NULL, 'Contact', 'contact', '[]', '2', '1', '0', 'Contact', NULL, NULL, '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, '6eb6d229-3368-4598-bdac-2789e5b7f220', 2, NULL, NULL, 'Blogs', 'blogs', '[]', '2', '1', '0', 'Blogs', NULL, NULL, '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, '953e6d81-a24c-48f7-9785-aa369bd0c593', 3, NULL, NULL, 'Plans', 'plans', '[]', '2', '1', '0', 'Plans', NULL, NULL, '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `ticket_id` bigint UNSIGNED DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_07_15_000000_create_firewall_ips_table', 1),
(4, '2019_07_15_000000_create_firewall_logs_table', 1),
(5, '2019_08_19_000000_create_failed_jobs_table', 1),
(6, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(7, '2023_06_11_101656_create_admins_table', 1),
(8, '2023_06_12_045319_create_languages_table', 1),
(9, '2023_06_12_045405_create_translations_table', 1),
(10, '2023_06_12_063444_create_settings_table', 1),
(11, '2023_06_15_053643_create_roles_table', 1),
(12, '2023_06_25_110314_create_files_table', 1),
(13, '2023_06_26_223207_create_templates_table', 1),
(14, '2023_06_26_223307_create_payment_methods_table', 1),
(15, '2023_06_26_223423_create_pages_table', 1),
(16, '2023_06_26_223509_create_menus_table', 1),
(17, '2023_06_26_223616_create_contacts_table', 1),
(18, '2023_06_26_223703_create_subscribers_table', 1),
(19, '2023_07_10_161535_create_sms_gateways_table', 1),
(20, '2023_07_10_161751_create_mail_gateways_table', 1),
(21, '2023_07_12_111808_create_otps_table', 1),
(22, '2023_07_14_171648_create_categories_table', 1),
(23, '2023_07_14_200955_create_articles_table', 1),
(24, '2023_07_16_191644_create_visitors_table', 1),
(25, '2023_07_17_120854_create_frontends_table', 1),
(26, '2023_07_24_223152_create_packages_table', 1),
(27, '2023_07_27_144628_create_payment_logs_table', 1),
(28, '2023_07_27_144715_create_subscriptions_table', 1),
(29, '2023_07_27_144734_create_transactions_table', 1),
(30, '2023_07_28_185417_create_jobs_table', 1),
(31, '2023_08_01_130142_create_tickets_table', 1),
(32, '2023_08_01_130712_create_messages_table', 1),
(33, '2023_08_06_152247_create_notifications_table', 1),
(34, '2023_08_06_172548_create_withdraws_table', 1),
(35, '2023_08_24_145721_create_currencies_table', 1),
(36, '2023_10_25_151522_create_model_translations_table', 1),
(37, '2023_11_09_125419_create_countries_table', 1),
(38, '2023_11_09_131956_create_kyc_logs_table', 1),
(39, '2023_11_19_123930_create_ai_templates_table', 1),
(40, '2023_11_20_123659_create_template_usages_table', 1),
(41, '2023_11_22_124846_create_media_platforms_table', 1),
(42, '2023_11_25_131356_create_withdraw_logs_table', 1),
(43, '2023_11_27_125146_create_credit_logs_table', 1),
(44, '2023_11_30_230046_create_affiliate_logs_table', 1),
(45, '2023_12_12_182514_create_social_accounts_table', 1),
(46, '2023_12_12_183444_create_social_posts_table', 1),
(47, '2023_12_12_183749_create_post_webhook_logs_table', 1),
(48, '2023_12_14_122410_create_contents_table', 1),
(49, '2025_05_14_033218_update_ai_module_type_in_categories', 1),
(50, '2025_05_14_035440_update_type_column_in_contents', 1),
(51, '2025_05_25_024113_update_type_column_in_template_usages_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_translations`
--

CREATE TABLE `model_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `translateable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translateable_id` bigint UNSIGNED NOT NULL,
  `locale` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `notificationable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notificationable_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `is_read` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1,No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint UNSIGNED NOT NULL,
  `otpable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otpable_id` bigint UNSIGNED NOT NULL,
  `otp` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` enum('1','2','-1') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MONTHLY = 1; YEARLY = 2; UNLIMITED = -1',
  `price` double(25,2) NOT NULL DEFAULT '0.00',
  `discount_price` double(25,2) NOT NULL DEFAULT '0.00',
  `total_subscription_income` double(25,5) DEFAULT '0.00000',
  `social_access` longtext COLLATE utf8mb4_unicode_ci,
  `ai_configuration` longtext COLLATE utf8mb4_unicode_ci,
  `template_access` longtext COLLATE utf8mb4_unicode_ci,
  `image_template_access` longtext COLLATE utf8mb4_unicode_ci,
  `video_template_access` longtext COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `is_recommended` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `is_feature` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1,No: 0',
  `is_free` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `affiliate_commission` double(25,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `uid`, `created_by`, `updated_by`, `title`, `icon`, `slug`, `duration`, `price`, `discount_price`, `total_subscription_income`, `social_access`, `ai_configuration`, `template_access`, `image_template_access`, `video_template_access`, `description`, `status`, `is_recommended`, `is_feature`, `is_free`, `affiliate_commission`, `created_at`, `updated_at`) VALUES
(1, '9f026237-846b-431d-8c0a-37554ede582b', NULL, NULL, 'Free', NULL, 'free', '1', 0.00, 0.00, 0.00000, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', '1', 0.00, '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_id` int DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1,Inactive: 0',
  `show_in_header` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1,No: 0',
  `show_in_footer` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1,No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `uid`, `serial_id`, `created_by`, `updated_by`, `title`, `slug`, `description`, `meta_title`, `meta_description`, `meta_keywords`, `status`, `show_in_header`, `show_in_footer`, `created_at`, `updated_at`) VALUES
(1, 'fe15589e-3f02-4f77-abaa-422e2fd2a80b', 0, NULL, NULL, 'Terms and conditions', 'terms-and-conditions', 'terms-and-conditions', NULL, NULL, NULL, '1', '0', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, 'c8e2a889-fd3b-4935-9476-f26c5e34c9c0', 1, NULL, NULL, 'Cookies policy', 'cookies-policy', 'cookies-policy', NULL, NULL, NULL, '1', '0', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, 'bb6a5943-866a-470f-bc1f-cf294696c35f', 2, NULL, NULL, 'Privacy policy', 'privacy-policy', 'privacy-policy', NULL, NULL, NULL, '1', '0', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `method_id` bigint UNSIGNED DEFAULT NULL,
  `currency_id` bigint DEFAULT NULL,
  `base_amount` double(20,5) NOT NULL DEFAULT '0.00000',
  `amount` double(20,5) NOT NULL DEFAULT '0.00000',
  `base_charge` double(20,5) NOT NULL DEFAULT '0.00000',
  `charge` double(20,5) NOT NULL DEFAULT '0.00000',
  `base_rate` double(20,5) NOT NULL DEFAULT '0.00000',
  `rate` double(20,5) NOT NULL DEFAULT '0.00000',
  `base_final_amount` double(20,5) NOT NULL DEFAULT '0.00000',
  `final_amount` double(20,5) NOT NULL DEFAULT '0.00000',
  `trx_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_data` text COLLATE utf8mb4_unicode_ci,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `gateway_response` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('-1','1','2','3','4','5') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Paid: 1, Cancel: 2, Pening: 3, Failed: 4, Rejected: 5, Initiate: -1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_id` int DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` longtext COLLATE utf8mb4_unicode_ci,
  `extra_parameters` longtext COLLATE utf8mb4_unicode_ci,
  `percentage_charge` double(25,2) NOT NULL DEFAULT '0.00',
  `fixed_charge` double(25,2) NOT NULL DEFAULT '0.00',
  `minimum_amount` double(25,2) NOT NULL DEFAULT '0.00',
  `maximum_amount` double(25,2) NOT NULL DEFAULT '0.00',
  `note` text COLLATE utf8mb4_unicode_ci,
  `gateway_response` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `type` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Automatic: 1, Manual: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `uid`, `serial_id`, `currency_id`, `created_by`, `updated_by`, `name`, `code`, `parameters`, `extra_parameters`, `percentage_charge`, `fixed_charge`, `minimum_amount`, `maximum_amount`, `note`, `gateway_response`, `status`, `type`, `created_at`, `updated_at`) VALUES
(1, '2d827411-619c-4940-92fb-834a8cda3ccb', 1, 1, 1, NULL, 'BKASH', 'bkash', '{\"api_key\":\"@@\",\"username\":\"@@\",\"password\":\"@@\",\"api_secret\":\"@@\",\"sandbox\":\"1\"}', '{\"callback\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, '9f90932f-8136-4304-881c-d8072bbbc9ac', 2, 1, 1, NULL, 'NAGAD', 'nagad', '{\"pub_key\":\"@@\",\"pri_key\":\"@@\",\"marchent_number\":\"@@\",\"marchent_id\":\"@@\",\"sandbox\":\"1\"}', '{\"callback\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, '14639f88-7deb-4e81-898b-7c987ab987bc', 3, 1, 1, NULL, 'PAYPAL', 'paypal', '{\"cleint_id\":\"@@\",\"secret\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, '11e6f4db-6041-45c0-b837-9efbe244ca01', 4, 1, 1, NULL, 'STRIPE', 'stripe', '{\"secret_key\":\"@@\",\"publishable_key\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(5, 'aa2cfdcd-ac37-487a-9371-d22e1a35c563', 5, 1, 1, NULL, 'PAYEER', 'payeer', '{\"merchant_id\":\"@@\",\"secret_key\":\"@@\"}', '{\"status\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(6, 'fa9ac47b-a545-4f1e-8b79-08e031f0b0c1', 6, 1, 1, NULL, 'PAYSTACK', 'paystack', '{\"public_key\":\"@@\",\"secret_key\":\"@@\"}', '{\"callback\":\"ipn\",\"webhook\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(7, '46c8d1c2-ed57-4fd4-aea6-9af82ddbf366', 7, 1, 1, NULL, 'FLUTTERWAVE', 'flutterwave', '{\"public_key\":\"@@\",\"secret_key\":\"@@\",\"encryption_key\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(8, 'c298477b-8c89-457a-875c-94277a6bd153', 8, 1, 1, NULL, 'RAZORPAY', 'razorpay', '{\"key_id\":\"@@\",\"key_secret\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(9, '1804a51a-4ef6-4d57-b5fa-75914c81d306', 9, 1, 1, NULL, 'INSTAMOJO', 'instamojo', '{\"api_key\":\"@@\",\"auth_token\":\"@@\",\"salt\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(10, '4468d981-acfc-4f35-a9fc-6a6ca660f291', 10, 1, 1, NULL, 'MOLLIE', 'mollie', '{\"api_key\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(11, '911be562-0c7e-409d-b7c0-8dc7168a9802', 11, 1, 1, NULL, 'PAYUMONEY', 'payumoney', '{\"merchant_key\":\"@@\",\"salt\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(12, '14cf1583-8e66-4f44-a24b-42d31d613eab', 12, 1, 1, NULL, 'MERCADOPOGO', 'mercadopago', '{\"access_token\":\"@@\"}', '[]', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(13, '77b2b39b-39ea-48d6-b6d6-787ad0b197d2', 13, 1, 1, NULL, 'CASHMAAL', 'cashmaal', '{\"web_id\":\"@@\",\"ipn_key\":\"@@\"}', '{\"ipn_url\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(14, 'ba63e7dc-3e1f-4f33-ab21-b7b384135e17', 14, 1, 1, NULL, 'PAYTM', 'paytm', '{\"mid\":\"@@\",\"merchant_key\":\"@@\",\"website\":\"@@\",\"industry_type_id\":\"@@\",\"channel_id\":\"@@\",\"transaction_url\":\"@@\",\"transaction_status_url\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(15, 'ea111036-077c-4696-9e76-0d8fe3777cf5', 16, 1, 1, NULL, 'AUTHORIZEDOTNET', 'authorizedotnet', '{\"login_id\":\"@@\",\"current_transaction_key\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(16, '128ced5f-300d-485a-924c-6b718f17a5fc', 17, 1, 1, NULL, 'NMI', 'nmi', '{\"api_key\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(17, 'cf67a06a-4496-431d-8131-c83ec009ef8d', 18, 1, 1, NULL, 'BTCPAY', 'btcpay', '{\"store_id\":\"@@\",\"api_key\":\"@@\",\"server_name\":\"@@\",\"secret_code\":\"@@\"}', '{\"callback\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(18, '3d4d4966-1ec9-451b-a1e1-a9a60782f9bb', 19, 1, 1, NULL, 'PERFECTMONEY', 'perfectmoney', '{\"passphrase\":\"@@\",\"wallet_id\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(19, '6a637655-e3d0-4eb4-9940-5c11ad70a17c', 22, 1, 1, NULL, 'COINGATE', 'coingate', '{\"api_key\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(20, '6f8c773a-60c5-481f-b46b-ba852f04175b', 23, 1, 1, NULL, 'SKRILL', 'skrill', '{\"secret_key\":\"@@\",\"skrill_email\":\"@@\"}', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(21, 'c3faf854-4892-4d70-aad0-333a9fe80984', 24, 1, 1, NULL, 'COINBASE', 'coinbase', '{\"api_key\":\"@@\",\"webhook_secret\":\"@@\"}', '{\"webhook\":\"ipn\"}', 0.00, 0.00, 0.00, 0.00, NULL, NULL, '1', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_webhook_logs`
--

CREATE TABLE `post_webhook_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `webhook_response` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Deactive: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `uid`, `created_by`, `updated_by`, `name`, `permissions`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ac1b1b18-d3d4-4aa2-af66-030cc6f5949d', NULL, NULL, 'Manager', '{\"language\":[\"view_language\",\"translate_language\",\"create_language\",\"update_language\",\"delete_language\"],\"staff\":[\"view_staff\",\"create_staff\",\"update_staff\",\"delete_staff\"],\"withdraw_method\":[\"view_withdraw\",\"create_withdraw\",\"update_withdraw\",\"delete_withdraw\"],\"currency\":[\"view_currency\",\"create_currency\",\"update_currency\",\"delete_currency\"],\"social_account\":[\"view_account\",\"create_account\",\"update_account\",\"delete_account\"],\"social_post\":[\"view_post\",\"create_post\",\"update_post\",\"delete_post\"],\"ticket\":[\"view_ticket\",\"delete_ticket\"],\"user\":[\"view_user\",\"create_user\",\"update_user\",\"delete_user\"],\"role\":[\"view_role\",\"create_role\",\"update_role\",\"delete_role\"],\"payment_method\":[\"view_method\",\"create_method\",\"update_method\",\"delete_method\"],\"category\":[\"view_category\",\"create_category\",\"update_category\",\"delete_category\"],\"page\":[\"view_page\",\"create_page\",\"update_page\",\"delete_page\"],\"ai_template\":[\"view_ai_template\",\"create_ai_template\",\"update_ai_template\",\"delete_ai_template\"],\"package\":[\"view_package\",\"create_package\",\"update_package\",\"delete_package\"],\"menu\":[\"view_menu\",\"create_menu\",\"update_menu\",\"delete_menu\"],\"frontend\":[\"view_frontend\",\"update_frontend\"],\"blog\":[\"view_blog\",\"create_blog\",\"update_blog\",\"delete_blog\"],\"content\":[\"view_content\",\"create_content\",\"update_content\",\"delete_content\"],\"security_settings\":[\"view_security\",\"update_security\"],\"transaction\":[\"view_report\",\"update_report\",\"delete_report\"],\"platform\":[\"view_platform\",\"update_platform\"],\"gateway\":[\"view_gateway\",\"update_gateway\"],\"notification_template\":[\"view_template\",\"update_template\"],\"notification\":[\"view_notification\"],\"settings\":[\"view_settings\",\"update_settings\"],\"dashboard\":[\"view_dashboard\"]}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `uid`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, '41f7f881-4f8b-4537-bff6-4d22e9f44e85', 'site_name', 'demo', NULL, NULL),
(2, 'c7f81f3b-4f16-4793-95a6-8d51be9bb6a5', 'logo_icon', '@@', NULL, NULL),
(3, 'a4a2c7d5-0d7d-4625-9b48-3e2a74822077', 'site_logo', '@@', NULL, NULL),
(4, '30ef9b50-02ec-4b59-80bd-0f4a2aa37b70', 'user_site_logo', '@@', NULL, NULL),
(5, '5c88131a-2fbe-47dc-8582-97c1c3562d08', 'favicon', '@@', NULL, NULL),
(6, 'fd77d90b-790f-4182-9a95-6441b048bb71', 'phone', '0xxxxxxxx', NULL, NULL),
(7, 'd11dc086-5096-40aa-8659-a6f6e2e85235', 'address', 'demo', NULL, NULL),
(8, 'ef4a5e7f-db49-4258-a33f-bc87f6370d0c', 'email', 'demo@gmail.com', NULL, NULL),
(9, '4e3bd228-e5d5-4868-9f30-3a205a4e7678', 'last_corn_run', '2025-08-17 07:56:32', NULL, NULL),
(10, '1cee9bb1-6814-48c8-88dc-f5c526f390e1', 'registration', '1', NULL, NULL),
(11, '88b17731-33f9-4f54-bf33-13c70c2f1d39', 'login', '1', NULL, NULL),
(12, '8d9e6ab4-64a9-47ff-bf12-88b07c28da4e', 'login_with', '[\"email\",\"phone\",\"username\"]', NULL, NULL),
(13, 'ebd4e26d-3cf7-482c-a112-3e8abcc5abac', 'default_sms_template', 'hi {{name}}, {{message}}', NULL, NULL),
(14, '5add1a4e-54d5-4dc4-ad64-2781cdd71405', 'default_mail_template', 'hi {{name}}, {{message}}', NULL, NULL),
(15, '3f8eaf6b-2439-4d0b-906b-1fd777449e02', 'two_factor_auth', '0', NULL, NULL),
(16, '66538271-69c2-4869-8783-bdfff1ba0690', 'sms_otp_verification', '0', NULL, NULL),
(17, 'e1daeb10-ba82-488d-b808-3be3d14e0883', 'registration_otp_verification', '0', NULL, NULL),
(18, '9648f123-2766-4a5a-893b-4215e42f493c', 'otp_expired_status', '0', NULL, NULL),
(19, '114aef1f-41ba-4b0c-a8a8-08e8fcf24465', 'sms_notifications', '0', NULL, NULL),
(20, '8242cc88-c461-42cf-9cd6-788ad5e4ce73', 'email_verification', '0', NULL, NULL),
(21, 'da1fe582-7673-4605-bd05-e0fad086b669', 'email_notifications', '0', NULL, NULL),
(22, 'c6200a69-e89e-41e1-b7e9-f5066a061bb3', 'slack_notifications', '0', NULL, NULL),
(23, '65449a5c-f217-41a6-a2ed-d7eb580a7766', 'currency_alignment', '0', NULL, NULL),
(24, 'c42432a7-9a22-44a6-b380-7952e726f313', 'num_of_decimal', '0', NULL, NULL),
(25, 'fc9c6bf7-5fd5-4532-bae4-e30b5d6ca746', 'decimal_separator', '.', NULL, NULL),
(26, '9d09c874-9fee-4a22-af27-fb778a4b46c9', 'thousands_separator', ',', NULL, NULL),
(27, '59a14b5a-283a-4a7e-8010-01ddc89a496b', 'price_format', '0', NULL, NULL),
(28, 'd1878df0-0c5b-47f4-bf44-b3b965d211c8', 'truncate_after', '1000', NULL, NULL),
(29, '508d4210-c940-498c-bc5c-05001518e689', 'slack_channel', '@@', NULL, NULL),
(30, 'ee82ba1b-c08a-486a-8d43-c3278a849441', 'slack_web_hook_url', '@@', NULL, NULL),
(31, '9541890d-1890-4ff3-9173-3cfae6c9e649', 'time_zone', NULL, NULL, NULL),
(32, '478bd91a-35e7-4403-84eb-c068d60737e0', 'site_seo', '0', NULL, NULL),
(33, '755d4d64-018e-4426-80cf-01ab7a948301', 'app_debug', '0', NULL, NULL),
(34, '74b9f2d9-39f4-4d78-95c0-10af89abebe8', 'maintenance_mode', '0', NULL, NULL),
(35, 'ffce291c-c915-47cb-8d3c-e824a3cd6b9e', 'pagination_number', '10', NULL, NULL),
(36, '073be9ba-3177-4696-a8a8-f798f4f67b45', 'copy_right_text', '@@@@', NULL, NULL),
(37, '01801305-ed6b-4155-8402-44328953ee6f', 'same_site_name', '0', NULL, NULL),
(38, '547e5d3d-deb9-413c-a426-bcfa7789864b', 'user_site_name', 'demo_site', NULL, NULL),
(39, '2cc86932-8e51-484e-9aee-fe7d9268d0b1', 'google_recaptcha', '{\"key\":\"@@@\",\"secret_key\":\"@@@\",\"status\":\"0\"}', NULL, NULL),
(40, '8d0d7cbc-1191-4b1b-8769-8f41583c8765', 'strong_password', '1', NULL, NULL),
(41, 'db244e5e-b9e1-46e5-a7cd-3d2ddbebe122', 'captcha', '0', NULL, NULL),
(42, 'ff684356-3afc-4139-8939-3718a2fe4f33', 'vistors', '500', NULL, NULL),
(43, '406038ca-69ce-462c-b1f6-b30366ba51e9', 'sign_up_bonus', '0', NULL, NULL),
(44, 'b7f13994-4a7e-4eed-a924-f01842ce972a', 'default_recaptcha', '0', NULL, NULL),
(45, 'ce412292-b2f4-4b35-87a3-270c2d52c051', 'captcha_with_login', '1', NULL, NULL),
(46, 'f71460b6-9a78-4b9e-989b-29131f48210c', 'captcha_with_registration', '1', NULL, NULL),
(47, 'ff588c48-33b2-4201-8a62-ea2314f7affe', 'social_login', '0', NULL, NULL),
(48, 'fa3d1d72-5331-4c45-9047-c5423c0cf2a5', 'social_login_with', '{\"google_oauth\":{\"client_id\":\"@@\",\"client_secret\":\"@@\",\"status\":\"1\"},\"facebook_oauth\":{\"client_id\":\"@@\",\"client_secret\":\"@@\",\"status\":\"1\"}}', NULL, NULL),
(49, 'c94741e0-41b2-4caa-9f5b-31d5091a50fb', 'google_map_api_key', '@@@@', NULL, NULL),
(50, '3de34573-1693-4f3c-8526-1f2006b58c06', 'storage', 'local', NULL, NULL),
(51, 'a48ad4ee-aee0-444a-9894-3f80118e284e', 'mime_types', '[\"png\",\"jpg\",\"jpeg\",\"jpe\"]', NULL, NULL),
(52, '91372df1-0295-4d08-b821-d9fc2f284d0f', 'max_file_size', '20000', NULL, NULL),
(53, 'dc88b0db-6ceb-4476-8002-4664d55ee671', 'max_file_upload', '4', NULL, NULL),
(54, '7deca188-5c86-4649-9a81-76fa195e6800', 'aws_s3', '{\"s3_key\":\"@@\",\"s3_secret\":\"@@\",\"s3_region\":\"@@\",\"s3_bucket\":\"@@\"}', NULL, NULL),
(55, '73342092-4f27-4278-941a-06df79bdfc29', 'ftp', '{\"host\":\"@@\",\"port\":\"@@\",\"user_name\":\"@@\",\"password\":\"@@\",\"root\":\"\\/\"}', NULL, NULL),
(56, '9b82230f-de1a-4f38-92bf-21c1b59cdc9c', 'database_notifications', '0', NULL, NULL),
(57, 'd08570f1-33cf-478c-8337-ae64144624d6', 'cookie', '0', NULL, NULL),
(58, '529e5faa-f25a-498b-9cbe-19974fd5b506', 'frontend_preloader', '0', NULL, NULL),
(59, '32a4a8c4-b5a6-4c7e-9840-0fad9418fc42', 'cookie_text', 'demo cookie_text', NULL, NULL),
(60, 'f5c16692-23a5-421d-b1da-473f49d75cfe', 'google_map_key', '@@', NULL, NULL),
(61, 'aea67de9-1745-4d78-a827-b622b6437bca', 'geo_location', 'map_base', NULL, NULL),
(62, '61d97bb6-96b7-4d9f-9902-ec9db834011a', 'sentry_dns', '@@', NULL, NULL),
(63, 'b025bff2-f8ca-44db-bc7b-d6e3112b2e63', 'login_attempt_validation', '0', NULL, NULL),
(64, '89d1f2a2-839f-4a3a-aa3f-9715d96b1b71', 'max_login_attemtps', '5', NULL, NULL),
(65, '00ed62fa-dc10-44ed-824c-54474f16a468', 'otp_expired_in', '2', NULL, NULL),
(66, 'a1eea816-9f3c-4804-9ef5-1a03cbe9b196', 'api_route_rate_limit', '1000', NULL, NULL),
(67, '77a24616-8071-4316-aeb8-ebd62f574c71', 'web_route_rate_limit', '1000', NULL, NULL),
(68, '5e0fe5e0-1bd9-4c61-8d8f-0fb8b1d9bb63', 'primary_color', '#7f56d9', NULL, NULL),
(69, 'efc5ef99-ac18-416b-a3e7-c4b00066a096', 'secondary_color', '#ffbf00', NULL, NULL),
(70, '290732d5-5935-4ccf-89e5-f1c008c67137', 'text_primary', '#24282c', NULL, NULL),
(71, '80a68be8-6b00-40eb-b84c-81284d1ca1ae', 'text_secondary', '#545454', NULL, NULL),
(72, 'ef2016b7-f633-46a5-87bf-c6d607f38709', 'btn_text_primary', '#ffffff', NULL, NULL),
(73, '7b5e86f5-a32e-414f-a0a5-c5ef0f51c769', 'btn_text_secondary', '#6a7b65', NULL, NULL),
(74, '0c507201-cf6f-490c-8ff6-24791dc86bd8', 'site_description', 'demo description', NULL, NULL),
(75, '88cd1ede-259b-4ff1-8a45-aa52808d1323', 'sms_notification', '0', NULL, NULL),
(76, '6fff1030-26bc-482c-ac08-edf83da0f33c', 'max_pending_withdraw', '1', NULL, NULL),
(77, 'e4dd813d-cee6-490f-be55-d1f1d1634c41', 'force_ssl', '0', NULL, NULL),
(78, '1d613ce0-bb55-40c1-9b71-b32dfa5f85f3', 'dos_prevent', '0', NULL, NULL),
(79, 'b9edddc9-7efb-42d8-ae5d-61208c9d6c11', 'dos_attempts', '0', NULL, NULL),
(80, '1bcef86f-8617-4978-a95a-ed9aa8754b43', 'dos_attempts_in_second', '5', NULL, NULL),
(81, '3c5096c0-e86b-4f34-a7bc-68fa6bd25257', 'dos_security', 'captcha', NULL, NULL),
(82, 'c43db9bb-5428-453d-9270-db682e7b8449', 'google_ads', '0', NULL, NULL),
(83, '191ae317-3f84-4f72-910b-b7b849bb319a', 'google_adsense_publisher_id', '@@', NULL, NULL),
(84, 'a5d034de-1153-49a3-b3bf-fec002b3edc1', 'google_analytics', '0', NULL, NULL),
(85, '1bc19ca0-cf37-43d1-b7f8-426634151f99', 'google_analytics_tracking_id', '@@', NULL, NULL),
(86, '3047657d-c93c-4cd5-b1d1-9a08b53b4124', 'breadcrumbs', '1', NULL, NULL),
(87, '7ba2bc8c-3eac-4ceb-b4cd-b0355598e8da', 'expired_data_delete', '0', NULL, NULL),
(88, '904984d0-bc39-4097-96fa-ca222d9d5079', 'expired_data_delete_after', '10', NULL, NULL),
(89, '9e5e1835-bcac-43cd-a15b-cd1c23b6ee1b', 'site_meta_keywords', '[\"demo\"]', NULL, NULL),
(90, '59bf8bba-087b-4a16-ae84-6b4dc6006921', 'title_separator', ':', NULL, NULL),
(91, 'af463daa-d21d-4e1d-8047-23f795cce703', 'ai_default_creativity', '0.5', NULL, NULL),
(92, '54b403fe-f8a9-4a7f-89d4-b18d98864c25', 'ai_default_tone', 'Casual', NULL, NULL),
(93, '9459e72e-5fa3-4feb-b3d3-f346e09d5ebd', 'ai_max_result', '4', NULL, NULL),
(94, '0a1ae350-79f1-4930-b656-89dc46ea489d', 'default_max_result', '20', NULL, NULL),
(95, '16e3690c-6d2d-4571-956c-fced61f9dad4', 'ai_result_length', '20', NULL, NULL),
(96, '07840ca9-8ca7-4444-a912-d2090380c5e1', 'ai_bad_words', NULL, NULL, NULL),
(97, '6e709fbb-aec9-445f-ae80-59a820914004', 'open_ai_model', NULL, NULL, NULL),
(98, 'ffe1f932-9c6b-4582-85a9-b23a9e484648', 'open_ai_secret', '@@', NULL, NULL),
(99, 'fe419f90-2c4e-4a64-a57e-afbaedd36377', 'ai_key_usage', '0', NULL, NULL),
(100, 'b2de6366-3592-4e47-8d63-3d9920466ff6', 'rand_api_key', '@@', NULL, NULL),
(101, '44ad61ef-e17b-4a42-baa2-1b8d66350017', 'subscription_carry_forword', '0', NULL, NULL),
(102, '31d4496b-a7f1-402b-9587-889c2e0f29e1', 'auto_subscription', '0', NULL, NULL),
(103, '9f38d39d-b881-4b70-ab57-8de85090e660', 'auto_subscription_package', NULL, NULL, NULL),
(104, '71073482-b3c5-4ce8-8f80-25de8a744094', 'signup_bonus', NULL, NULL, NULL),
(105, '873d8383-f73d-475a-9997-454c36f763ae', 'webhook_api_key', '@@', NULL, NULL),
(106, '8443bf49-0d0c-4f68-8248-a7329b2a98f4', 'kyc_settings', '[{\"labels\":\"Name\",\"name\":\"name\",\"placeholder\":\"Name\",\"type\":\"text\",\"required\":\"1\",\"default\":\"1\",\"multiple\":\"0\"}]', NULL, NULL),
(107, 'c19de743-086c-462a-b03d-dbd24a25e329', 'kyc_verification', '0', NULL, NULL),
(108, '07ed5278-0acb-451c-bae4-c4aade4c2e5c', 'ticket_settings', '[{\"labels\":\"Name\",\"name\":\"name\",\"placeholder\":\"Name\",\"type\":\"text\",\"required\":\"1\",\"default\":\"1\",\"multiple\":\"0\"},{\"labels\":\"Subject\",\"name\":\"subject\",\"placeholder\":\"Subject\",\"type\":\"text\",\"required\":\"1\",\"default\":\"1\",\"multiple\":\"0\"},{\"labels\":\"Description\",\"name\":\"description\",\"placeholder\":\"Description\",\"type\":\"textarea\",\"required\":\"1\",\"default\":\"1\",\"multiple\":\"0\"},{\"labels\":\"File\",\"name\":\"attachment\",\"placeholder\":\"Upload file\",\"type\":\"file\",\"required\":\"1\",\"default\":\"1\",\"multiple\":\"1\"}]', NULL, NULL),
(109, '7e3c8aac-7312-402a-bd1f-4de6a239275b', 'continuous_commission', '0', NULL, NULL),
(110, '542de828-9a0b-4c89-be2b-c69f23a9e955', 'affiliate_system', '0', NULL, NULL),
(111, '74f2a5ed-df68-473e-9c08-641e573d23a2', 'multi_lang', '0', NULL, NULL),
(112, 'b837e85f-b25f-4b90-8db9-34640cfc3e97', 'multi_currency', '0', NULL, NULL),
(113, '57d85044-9459-409c-bf5a-67061be5daf5', 'meta_image', '@@', NULL, NULL),
(114, NULL, 'app_version', '2.3', NULL, NULL),
(115, NULL, 'system_installed_at', '2025-08-17 02:56:35', NULL, NULL),
(116, NULL, 'is_domain_verified', '1', NULL, NULL),
(117, NULL, 'next_verification', '2025-08-18 02:56:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sms_gateways`
--

CREATE TABLE `sms_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credential` longtext COLLATE utf8mb4_unicode_ci,
  `default` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_gateways`
--

INSERT INTO `sms_gateways` (`id`, `uid`, `created_by`, `updated_by`, `code`, `name`, `credential`, `default`, `created_at`, `updated_at`) VALUES
(1, '850dd16c-5647-4a8b-9902-b115791bbb80', NULL, NULL, '101VON', 'vonage', '{\"api_key\":\"@@\",\"api_secret\":\"@@\",\"sender_id\":\"@@\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, 'ff2b9220-8728-47aa-b618-921033ab0103', NULL, NULL, '102TWI', 'twilio', '{\"account_sid\":\"@@\",\"auth_token\":\"@@\",\"from_number\":\"@@\"}', '0', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, '0e0d5eee-b2dc-404d-94fe-4adc7c0053e0', NULL, NULL, '103BIRD', 'messagebird', '{\"access_key\":\"@@\"}', '0', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, '80af4c39-ea20-4152-9c2a-4b9f370e3c26', NULL, NULL, '104INFO', 'infobip', '{\"sender_id\":\"@@\",\"infobip_api_key\":\"@@\",\"infobip_base_url\":\"@@\"}', '0', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `social_accounts`
--

CREATE TABLE `social_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_information` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Disconnected: 0, Connected: 1',
  `is_official` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Yes: 1, No: 1',
  `is_connected` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Yes: 1, No: 1',
  `account_type` enum('1','2','0','3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Profile: 0, Page: 1 ,Group:2',
  `details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_expire_at` datetime DEFAULT NULL,
  `refresh_token` text COLLATE utf8mb4_unicode_ci,
  `refresh_token_expire_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_posts`
--

CREATE TABLE `social_posts` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` bigint UNSIGNED NOT NULL,
  `platform_id` bigint UNSIGNED DEFAULT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `link` longtext COLLATE utf8mb4_unicode_ci,
  `platform_response` longtext COLLATE utf8mb4_unicode_ci,
  `is_scheduled` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'No: 0, Yes: 1',
  `schedule_time` timestamp NULL DEFAULT NULL,
  `repeat_every` mediumint NOT NULL DEFAULT '0' COMMENT 'In minutes',
  `repeat_schedule_end_date` timestamp NULL DEFAULT NULL,
  `is_draft` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'No: 0, Yes: 1',
  `status` enum('0','1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Pending: 0, Success: 1 ,Failed:2,Schedule:3',
  `post_type` enum('0','1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FEED: 0 ,Story:2,REELS:1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `old_package_id` bigint DEFAULT NULL,
  `word_balance` mediumint NOT NULL DEFAULT '0',
  `remaining_word_balance` mediumint NOT NULL DEFAULT '0',
  `carried_word_balance` mediumint NOT NULL DEFAULT '0',
  `image_balance` mediumint NOT NULL DEFAULT '0',
  `remaining_image_balance` mediumint NOT NULL DEFAULT '0',
  `carried_image_balance` mediumint NOT NULL DEFAULT '0',
  `video_balance` mediumint NOT NULL DEFAULT '0',
  `remaining_video_balance` mediumint NOT NULL DEFAULT '0',
  `carried_video_balance` mediumint NOT NULL DEFAULT '0',
  `total_profile` mediumint NOT NULL DEFAULT '0',
  `carried_profile` mediumint NOT NULL DEFAULT '0',
  `post_balance` mediumint NOT NULL DEFAULT '0',
  `carried_post_balance` mediumint NOT NULL DEFAULT '0',
  `remaining_post_balance` mediumint NOT NULL DEFAULT '0',
  `payment_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `trx_code` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Expired: 3, Running: 1, Inactive: 2',
  `payment_status` enum('-1','0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Initiate:-1 ,Pending: 0,Complete: 1, Rejected: 2',
  `expired_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `sms_body` longtext COLLATE utf8mb4_unicode_ci,
  `template_key` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Deactive: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `uid`, `created_by`, `updated_by`, `name`, `slug`, `subject`, `body`, `sms_body`, `template_key`, `status`, `created_at`, `updated_at`) VALUES
(1, '9c50cf56-e01d-4550-b689-8e7a515b8a2c', 1, NULL, 'PASSWORD RESET', 'PASSWORD_RESET', 'Password Reset', 'We have received a request to reset the password for your account on {{otp_code}} and Request time {{time}}', 'We have received a request to reset the password for your account on {{otp_code}} and Request time {{time}}', '{\"otp_code\":\"Password Reset Code\",\"time\":\"Password Reset Time\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(2, 'f901e80f-b5d3-4347-b535-caa8c0b8c65b', 1, NULL, 'REGISTRATION VERIFY', 'REGISTRATION_VERIFY', 'Registration Verify', 'We have received a request to create an account, you need to verify email first, your verification code is {{otp_code}} and request time {{time}}', 'We have received a request to create an account, you need to verify email first, your verification code is {{otp_code}} and request time {{time}}', '{\"otp_code\":\"Verification Code\",\"time\":\"Time\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(3, '4dcd5d35-b3e1-489f-9014-d5812c83c076', 1, NULL, 'SUPPORT TICKET REPLY', 'SUPPORT_TICKET_REPLY', 'Support Ticket', '<p>Hello Dear ! To provide a response to Ticket ID {{ticket_number}}, kindly click the link provided below in order to reply to the ticket &nbsp;<a style=\"background-color:#13C56B;border-radius:4px;color:#fff;display:inline-flex;font-weight:400;line-height:1;padding:5px 10px;text-align:center:font-size:14px;text-decoration:none;\" href=\"{{link}}\">Link</a></p>', 'Hello Dear ! To get a response to Ticket ID {{ticket_number}}, kindly click the link provided below in order to reply to the ticket. {{link}}', '{\"ticket_number\":\"Support Ticket Number\",\"link\":\"Ticket URL For relpy\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(4, '953069b5-07f5-4b9d-873e-32d71d34e578', 1, NULL, 'TEST MAIL', 'TEST_MAIL', 'Test Mail', 'This is testing mail for mail configuration Request time<span style=\"background-color: rgb(255, 255, 0);\"> {{time}}</span></h5>', '', '{\"time\":\"Time\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(5, 'ed97b686-c411-43f2-a4eb-aee23fedef47', 1, NULL, 'TICKET REPLY', 'TICKET_REPLY', 'Support Ticket Reply', '{{name}}!! Just Replied To A Ticket..  To provide a response to Ticket ID {{ticket_number}}, kindly click the link provided below in order to reply to the ticket.  {{link}}', '{{name}}!! Just Replied To A Ticket..  To provide a response to Ticket ID {{ticket_number}}, kindly click the link provided below in order to reply to the ticket.  {{link}}', '{\"name\":\"Admin\\/Agent\\/User Name\",\"ticket_number\":\"Support Ticket Number\",\"link\":\"Ticket URL For relpy\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(6, '98bbf601-9fa9-4e2d-94d8-141720bed282', 1, NULL, 'OTP VERIFY', 'OTP_VERIFY', 'OTP Verificaton', 'Your Otp {{otp_code}} and request time {{time}}, expired time {{expire_time}}', 'Your Otp {{otp_code}} and request time {{time}}, expired time {{expire_time}}', '{\"otp_code\":\"OTP (One time password)\",\"time\":\"Time\",\"expire_time\":\"OTP Expired Time\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(7, '48a6f024-9dbb-49b7-8b9b-9536e0504081', 1, NULL, 'WITHDRAWAL REQUEST ACCEPTED', 'WITHDRAWAL_REQUEST_ACCEPTED', 'Withdrawal Request Accepted', 'We are pleased to inform you that your withdrawal request has been accepted. Here are the details: - Transaction Code: {{trx_code}} - Amount: {{amount}} - Method: {{method}} - Time of Approval: {{time}} The funds will be processed accordingly.', 'We are pleased to inform you that your withdrawal request has been accepted. Here are the details: - Transaction Code: {{trx_code}} - Amount: {{amount}} - Method: {{method}} - Time of Approval: {{time}} The funds will be processed accordingly.', '{\"time\":\"Time\",\"trx_code\":\"Transaction id\",\"amount\":\"Withdraw amount\",\"method\":\"Withdraw method\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(8, 'b848cbec-6155-41e3-888f-82638724b15e', 1, NULL, 'WITHDRAWAL REQUEST SUBMIT', 'WITHDRAWAL_REQUEST_SUBMIT', 'New Withdrawal Request Submitted', 'A new withdrawal request has been submitted. Here are the details: User: {{name}} Transaction ID: {{trx_code}} Amount: {{amount}} Withdrawal Method: {{method}} Requested On: {{time}}', 'A new withdrawal request has been submitted. Here are the details: User: {{name}} Transaction ID: {{trx_code}} Amount: {{amount}} Withdrawal Method: {{method}} Requested On: {{time}}', '{\"name\":\"User name\",\"trx_code\":\"Transaction id\",\"amount\":\"Withdraw amount\",\"method\":\"Withdraw method\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(9, '6059be7c-3d3f-460b-91aa-647923b2a5e5', 1, NULL, 'DEPOSIT REQUEST', 'DEPOSIT_REQUEST', 'New Deposit Request', 'We have received your deposit request for an amount of {{amount}} via {{payment_method}} at {{time}} Your transaction code is {{trx_code}}. Please wait for our confirmation', 'We have received your deposit request for an amount of {{amount}} via {{payment_method}} at {{time}} Your transaction code is {{trx_code}}. Please wait for our confirmation', '{\"time\":\"Time\",\"trx_code\":\"Transaction id\",\"amount\":\"Deposited amount\",\"payment_method\":\"Payment method\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(10, '6607c055-cadf-4634-a216-23924f29fad3', 1, NULL, 'DEPOSIT REQUEST ACCEPTED', 'DEPOSIT_REQUEST_ACCEPTED', 'Deposit Request Accepted', 'We are pleased to inform you that your deposit request has been accepted. Your transaction code is {{trx_code}}. The deposited amount is {{amount}} via {{payment_method}}', 'We are pleased to inform you that your deposit request has been accepted. Your transaction code is {{trx_code}}. The deposited amount is {{amount}} via {{payment_method}}', '{\"trx_code\":\"Transaction id\",\"amount\":\"Deposited amount\",\"payment_method\":\"Payment method\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(11, 'c6d2dd2b-d0aa-46db-bb09-0c4ad79e18bf', 1, NULL, 'NEW DEPOSIT', 'NEW_DEPOSIT', 'Newly Deposited Amount', 'A new deposit has been made by {{name}}. Here are the details: - User: {{name}} - Transaction Code: {{trx_code}} - Amount: {{amount}} - Payment Method: {{payment_method}} - Time of Deposit: {{time}} Please review and take the necessary actions.', 'A new deposit has been made by {{name}}. Here are the details: - User: {{name}} - Transaction Code: {{trx_code}} - Amount: {{amount}} - Payment Method: {{payment_method}} - Time of Deposit: {{time}} Please review and take the necessary actions.', '{\"time\":\"Time\",\"trx_code\":\"Transaction id\",\"amount\":\"Deposited amount\",\"payment_method\":\"Payment method\",\"name\":\"User name\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(12, 'b28e8cca-4b63-4dff-9306-0f0cf5c401a2', 1, NULL, 'WITHDRAWAL REQUEST REJECTED', 'WITHDRAWAL_REQUEST_REJECTED', 'Withdrawal Request Rejected', 'We regret to inform you that your withdrawal request has been rejected. Please review the details: - Transaction Code: {{trx_code}} - Amount: {{amount}} - Method: {{method}} - Reason for Rejection: {{reason}} - Time of Rejection: {{time}}', 'We regret to inform you that your withdrawal request has been rejected. Please review the details: - Transaction Code: {{trx_code}} - Amount: {{amount}} - Method: {{method}} - Reason for Rejection: {{reason}} - Time of Rejection: {{time}}', '{\"time\":\"Time\",\"trx_code\":\"Transaction id\",\"amount\":\"Withdraw amount\",\"method\":\"Withdraw method\",\"reason\":\"Rejection reason\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(13, '57698f16-fa83-4003-8f68-6852d671a3df', 1, NULL, 'WITHDRAWAL REQUEST RECEIVED', 'WITHDRAWAL_REQUEST_RECEIVED', 'Withdrawal Request Received', 'We have received your withdrawal request. Here are the details: - Transaction Code: {{trx_code}} - Amount: {{amount}} - Method: {{method}} - Time : {{time}} Your request is currently being processed. We will notify you once the status is updated.', 'We have received your withdrawal request. Here are the details: - Transaction Code: {{trx_code}} - Amount: {{amount}} - Method: {{method}} - Time : {{time}} Your request is currently being processed. We will notify you once the status is updated.', '{\"time\":\"Time\",\"trx_code\":\"Transaction id\",\"amount\":\"Withdraw amount\",\"method\":\"Withdraw method\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(14, 'fe0011a2-ef69-4b50-86ba-ff75c21f639b', 1, NULL, 'NEW TICKET', 'NEW_TICKET', 'New Ticket', 'A new ticket has been created with the following details: Ticket ID: {{ticket_number}} Created by: {{name}} Date and Time: {{time}} Priority: {{priority}}', 'A new ticket has been created with the following details: Ticket ID: {{ticket_number}} Created by: {{name}} Date and Time: {{time}} Priority: {{priority}}', '{\"ticket_number\":\"Support Ticket Number\",\"name\":\"User name\",\"time\":\"Created Date and time\",\"priority\":\"Ticket Priority\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(15, 'd430eefc-8cb3-4155-a1cc-c3c2df5936f5', 1, NULL, 'DEPOSIT REQUEST REJECTED', 'DEPOSIT_REQUEST_REJECTED', 'Deposit Request Rejected', 'We regret to inform you that your deposit request has been rejected. reason : {{reason}} Your transaction code is {{trx_code}}. The deposited amount is {{amount}} via {{payment_method}}', 'We regret to inform you that your deposit request has been rejected. reason : {{reason}} Your transaction code is {{trx_code}}. The deposited amount is {{amount}} via {{payment_method}}', '{\"trx_code\":\"Transaction id\",\"amount\":\"Deposited amount\",\"payment_method\":\"Payment method\",\"reason\":\"Rejection reason\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(16, '9d61baff-c1b5-4db8-85f5-7cc16af2126f', 1, NULL, 'USER ACTION', 'USER_ACTION', 'New User Action', 'A new {{type}}  has occurred. Here are the details: {{details}} Please respond promptly.', 'A new {{type}}  has occurred. Here are the details: {{details}} Please respond promptly.', '{\"type\":\"Action type\",\"details\":\"Action Details\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(17, '88277f04-8c13-4a77-a7ad-4f7d0470224e', 1, NULL, 'KYC UPDATE', 'KYC_UPDATE', 'KYC Log Status Updated', 'We\'re here to inform you that there has been an update to your KYC (Know Your Customer) log status.\n                                            Kyc Information:\n                                                Applied By : {{name}}\n                                                status     : {{status}}', 'We\'re here to inform you that there has been an update to your KYC (Know Your Customer) log status.\n                                            Kyc Information:Applied By : {{name}} status : {{status}}', '{\"name\":\"User name\",\"status\":\"Verification status\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(18, 'fc9abbb2-d0dd-42bd-9db2-f3672afb09bd', 1, NULL, 'KYC APPLIED', 'KYC_APPLIED', 'New KYC Verification Application Received', 'A new user has applied for KYC (Know Your Customer) verification. Here are the details\n                            Kyc Information:Applied By :{{name}} Application time :{{time}}', 'A new user has applied for KYC (Know Your Customer) verification. Here are the details\n                            Kyc Information:Applied By :{{name}} Application time :{{time}}', '{\"name\":\"User name\",\"time\":\"Time\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(19, 'ce6732ec-f995-4569-86d9-7062b2c7b6ca', 1, NULL, 'SUBSCRIPTION CREATED', 'SUBSCRIPTION_CREATED', 'New Subscription Created', 'A new subscription has been created.\n                                              Subscription Details:\n                                            - User: {{name}}\n                                            - Subscription Plan: {{package_name}}\n                                            - Start Date: {{start_date}}\n                                            - End Date: {{end_date}', 'A new subscription has been created.\n                                              Subscription Details:\n                                            - User: {{name}}\n                                            - Subscription Plan: {{package_name}}\n                                            - Start Date: {{start_date}}\n                                            - End Date: {{end_date}}', '{\"name\":\"User name\",\"start_date\":\"Start Date\",\"end_date\":\"End Date\",\"package_name\":\"Package name\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(20, '9c2fd90f-4e96-4f11-bf7a-1cd092f95d7d', 1, NULL, 'SUBSCRIPTION STATUS', 'SUBSCRIPTION_STATUS', 'Subscription Status Updated', 'We wanted to inform you that the status of your subscription has been updated.\n                                                Subscription Details:\n                                                - Plan: {{plan_name}}\n                                                - Status: {{status}}\n                                                - Time :{{time}}', 'We wanted to inform you that the status of your subscription has been updated.\n                                                Subscription Details:\n                                                - Plan: {{plan_name}}\n                                                - Status: {{status}}\n                                                - Time :{{time}}', '{\"status\":\"Status\",\"time\":\"Time\",\"plan_name\":\"Package name\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(21, 'c165d09a-0790-4c89-ac0e-fdf8fd4edcaa', 1, NULL, 'SUBSCRIPTION FAILED', 'SUBSCRIPTION_FAILED', 'Auto Subscription Renewal Failed', 'We regret to inform you that the automatic renewal of your subscription has failed. \n                                                Subscription Details:\n                                                - Plan: {{name}}\n                                                - Reason: {{reason}}\n                                                - Time :{{time}}', 'We regret to inform you that the automatic renewal of your subscription has failed. \n                                                Subscription Details:\n                                                - Plan: {{name}}\n                                                - Reason: {{reason}}\n                                                - Time :{{time}}', '{\"reason\":\"Failed Reason\",\"time\":\"Time\",\"name\":\"Package name\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(22, 'e50f0d1a-2b02-4b08-b2e6-473c515d3f0c', 1, NULL, 'SUBSCRIPTION EXPIRED', 'SUBSCRIPTION_EXPIRED', 'Subscription Expired', 'Your {{name}} Package Subscription Has Been Expired!! at time {{time}}', 'Your {{name}} Package Subscription Has Been Expired!! at time {{time}}', '{\"time\":\"Time\",\"name\":\"Package name\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35'),
(23, '7753dca3-5273-4b40-8c1f-69b5d1fed98c', 1, NULL, 'CONTACT REPLY', 'CONTACT_REPLY', 'Contact Message reply', 'Hello Dear! {{email}} {{message}}', 'Hello Dear! {{email}} {{message}}', '{\"email\":\"email\",\"message\":\"message\"}', '1', '2025-08-17 02:56:35', '2025-08-17 02:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `template_usages`
--

CREATE TABLE `template_usages` (
  `id` bigint UNSIGNED NOT NULL,
  `template_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `total_words` int NOT NULL DEFAULT '0',
  `total_images` int NOT NULL DEFAULT '0',
  `images` longtext COLLATE utf8mb4_unicode_ci,
  `total_videos` int NOT NULL DEFAULT '0',
  `videos` longtext COLLATE utf8mb4_unicode_ci,
  `open_ai_usage` longtext COLLATE utf8mb4_unicode_ci,
  `type` enum('text','image','video') COLLATE utf8mb4_unicode_ci DEFAULT 'text' COMMENT 'TEXT : text, IMAGE : image, VIDEO : video',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticket_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ticket_data` longtext COLLATE utf8mb4_unicode_ci,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('1','2','3','4','5','6') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2' COMMENT 'Open: 1, Pending: 2, Processing: 3, Solved: 4  ,On-Hold: 5 ,Closed: 5',
  `priority` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3' COMMENT 'Urgent: 1, High: 2, Low: 3, Medium: 4',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `post_balance` double(25,5) NOT NULL DEFAULT '0.00000',
  `charge` double(25,5) NOT NULL DEFAULT '0.00000',
  `final_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `trx_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trx_type` enum('+','-') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '+ = plus , - = minus',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `translations`
--

INSERT INTO `translations` (`id`, `uid`, `code`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, '19b1984d-df47-4aef-8c7f-902aa40c92fa', 'en', 'login', 'Login', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(2, 'a2fae6d1-1775-45a8-9904-4f545b4bd47a', 'en', 'welcome_back_please_login_to_your_account', 'Welcome back! Please login to your account.', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(3, '8eec343b-f8da-4d55-a3a1-e541cde590e6', 'en', 'usernameemail', 'Username/Email', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(4, '426fc055-5215-4412-99d4-6e769e25128f', 'en', 'enter_username_or_email', 'Enter Username or email', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(5, 'bbdfde30-f1c7-4db1-b742-b211b90d5256', 'en', 'password', 'Password', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(6, 'c71c7e55-e9e2-44a0-b9e9-b84d76eafea6', 'en', 'remember_me', 'Remember me', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(7, 'eab66360-01f5-40eb-8c9a-00b956bd5114', 'en', 'sign_in', 'Sign In', '2025-08-17 02:56:37', '2025-08-17 02:56:37'),
(8, 'bc572206-b50c-4a36-a047-d011f0ac470d', 'en', 'forgot_password', 'Forgot password', '2025-08-17 02:56:37', '2025-08-17 02:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `referral_id` bigint UNSIGNED DEFAULT NULL,
  `referral_code` mediumint DEFAULT NULL,
  `auto_subscription_by` bigint DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `country_id` bigint UNSIGNED DEFAULT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `o_auth_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` double(20,2) NOT NULL DEFAULT '0.00',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_settings` longtext COLLATE utf8mb4_unicode_ci,
  `settings` longtext COLLATE utf8mb4_unicode_ci,
  `address` longtext COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Deactive: 0',
  `auto_subscription` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Off: 0, On: 1',
  `is_kyc_verified` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `custom_data` longtext COLLATE utf8mb4_unicode_ci,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `country_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `times_visited` int NOT NULL DEFAULT '1',
  `agent_info` longtext COLLATE utf8mb4_unicode_ci,
  `is_blocked` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Yes: 1, No: 0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdraws`
--

CREATE TABLE `withdraws` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL COMMENT 'Hours',
  `minimum_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `maximum_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Active: 1, Inactive: 0',
  `fixed_charge` double(25,5) NOT NULL DEFAULT '0.00000',
  `percent_charge` double(25,5) NOT NULL DEFAULT '0.00000',
  `note` text COLLATE utf8mb4_unicode_ci,
  `parameters` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_logs`
--

CREATE TABLE `withdraw_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `method_id` bigint UNSIGNED DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `trx_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `base_charge` double(25,5) NOT NULL DEFAULT '0.00000',
  `charge` double(25,5) NOT NULL DEFAULT '0.00000',
  `base_final_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `final_amount` double(25,5) NOT NULL DEFAULT '0.00000',
  `custom_data` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Pending : 3,Approved : 1 ,Rejected:2',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_username_unique` (`username`),
  ADD UNIQUE KEY `admins_email_unique` (`email`),
  ADD KEY `admins_created_by_index` (`created_by`),
  ADD KEY `admins_updated_by_index` (`updated_by`),
  ADD KEY `admins_uid_index` (`uid`),
  ADD KEY `admins_role_id_index` (`role_id`),
  ADD KEY `admins_phone_index` (`phone`);

--
-- Indexes for table `affiliate_logs`
--
ALTER TABLE `affiliate_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_logs_user_id_index` (`user_id`),
  ADD KEY `affiliate_logs_referred_to_index` (`referred_to`),
  ADD KEY `affiliate_logs_subscription_id_index` (`subscription_id`);

--
-- Indexes for table `ai_templates`
--
ALTER TABLE `ai_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_templates_name_unique` (`name`),
  ADD UNIQUE KEY `ai_templates_slug_unique` (`slug`),
  ADD KEY `ai_templates_uid_index` (`uid`),
  ADD KEY `ai_templates_category_id_index` (`category_id`),
  ADD KEY `ai_templates_sub_category_id_index` (`sub_category_id`),
  ADD KEY `ai_templates_user_id_index` (`user_id`),
  ADD KEY `ai_templates_admin_id_index` (`admin_id`),
  ADD KEY `ai_templates_status_index` (`status`),
  ADD KEY `ai_templates_is_default_index` (`is_default`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blogs_title_unique` (`title`),
  ADD UNIQUE KEY `blogs_slug_unique` (`slug`),
  ADD KEY `blogs_uid_index` (`uid`),
  ADD KEY `blogs_created_by_index` (`created_by`),
  ADD KEY `blogs_updated_by_index` (`updated_by`),
  ADD KEY `blogs_category_id_index` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_uid_index` (`uid`),
  ADD KEY `categories_parent_id_index` (`parent_id`),
  ADD KEY `categories_created_by_index` (`created_by`),
  ADD KEY `categories_updated_by_index` (`updated_by`),
  ADD KEY `categories_display_in_index` (`display_in`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_uid_index` (`uid`);

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contents_name_unique` (`name`),
  ADD UNIQUE KEY `contents_slug_unique` (`slug`),
  ADD KEY `contents_uid_index` (`uid`),
  ADD KEY `contents_user_id_index` (`user_id`),
  ADD KEY `contents_admin_id_index` (`admin_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `countries_name_unique` (`name`),
  ADD UNIQUE KEY `countries_code_unique` (`code`),
  ADD KEY `countries_updated_by_index` (`updated_by`),
  ADD KEY `countries_uid_index` (`uid`);

--
-- Indexes for table `credit_logs`
--
ALTER TABLE `credit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credit_logs_subscription_id_index` (`subscription_id`),
  ADD KEY `credit_logs_user_id_index` (`user_id`),
  ADD KEY `credit_logs_trx_code_index` (`trx_code`),
  ADD KEY `credit_logs_type_index` (`type`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `currencies_name_unique` (`name`),
  ADD UNIQUE KEY `currencies_code_unique` (`code`),
  ADD KEY `currencies_uid_index` (`uid`),
  ADD KEY `currencies_created_by_index` (`created_by`),
  ADD KEY `currencies_updated_by_index` (`updated_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `files_fileable_type_fileable_id_index` (`fileable_type`,`fileable_id`),
  ADD KEY `files_name_index` (`name`),
  ADD KEY `files_disk_index` (`disk`),
  ADD KEY `files_type_index` (`type`);

--
-- Indexes for table `firewall_ips`
--
ALTER TABLE `firewall_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `firewall_ips_ip_index` (`ip`);

--
-- Indexes for table `firewall_logs`
--
ALTER TABLE `firewall_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `firewall_logs_ip_index` (`ip`);

--
-- Indexes for table `frontends`
--
ALTER TABLE `frontends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frontends_uid_index` (`uid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `kyc_logs`
--
ALTER TABLE `kyc_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kyc_logs_user_id_index` (`user_id`),
  ADD KEY `kyc_logs_admin_id_index` (`admin_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `languages_name_unique` (`name`),
  ADD KEY `languages_uid_index` (`uid`),
  ADD KEY `languages_created_by_index` (`created_by`),
  ADD KEY `languages_updated_by_index` (`updated_by`);

--
-- Indexes for table `mail_gateways`
--
ALTER TABLE `mail_gateways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mail_gateways_uid_index` (`uid`),
  ADD KEY `mail_gateways_created_by_index` (`created_by`),
  ADD KEY `mail_gateways_updated_by_index` (`updated_by`);

--
-- Indexes for table `media_platforms`
--
ALTER TABLE `media_platforms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_platforms_uid_index` (`uid`),
  ADD KEY `media_platforms_name_index` (`name`),
  ADD KEY `media_platforms_slug_index` (`slug`),
  ADD KEY `media_platforms_status_index` (`status`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menus_uid_index` (`uid`),
  ADD KEY `menus_serial_id_index` (`serial_id`),
  ADD KEY `menus_created_by_index` (`created_by`),
  ADD KEY `menus_updated_by_index` (`updated_by`),
  ADD KEY `menus_name_index` (`name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_admin_id_index` (`admin_id`),
  ADD KEY `messages_ticket_id_index` (`ticket_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_translations`
--
ALTER TABLE `model_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_translations_translateable_type_translateable_id_index` (`translateable_type`,`translateable_id`),
  ADD KEY `model_translations_locale_index` (`locale`),
  ADD KEY `model_translations_key_index` (`key`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notificationable_type_notificationable_id_index` (`notificationable_type`,`notificationable_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otps_otpable_type_otpable_id_index` (`otpable_type`,`otpable_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `packages_title_unique` (`title`),
  ADD UNIQUE KEY `packages_slug_unique` (`slug`),
  ADD KEY `packages_uid_index` (`uid`),
  ADD KEY `packages_created_by_index` (`created_by`),
  ADD KEY `packages_updated_by_index` (`updated_by`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_uid_index` (`uid`),
  ADD KEY `pages_serial_id_index` (`serial_id`),
  ADD KEY `pages_created_by_index` (`created_by`),
  ADD KEY `pages_updated_by_index` (`updated_by`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_logs_user_id_index` (`user_id`),
  ADD KEY `payment_logs_method_id_index` (`method_id`),
  ADD KEY `payment_logs_currency_id_index` (`currency_id`),
  ADD KEY `payment_logs_trx_code_index` (`trx_code`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_methods_name_unique` (`name`),
  ADD UNIQUE KEY `payment_methods_code_unique` (`code`),
  ADD KEY `payment_methods_uid_index` (`uid`),
  ADD KEY `payment_methods_serial_id_index` (`serial_id`),
  ADD KEY `payment_methods_created_by_index` (`created_by`),
  ADD KEY `payment_methods_updated_by_index` (`updated_by`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `post_webhook_logs`
--
ALTER TABLE `post_webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_webhook_logs_uid_index` (`uid`),
  ADD KEY `post_webhook_logs_user_id_index` (`user_id`),
  ADD KEY `post_webhook_logs_admin_id_index` (`admin_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `roles_uid_index` (`uid`),
  ADD KEY `roles_created_by_index` (`created_by`),
  ADD KEY `roles_updated_by_index` (`updated_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_uid_index` (`uid`),
  ADD KEY `settings_key_index` (`key`);

--
-- Indexes for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_gateways_uid_index` (`uid`),
  ADD KEY `sms_gateways_created_by_index` (`created_by`),
  ADD KEY `sms_gateways_updated_by_index` (`updated_by`);

--
-- Indexes for table `social_accounts`
--
ALTER TABLE `social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_accounts_uid_index` (`uid`),
  ADD KEY `social_accounts_platform_id_index` (`platform_id`),
  ADD KEY `social_accounts_subscription_id_index` (`subscription_id`),
  ADD KEY `social_accounts_user_id_index` (`user_id`),
  ADD KEY `social_accounts_admin_id_index` (`admin_id`),
  ADD KEY `social_accounts_name_index` (`name`),
  ADD KEY `social_accounts_account_id_index` (`account_id`),
  ADD KEY `social_accounts_status_index` (`status`);

--
-- Indexes for table `social_posts`
--
ALTER TABLE `social_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_posts_id_index` (`id`),
  ADD KEY `social_posts_uid_index` (`uid`),
  ADD KEY `social_posts_account_id_index` (`account_id`),
  ADD KEY `social_posts_platform_id_index` (`platform_id`),
  ADD KEY `social_posts_subscription_id_index` (`subscription_id`),
  ADD KEY `social_posts_user_id_index` (`user_id`),
  ADD KEY `social_posts_admin_id_index` (`admin_id`),
  ADD KEY `social_posts_is_scheduled_index` (`is_scheduled`),
  ADD KEY `social_posts_status_index` (`status`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscribers_uid_index` (`uid`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_user_id_index` (`user_id`),
  ADD KEY `subscriptions_admin_id_index` (`admin_id`),
  ADD KEY `subscriptions_package_id_index` (`package_id`),
  ADD KEY `subscriptions_old_package_id_index` (`old_package_id`),
  ADD KEY `subscriptions_status_index` (`status`),
  ADD KEY `subscriptions_payment_status_index` (`payment_status`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `templates_uid_index` (`uid`),
  ADD KEY `templates_created_by_index` (`created_by`),
  ADD KEY `templates_updated_by_index` (`updated_by`),
  ADD KEY `templates_name_index` (`name`),
  ADD KEY `templates_slug_index` (`slug`);

--
-- Indexes for table `template_usages`
--
ALTER TABLE `template_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_usages_template_id_index` (`template_id`),
  ADD KEY `template_usages_user_id_index` (`user_id`),
  ADD KEY `template_usages_admin_id_index` (`admin_id`),
  ADD KEY `template_usages_package_id_index` (`package_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tickets_ticket_number_unique` (`ticket_number`),
  ADD KEY `tickets_user_id_foreign` (`user_id`),
  ADD KEY `tickets_id_index` (`id`),
  ADD KEY `tickets_uid_index` (`uid`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_user_id_index` (`user_id`),
  ADD KEY `transactions_admin_id_index` (`admin_id`),
  ADD KEY `transactions_currency_id_index` (`currency_id`),
  ADD KEY `transactions_trx_code_index` (`trx_code`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translations_uid_index` (`uid`),
  ADD KEY `translations_code_index` (`code`),
  ADD KEY `translations_key_index` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_referral_id_index` (`referral_id`),
  ADD KEY `users_referral_code_index` (`referral_code`),
  ADD KEY `users_auto_subscription_by_index` (`auto_subscription_by`),
  ADD KEY `users_created_by_index` (`created_by`),
  ADD KEY `users_updated_by_index` (`updated_by`),
  ADD KEY `users_country_id_index` (`country_id`),
  ADD KEY `users_uid_index` (`uid`),
  ADD KEY `users_name_index` (`name`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_is_kyc_verified_index` (`is_kyc_verified`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visitors_created_by_foreign` (`created_by`),
  ADD KEY `visitors_updated_by_foreign` (`updated_by`),
  ADD KEY `visitors_id_index` (`id`),
  ADD KEY `visitors_uid_index` (`uid`);

--
-- Indexes for table `withdraws`
--
ALTER TABLE `withdraws`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `withdraws_name_unique` (`name`),
  ADD KEY `withdraws_uid_index` (`uid`),
  ADD KEY `withdraws_created_by_index` (`created_by`),
  ADD KEY `withdraws_updated_by_index` (`updated_by`);

--
-- Indexes for table `withdraw_logs`
--
ALTER TABLE `withdraw_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdraw_logs_method_id_index` (`method_id`),
  ADD KEY `withdraw_logs_currency_id_index` (`currency_id`),
  ADD KEY `withdraw_logs_user_id_index` (`user_id`),
  ADD KEY `withdraw_logs_trx_code_index` (`trx_code`),
  ADD KEY `withdraw_logs_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `affiliate_logs`
--
ALTER TABLE `affiliate_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_templates`
--
ALTER TABLE `ai_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT for table `credit_logs`
--
ALTER TABLE `credit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firewall_ips`
--
ALTER TABLE `firewall_ips`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firewall_logs`
--
ALTER TABLE `firewall_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `frontends`
--
ALTER TABLE `frontends`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_logs`
--
ALTER TABLE `kyc_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mail_gateways`
--
ALTER TABLE `mail_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `media_platforms`
--
ALTER TABLE `media_platforms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `model_translations`
--
ALTER TABLE `model_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_webhook_logs`
--
ALTER TABLE `post_webhook_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `social_accounts`
--
ALTER TABLE `social_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_posts`
--
ALTER TABLE `social_posts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `template_usages`
--
ALTER TABLE `template_usages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdraws`
--
ALTER TABLE `withdraws`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdraw_logs`
--
ALTER TABLE `withdraw_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `visitors`
--
ALTER TABLE `visitors`
  ADD CONSTRAINT `visitors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `visitors_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
