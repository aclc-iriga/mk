-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2023 at 07:05 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mk`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `pinged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `number`, `name`, `avatar`, `username`, `password`, `called_at`, `pinged_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'DEVELOPMENT', 'no-avatar.jpg', 'admin', 'admin', NULL, NULL, '2023-02-19 07:36:32', '2023-05-22 13:34:27');

-- --------------------------------------------------------

--
-- Table structure for table `arrangements`
--

CREATE TABLE `arrangements` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `team_id` tinyint(3) UNSIGNED NOT NULL,
  `order` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `competition_id` tinyint(3) UNSIGNED NOT NULL,
  `slug` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `competition_id`, `slug`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 'preliminaries', 'Preliminaries', '2023-04-06 13:25:10', '2023-05-13 12:54:45'),
(2, 1, 'prejudging', 'Prejudging', '2023-05-22 09:08:23', '2023-05-22 09:08:23'),
(3, 1, 'pageant-night', 'Pageant Night', '2023-05-22 13:28:11', '2023-05-22 13:28:11');

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `slug` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `slug`, `title`, `created_at`, `updated_at`) VALUES
(1, 'mk-2023', 'Miss Kaogma 2023', '2023-04-06 13:24:04', '2023-04-15 02:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `criteria`
--

CREATE TABLE `criteria` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `percentage` float UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `criteria`
--

INSERT INTO `criteria` (`id`, `event_id`, `title`, `percentage`, `created_at`, `updated_at`) VALUES
(1, 1, 'Originality, Theme Relevance, Design & Aesthetics', 40, '2023-05-13 12:58:46', '2023-05-17 07:24:13'),
(2, 1, 'Stage Presence', 40, '2023-05-13 12:59:58', '2023-05-17 07:37:08'),
(3, 1, 'Craftsmanship', 20, '2023-05-13 12:59:02', '2023-05-17 07:37:12'),
(4, 2, 'Beauty of the Face', 40, '2023-05-22 09:09:14', '2023-05-22 09:09:14'),
(5, 2, 'Figure', 30, '2023-05-22 09:09:26', '2023-05-22 09:09:26'),
(6, 2, 'Personality / Intelligence', 30, '2023-05-22 09:09:39', '2023-05-22 09:09:39'),
(7, 3, 'Beauty of the Face', 40, '2023-05-22 13:29:14', '2023-05-22 13:29:14'),
(8, 3, 'Figure', 30, '2023-05-22 13:29:24', '2023-05-22 13:29:24'),
(9, 4, 'Rating', 100, '2023-05-22 13:51:00', '2023-05-22 13:51:00'),
(10, 5, 'Intelligence', 30, '2023-05-22 14:57:01', '2023-05-22 14:57:01'),
(11, 6, 'Beauty and Stage Presence', 60, '2023-05-22 16:52:42', '2023-05-22 16:52:42'),
(12, 6, 'Intelligence', 40, '2023-05-22 16:52:54', '2023-05-22 16:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `technical_id` tinyint(3) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `team_id` tinyint(3) UNSIGNED NOT NULL,
  `value` float UNSIGNED NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eliminations`
--

CREATE TABLE `eliminations` (
  `id` mediumint(9) NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `team_id` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eliminations`
--

INSERT INTO `eliminations` (`id`, `event_id`, `team_id`, `created_at`, `updated_at`) VALUES
(1, 1, 13, '2023-05-21 06:00:21', '2023-05-21 06:00:21'),
(2, 5, 1, '2023-05-22 14:57:20', '2023-05-22 14:57:20'),
(3, 5, 2, '2023-05-22 14:57:21', '2023-05-22 14:57:21'),
(4, 5, 3, '2023-05-22 14:57:22', '2023-05-22 14:57:22'),
(5, 5, 4, '2023-05-22 14:57:24', '2023-05-22 14:57:24'),
(6, 5, 5, '2023-05-22 14:57:24', '2023-05-22 14:57:24'),
(7, 5, 6, '2023-05-22 14:57:25', '2023-05-22 14:57:25'),
(8, 5, 7, '2023-05-22 14:57:27', '2023-05-22 14:57:27'),
(9, 5, 8, '2023-05-22 14:57:27', '2023-05-22 14:57:27'),
(10, 5, 9, '2023-05-22 14:57:28', '2023-05-22 14:57:28'),
(11, 5, 10, '2023-05-22 14:57:29', '2023-05-22 14:57:29'),
(12, 5, 11, '2023-05-22 14:57:30', '2023-05-22 14:57:30'),
(13, 5, 12, '2023-05-22 14:57:31', '2023-05-22 14:57:31'),
(14, 5, 13, '2023-05-22 14:57:32', '2023-05-22 14:57:32'),
(15, 5, 14, '2023-05-22 14:57:33', '2023-05-22 14:57:33'),
(16, 5, 15, '2023-05-22 14:57:34', '2023-05-22 14:57:34'),
(17, 5, 16, '2023-05-22 14:57:35', '2023-05-22 14:57:35'),
(18, 5, 17, '2023-05-22 14:57:36', '2023-05-22 14:57:36'),
(19, 5, 18, '2023-05-22 14:57:37', '2023-05-22 14:57:37'),
(20, 5, 19, '2023-05-22 14:57:39', '2023-05-22 14:57:39'),
(21, 5, 20, '2023-05-22 14:57:40', '2023-05-22 14:57:40'),
(22, 6, 1, '2023-05-22 16:56:26', '2023-05-22 16:56:26'),
(23, 6, 2, '2023-05-22 16:56:27', '2023-05-22 16:56:27'),
(24, 6, 3, '2023-05-22 16:56:28', '2023-05-22 16:56:28'),
(25, 6, 4, '2023-05-22 16:56:29', '2023-05-22 16:56:29'),
(26, 6, 5, '2023-05-22 16:56:30', '2023-05-22 16:56:30'),
(27, 6, 6, '2023-05-22 16:56:32', '2023-05-22 16:56:32'),
(28, 6, 7, '2023-05-22 16:56:33', '2023-05-22 16:56:33'),
(29, 6, 8, '2023-05-22 16:56:34', '2023-05-22 16:56:34'),
(30, 6, 9, '2023-05-22 16:56:35', '2023-05-22 16:56:35'),
(31, 6, 10, '2023-05-22 16:56:36', '2023-05-22 16:56:36'),
(32, 6, 11, '2023-05-22 16:56:37', '2023-05-22 16:56:37'),
(33, 6, 12, '2023-05-22 16:56:38', '2023-05-22 16:56:38'),
(34, 6, 13, '2023-05-22 16:56:39', '2023-05-22 16:56:39'),
(35, 6, 14, '2023-05-22 16:56:40', '2023-05-22 16:56:40'),
(36, 6, 15, '2023-05-22 16:56:41', '2023-05-22 16:56:41'),
(37, 6, 16, '2023-05-22 16:56:42', '2023-05-22 16:56:42'),
(38, 6, 17, '2023-05-22 16:56:43', '2023-05-22 16:56:43'),
(39, 6, 18, '2023-05-22 16:56:44', '2023-05-22 16:56:44'),
(40, 6, 19, '2023-05-22 16:56:45', '2023-05-22 16:56:45'),
(41, 6, 20, '2023-05-22 16:56:46', '2023-05-22 16:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `category_id` tinyint(3) UNSIGNED NOT NULL,
  `slug` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `category_id`, `slug`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 'kaogma-festival-costume', 'Kaogma Festival Costume', '2023-05-13 12:57:42', '2023-05-17 03:23:01'),
(2, 2, 'prejudging', 'Prejudging', '2023-05-22 09:08:43', '2023-05-22 09:08:43'),
(3, 3, 'swimsuit', 'Swimsuit', '2023-05-22 13:28:40', '2023-05-22 13:28:40'),
(4, 3, 'evening-gown', 'Evening Gown', '2023-05-22 13:49:05', '2023-05-22 13:49:05'),
(5, 3, 'top10-qa', 'Top 10 Q&A', '2023-05-22 14:56:45', '2023-05-22 14:56:45'),
(6, 3, 'top5-qa', 'Top 5 Q&A', '2023-05-22 16:51:24', '2023-05-22 16:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `judges`
--

CREATE TABLE `judges` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `pinged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `judges`
--

INSERT INTO `judges` (`id`, `number`, `name`, `avatar`, `username`, `password`, `called_at`, `pinged_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Judge 01', 'no-avatar.jpg', 'judge01', 'judge01', NULL, NULL, '2023-04-06 13:58:11', '2023-05-13 13:03:04'),
(2, 2, 'Judge 02', 'no-avatar.jpg', 'judge02', 'judge02', NULL, NULL, '2023-04-06 13:58:28', '2023-05-13 13:03:06'),
(3, 3, 'Judge 03', 'no-avatar.jpg', 'judge03', 'judge03', NULL, NULL, '2023-04-06 13:58:42', '2023-05-13 13:03:10'),
(4, 4, 'Judge 04', 'no-avatar.jpg', 'judge04', 'judge04', NULL, NULL, '2023-05-18 03:27:54', '2023-05-18 03:27:54'),
(5, 1, 'Pageant 01', 'no-avatar.jpg', 'pageant01', 'pageant01', NULL, NULL, '2023-05-22 09:06:05', '2023-05-22 09:06:05'),
(6, 2, 'Pageant 02', 'no-avatar.jpg', 'pageant02', 'pageant02', NULL, NULL, '2023-05-22 09:06:22', '2023-05-22 09:06:22'),
(7, 3, 'Pageant 03', 'no-avatar.jpg', 'pageant03', 'pageant03', NULL, NULL, '2023-05-22 09:06:43', '2023-05-22 09:06:43'),
(8, 4, 'Pageant 04', 'no-avatar.jpg', 'pageant04', 'pageant04', NULL, NULL, '2023-05-22 09:07:00', '2023-05-22 09:07:00'),
(9, 5, 'Pageant 05', 'no-avatar.jpg', 'pageant05', 'pageant05', NULL, NULL, '2023-05-22 09:07:15', '2023-05-22 09:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `judge_event`
--

CREATE TABLE `judge_event` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `judge_id` tinyint(3) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `is_chairman` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `judge_event`
--

INSERT INTO `judge_event` (`id`, `judge_id`, `event_id`, `is_chairman`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, '2023-05-13 13:05:35', '2023-05-13 13:05:35'),
(2, 2, 1, 0, '2023-05-13 13:05:39', '2023-05-13 13:05:39'),
(3, 3, 1, 0, '2023-05-13 13:05:43', '2023-05-13 13:05:43'),
(4, 4, 1, 0, '2023-05-18 03:28:13', '2023-05-18 03:28:13'),
(5, 5, 2, 0, '2023-05-22 09:10:15', '2023-05-22 09:10:15'),
(6, 6, 2, 0, '2023-05-22 09:10:20', '2023-05-22 09:10:20'),
(7, 7, 2, 0, '2023-05-22 09:10:26', '2023-05-22 09:10:26'),
(8, 8, 2, 0, '2023-05-22 09:10:32', '2023-05-22 09:10:32'),
(9, 9, 2, 0, '2023-05-22 09:10:36', '2023-05-22 09:10:36'),
(10, 5, 3, 0, '2023-05-22 13:30:08', '2023-05-22 13:30:08'),
(11, 6, 3, 0, '2023-05-22 13:30:13', '2023-05-22 13:30:13'),
(12, 7, 3, 0, '2023-05-22 13:30:19', '2023-05-22 13:30:19'),
(13, 8, 3, 0, '2023-05-22 13:30:23', '2023-05-22 13:30:23'),
(14, 9, 3, 0, '2023-05-22 13:30:28', '2023-05-22 13:30:28'),
(15, 5, 4, 0, '2023-05-22 13:52:55', '2023-05-22 13:52:55'),
(16, 6, 4, 0, '2023-05-22 13:53:00', '2023-05-22 13:53:00'),
(17, 7, 4, 0, '2023-05-22 13:53:05', '2023-05-22 13:53:05'),
(18, 8, 4, 0, '2023-05-22 13:53:10', '2023-05-22 13:53:10'),
(19, 9, 4, 0, '2023-05-22 13:53:15', '2023-05-22 13:53:15'),
(20, 5, 5, 0, '2023-05-22 14:58:31', '2023-05-22 14:58:31'),
(21, 6, 5, 0, '2023-05-22 14:58:37', '2023-05-22 14:58:37'),
(22, 7, 5, 0, '2023-05-22 14:58:43', '2023-05-22 14:58:43'),
(23, 8, 5, 0, '2023-05-22 14:58:48', '2023-05-22 14:58:48'),
(24, 9, 5, 0, '2023-05-22 14:58:52', '2023-05-22 14:58:52'),
(25, 5, 6, 0, '2023-05-22 16:53:15', '2023-05-22 16:53:15'),
(26, 6, 6, 0, '2023-05-22 16:53:20', '2023-05-22 16:53:20'),
(27, 7, 6, 0, '2023-05-22 16:53:34', '2023-05-22 16:53:34'),
(28, 8, 6, 0, '2023-05-22 16:53:41', '2023-05-22 16:53:41'),
(29, 9, 6, 0, '2023-05-22 16:53:47', '2023-05-22 16:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `noshows`
--

CREATE TABLE `noshows` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `team_id` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `noshows`
--

INSERT INTO `noshows` (`id`, `event_id`, `team_id`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2023-05-22 13:31:59', '2023-05-22 13:31:59'),
(2, 3, 2, '2023-05-22 13:32:00', '2023-05-22 13:32:00'),
(3, 3, 3, '2023-05-22 13:32:02', '2023-05-22 13:32:02'),
(4, 3, 4, '2023-05-22 13:32:03', '2023-05-22 13:32:03'),
(5, 3, 5, '2023-05-22 13:32:05', '2023-05-22 13:32:05'),
(6, 3, 6, '2023-05-22 13:32:06', '2023-05-22 13:32:06'),
(7, 3, 7, '2023-05-22 13:32:07', '2023-05-22 13:32:07'),
(8, 3, 8, '2023-05-22 13:32:09', '2023-05-22 13:32:09'),
(9, 3, 9, '2023-05-22 13:32:10', '2023-05-22 13:32:10'),
(10, 3, 10, '2023-05-22 13:32:11', '2023-05-22 13:32:11'),
(11, 3, 11, '2023-05-22 13:32:13', '2023-05-22 13:32:13'),
(12, 3, 12, '2023-05-22 13:32:14', '2023-05-22 13:32:14'),
(13, 3, 13, '2023-05-22 13:32:15', '2023-05-22 13:32:15'),
(14, 3, 14, '2023-05-22 13:32:16', '2023-05-22 13:32:16'),
(15, 3, 15, '2023-05-22 13:32:18', '2023-05-22 13:32:18'),
(16, 3, 16, '2023-05-22 13:32:19', '2023-05-22 13:32:19'),
(17, 3, 17, '2023-05-22 13:32:20', '2023-05-22 13:32:20'),
(18, 3, 18, '2023-05-22 13:32:21', '2023-05-22 13:32:21'),
(19, 3, 19, '2023-05-22 13:32:23', '2023-05-22 13:32:23'),
(20, 3, 20, '2023-05-22 13:32:24', '2023-05-22 13:32:24');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `team_id` tinyint(3) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `number` smallint(5) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

CREATE TABLE `points` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `rank` tinyint(3) UNSIGNED NOT NULL,
  `value` float UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `judge_id` tinyint(3) UNSIGNED NOT NULL,
  `criteria_id` smallint(5) UNSIGNED NOT NULL,
  `team_id` tinyint(3) UNSIGNED NOT NULL,
  `value` float UNSIGNED NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `number` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `location` varchar(64) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `number`, `name`, `location`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 1, 'CHRISTINE OVILLA', 'NAGA CITY', '01-christine-ovilla.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(2, 2, 'MARGA JOYCE SAYSON', 'LIBON, ALBAY', '02-marga-joyce-sayson.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(3, 3, 'KAYE PAULINE SERVIDAD', 'CAMALIGAN, CAMARINES SUR', '03-kaye-pauline-servidad.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(4, 4, 'NEOLI KRYSS ANGELINE ABARIENTOS', 'NABUA, CAMARINES SUR', '04-neoli-kryss-angeline-abarientos.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(5, 5, 'HANNAH MAE PANIBE', 'CALABANGA, CAMARINES SUR', '05-hannah-mae-panibe.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(6, 6, 'RUFFA MAE ARMILLOS', 'NABUA, CAMARINES SUR', '06-ruffa-mae-armillos.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(7, 7, 'TRIZIA MARIE ABONITA', 'SIPOCOT, CAMARINES SUR', '07-trizia-marie-abonita.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(8, 8, 'CHARLENE BOHOLANO', 'LEGAZPI CITY', '08-charlene-boholano.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(9, 9, 'MARY JOY DARILAY', 'NAGA CITY', '09-mary-joy-darilay.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(10, 10, 'SHAINA RABACAL', 'BUHI, CAMARINES SUR', '10-shaina-rabacal.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(11, 11, 'ANGELA SHERIZA TINO', 'BATO, CAMARINES SUR', '11-angela-sheriza-tino.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(12, 12, 'REINSFER KHRIZETTE RANARA', 'NAGA CITY', '12-reinsfer-khrizette-ranara.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(13, 13, 'CHRISTINE ARNEDO', 'LEGAZPI CITY', '13-christine-arnedo.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(14, 14, 'IRIS ORESCA', 'NAGA CITY', '14-iris-oresca.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(15, 15, 'ALYSSA MILDRED VILLARIÑA', 'TABACO CITY', '15-alyssa-mildred-villariña.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(16, 16, 'KATRINA CLAUDIA JAMIN', 'IRIGA CITY', '16-katrina-claudia-jamin.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(17, 17, 'TRISHIA BARNEDO', 'PASACAO, CAMARINES SUR', '17-trishia-barnedo.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(18, 18, 'MARIA YSABELLA FRANCESCA SAPIENZA', 'LEGAZPI CITY', '18-maria-ysabella-francesca-sapienza.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(19, 19, 'SEANNEL ADDERIE CRUZ', 'GUINOBATAN, ALBAY', '19-seannel-adderie-cruz.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37'),
(20, 20, 'MARIA PAULA BATALLA', 'CALABANGA, CAMARINES SUR', '20-maria-paula-batalla.jpg', '2023-05-13 12:53:37', '2023-05-13 12:53:37');

-- --------------------------------------------------------

--
-- Table structure for table `technicals`
--

CREATE TABLE `technicals` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `pinged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technicals`
--

INSERT INTO `technicals` (`id`, `number`, `name`, `avatar`, `username`, `password`, `called_at`, `pinged_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Technical 01', 'no-avatar.jpg', 'technical01', 'technical01', NULL, NULL, '2023-02-19 08:58:58', '2023-04-06 14:00:12');

-- --------------------------------------------------------

--
-- Table structure for table `technical_event`
--

CREATE TABLE `technical_event` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `technical_id` tinyint(3) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

CREATE TABLE `titles` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `rank` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `titles`
--

INSERT INTO `titles` (`id`, `event_id`, `rank`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Best in Kaogma Festival Costume', '2023-05-13 13:05:01', '2023-05-17 03:25:05'),
(2, 4, 1, 'Best in Evening Gown', '2023-05-22 13:57:02', '2023-05-22 13:57:02'),
(3, 6, 1, 'MISS KAOGMA 2023', '2023-05-22 17:01:37', '2023-05-22 17:01:37'),
(4, 6, 2, 'MISS CAMARINES SUR', '2023-05-22 17:01:37', '2023-05-22 17:05:09'),
(5, 6, 3, 'MISS TOURISM', '2023-05-22 17:01:37', '2023-05-22 17:05:13'),
(6, 6, 4, '1st RUNNER UP', '2023-05-22 17:01:37', '2023-05-22 17:01:37'),
(7, 6, 5, '2nd RUNNER UP', '2023-05-22 17:01:37', '2023-05-22 17:01:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `arrangements`
--
ALTER TABLE `arrangements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `criteria`
--
ALTER TABLE `criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `judge_id` (`technical_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `eliminations`
--
ALTER TABLE `eliminations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_id` (`category_id`);

--
-- Indexes for table `judges`
--
ALTER TABLE `judges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `judge_event`
--
ALTER TABLE `judge_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `judge_id` (`judge_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `noshows`
--
ALTER TABLE `noshows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `judge_id` (`judge_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technicals`
--
ALTER TABLE `technicals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technical_event`
--
ALTER TABLE `technical_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `judge_id` (`technical_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `titles`
--
ALTER TABLE `titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `arrangements`
--
ALTER TABLE `arrangements`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `criteria`
--
ALTER TABLE `criteria`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eliminations`
--
ALTER TABLE `eliminations`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `judges`
--
ALTER TABLE `judges`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `judge_event`
--
ALTER TABLE `judge_event`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `noshows`
--
ALTER TABLE `noshows`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `points`
--
ALTER TABLE `points`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `technicals`
--
ALTER TABLE `technicals`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `technical_event`
--
ALTER TABLE `technical_event`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `titles`
--
ALTER TABLE `titles`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `arrangements`
--
ALTER TABLE `arrangements`
  ADD CONSTRAINT `arrangements_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `arrangements_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `criteria`
--
ALTER TABLE `criteria`
  ADD CONSTRAINT `criteria_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `deductions`
--
ALTER TABLE `deductions`
  ADD CONSTRAINT `deductions_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `deductions_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `deductions_ibfk_3` FOREIGN KEY (`technical_id`) REFERENCES `technicals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eliminations`
--
ALTER TABLE `eliminations`
  ADD CONSTRAINT `eliminations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eliminations_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `judge_event`
--
ALTER TABLE `judge_event`
  ADD CONSTRAINT `judge_event_ibfk_1` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `judge_event_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `noshows`
--
ALTER TABLE `noshows`
  ADD CONSTRAINT `noshows_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `noshows_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `points`
--
ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`criteria_id`) REFERENCES `criteria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `technical_event`
--
ALTER TABLE `technical_event`
  ADD CONSTRAINT `technical_event_ibfk_2` FOREIGN KEY (`technical_id`) REFERENCES `technicals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `technical_event_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `titles`
--
ALTER TABLE `titles`
  ADD CONSTRAINT `titles_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
