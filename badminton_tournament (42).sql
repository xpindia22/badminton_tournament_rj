-- phpMyAdmin SQL Dump
-- version 5.2.1deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 21, 2025 at 05:38 PM
-- Server version: 11.4.3-MariaDB-1
-- PHP Version: 8.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `badminton_tournament`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `field_changed` varchar(255) NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `age_group` varchar(255) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `type` enum('singles','doubles','mixed doubles') DEFAULT 'singles',
  `tournament_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_by`, `age_group`, `sex`, `type`, `tournament_id`) VALUES
(1, 'U17BS', 1, 'Under 17', 'M', 'singles', 0),
(2, 'U15BS', 1, 'Under 15', 'M', 'singles', 0),
(3, 'U15BD', 1, 'Under 15', 'M', 'doubles', 0),
(4, 'U17BD', 2, 'Under 17', 'M', 'doubles', 0),
(6, 'U15GS', 4, 'Under 15', 'F', 'singles', 0),
(7, 'U15GD', 1, 'Under 15', 'F', 'doubles', 0),
(8, 'U13BS', 1, 'Under 13', 'M', 'singles', 0),
(11, 'Open BS', 4, 'Between 5 - 100', 'M', 'singles', 0),
(12, 'Open GS', 4, 'Between 5 - 100', 'F', 'singles', 0),
(13, 'Open XD', 4, 'Between 5 - 100', 'Mixed', 'mixed doubles', 0),
(14, 'Open BD', 4, 'Between 5 - 100', 'M', 'doubles', 0),
(15, 'Open GD', 4, 'Between 5 - 100', 'F', 'doubles', 0),
(16, 'U17GS', 4, 'Under 17', 'F', 'singles', 0),
(17, 'U17GD', 4, 'Under 17', 'F', 'doubles', 0),
(18, 'U17GD', 4, 'Under 17', 'F', 'doubles', 0),
(19, 'U13GS', 4, 'Under 13', 'F', 'singles', 0),
(20, 'Senior 40 Plus BS', 4, 'Over 40', 'M', 'singles', 0),
(21, 'Senior 40 Plus BD', 4, 'Over 40', 'M', 'doubles', 0),
(22, 'Senior 40 Plus GS', 4, 'Over 40', 'F', 'singles', 0),
(23, 'Senior 40 Plus GD', 4, 'Over 40', 'F', 'doubles', 0),
(25, 'U19BS', 4, 'Under 19', 'M', 'singles', 0),
(26, 'U19BD', 4, 'Under 19', 'M', 'doubles', 0),
(27, 'U19GS', 4, 'Under 19', 'F', 'singles', 0),
(28, 'U19GD', 4, 'Under 19', 'F', 'doubles', 0),
(29, 'U19XD', 4, 'Under 19', 'Mixed', 'mixed doubles', 0),
(30, 'U17XD', 4, 'Under 17', 'Mixed', 'singles', 0),
(31, 'U15XD', 4, 'Under 15', 'Mixed', 'singles', 0),
(32, 'Senior 40 Plus XD', 4, 'Over 40', 'Mixed', 'singles', 0);

-- --------------------------------------------------------

--
-- Table structure for table `category_access`
--

CREATE TABLE `category_access` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `pool` enum('A','B') DEFAULT NULL,
  `player1_id` int(11) DEFAULT NULL,
  `player2_id` int(11) DEFAULT NULL,
  `pre_quarter` tinyint(1) DEFAULT 0,
  `quarter` tinyint(1) DEFAULT 0,
  `semi` tinyint(1) DEFAULT 0,
  `final` tinyint(1) DEFAULT 0,
  `set1_player1_points` int(11) DEFAULT 0,
  `set1_player2_points` int(11) DEFAULT 0,
  `set2_player1_points` int(11) DEFAULT 0,
  `set2_player2_points` int(11) DEFAULT 0,
  `set3_player1_points` int(11) DEFAULT 0,
  `set3_player2_points` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `stage` enum('Pre Quarter Finals','Quarter Finals','Semifinals','Finals','Preliminary') NOT NULL,
  `match_date` date DEFAULT NULL,
  `match_time` varchar(255) NOT NULL,
  `date` date DEFAULT NULL,
  `team1_player1_id` int(11) NOT NULL DEFAULT 0,
  `team1_player2_id` int(11) NOT NULL DEFAULT 0,
  `team2_player1_id` int(11) DEFAULT 0,
  `team2_player2_id` int(11) DEFAULT 0,
  `set1_team1_points` int(11) NOT NULL DEFAULT 0,
  `set1_team2_points` int(11) DEFAULT 0,
  `set2_team1_points` int(11) DEFAULT 0,
  `set2_team2_points` int(11) DEFAULT 0,
  `set3_team1_points` int(11) DEFAULT 0,
  `set3_team2_points` int(11) DEFAULT 0,
  `player3_id` int(11) DEFAULT NULL,
  `player4_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `tournament_id`, `category_id`, `pool`, `player1_id`, `player2_id`, `pre_quarter`, `quarter`, `semi`, `final`, `set1_player1_points`, `set1_player2_points`, `set2_player1_points`, `set2_player2_points`, `set3_player1_points`, `set3_player2_points`, `created_by`, `stage`, `match_date`, `match_time`, `date`, `team1_player1_id`, `team1_player2_id`, `team2_player1_id`, `team2_player2_id`, `set1_team1_points`, `set1_team2_points`, `set2_team1_points`, `set2_team2_points`, `set3_team1_points`, `set3_team2_points`, `player3_id`, `player4_id`, `deleted_at`) VALUES
(1, 1, 2, 'A', 3, 2, 0, 0, 0, 0, 21, 11, 12, 21, 21, 13, 1, 'Quarter Finals', '2024-12-31', '11:11AM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(2, 1, 1, 'A', 2, 3, 0, 0, 0, 0, 21, 12, 12, 21, 21, 12, 1, 'Quarter Finals', '2024-12-26', '12:12AM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(3, 1, 1, 'A', 2, 3, 1, 0, 0, 0, 28, 2, 2, 21, 24, 2, 1, 'Finals', '2024-12-24', '01:12PM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(4, 1, 16, 'A', 1, 4, 0, 0, 0, 0, 21, 2, 1, 21, 21, 1, 1, 'Pre Quarter Finals', '2024-06-01', '01:01AM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(5, 3, 16, NULL, 1, 4, 0, 0, 0, 0, 21, 2, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-01', '20:53', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(7, 1, 1, NULL, 2, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Quarter Finals', '2025-01-01', '12:15', '2025-01-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(8, 1, 8, NULL, 2, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Finals', '2025-01-01', '09:19', '2025-01-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(9, 1, 16, NULL, 1, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-01', '22:22', '2025-01-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(10, 3, 20, NULL, 11, 12, 0, 0, 0, 0, 21, 11, 12, 21, 21, 16, 1, 'Pre Quarter Finals', '2025-01-02', '18:13', '2025-01-02', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(13, 3, 11, NULL, 10, 6, 0, 0, 0, 0, 28, 26, 24, 26, 28, 2, 1, 'Pre Quarter Finals', '2025-01-03', '11:35', '2025-01-02', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(14, 1, 1, NULL, 2, 3, 0, 0, 0, 0, 21, 2, 2, 21, 21, 1, 1, 'Pre Quarter Finals', NULL, '12:29', '2025-01-03', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(15, 1, 11, NULL, 2, 6, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, 1, 'Pre Quarter Finals', NULL, '12:31', '2025-01-03', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(16, 3, 20, NULL, 10, 6, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, 1, 'Pre Quarter Finals', '2025-01-03', '10:19', '2025-01-03', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(17, 3, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Preliminary', '2024-12-30', '10:33', NULL, 2, 10, 11, 12, 21, 0, 0, 21, 21, 4, NULL, NULL, NULL),
(18, 1, 1, NULL, 2, 3, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, 1, 'Pre Quarter Finals', NULL, '06:07', '2025-01-04', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(19, 1, 11, NULL, 3, 6, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-04', '08:25', '2025-01-04', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(20, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Quarter Finals', '2025-01-03', '09:47', NULL, 6, 10, 12, 13, 21, 12, 14, 21, 21, 15, NULL, NULL, NULL),
(21, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2024-12-30', '12:58', NULL, 6, 2, 13, 12, 21, 4, 4, 21, 21, 2, NULL, NULL, NULL),
(22, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', NULL, '2025-01-04 08:37:00', NULL, 6, 2, 13, 12, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(23, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', NULL, '2025-01-04 08:42:00', NULL, 10, 13, 12, 2, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(24, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', NULL, '8', '2025-01-04', 2, 3, 11, 12, 21, 2, 2, 21, 21, 3, NULL, NULL, NULL),
(25, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', NULL, '10', '2025-01-04', 2, 13, 12, 6, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(26, 1, 26, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-06', '9', NULL, 2, 11, 13, 12, 24, 22, 22, 24, 21, 1, NULL, NULL, NULL),
(27, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Preliminary', '2025-01-06', '14:16', NULL, 9, 6, 2, 4, 14, 21, 21, 12, 21, 14, NULL, NULL, NULL),
(28, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Preliminary', '2025-01-06', '17:23', NULL, 9, 6, 2, 4, 1, 21, 21, 2, 7, 21, NULL, NULL, NULL),
(29, 1, 26, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-06', '11', NULL, 12, 3, 6, 13, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(30, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Quarter Finals', '2025-01-06', '11', NULL, 13, 11, 2, 3, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(31, 1, 21, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Quarter Finals', '2025-01-06', '11', NULL, 6, 11, 12, 13, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(32, 1, 15, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Preliminary', '2025-01-06', '13:58', NULL, 4, 14, 1, 15, 21, 13, 12, 21, 19, 21, NULL, NULL, NULL),
(33, 1, 15, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-06', '17', NULL, 19, 9, 17, 15, 21, 12, 13, 21, 21, 2, NULL, NULL, NULL),
(34, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-07', '17', NULL, 1, 6, 21, 4, 24, 3, 4, 21, 21, 3, NULL, NULL, NULL),
(35, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-07', '17', NULL, 19, 17, 18, 13, 21, 3, 3, 21, 21, 2, NULL, NULL, NULL),
(36, 1, 15, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Finals', '2025-01-07', '17', NULL, 4, 9, 14, 19, 26, 24, 22, 24, 21, 2, NULL, NULL, NULL),
(37, 1, 15, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Finals', '2025-01-07', '17', NULL, 16, 9, 14, 1, 21, 3, 2, 21, 2, 21, NULL, NULL, NULL),
(38, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Quarter Finals', '2025-01-11', '9', NULL, 21, 12, 6, 11, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(39, 14, 11, NULL, 3, 20, 0, 0, 0, 0, 21, 2, 2, 21, 21, 11, 1, 'Pre Quarter Finals', '2025-01-01', '0', '2025-01-15', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(40, 1, 26, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'Pre Quarter Finals', '2025-01-15', '13', NULL, 2, 3, 3, 2, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(41, 14, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Finals', '2025-01-01', '12:00', NULL, 3, 21, 11, 20, 3, 21, 12, 2, 2, 21, NULL, NULL, NULL),
(42, 17, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Quarter Finals', '2025-01-16', '10:52', NULL, 19, 12, 14, 20, 21, 2, 2, 21, 21, 5, NULL, NULL, NULL),
(43, 17, 15, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-16', '10:52', NULL, 16, 18, 1, 19, 21, 2, 2, 21, 21, 3, NULL, NULL, NULL),
(44, 1, 1, NULL, 2, 3, 0, 0, 0, 0, 21, 2, 2, 21, 21, 12, NULL, 'Quarter Finals', '2025-01-20', '0', '2025-01-20', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(45, 1, 20, NULL, 22, 22, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, NULL, 'Pre Quarter Finals', NULL, '06:42', '2025-01-20', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(46, 1, 1, NULL, 3, 2, 0, 0, 0, 0, 12, 21, 21, 12, 12, 21, NULL, 'Pre Quarter Finals', '2025-01-21', '21', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL),
(47, 1, 14, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Quarter Finals', '2025-01-21', '21', NULL, 2, 21, 6, 10, 2, 21, 2, 2, 21, 21, NULL, NULL, NULL),
(48, 1, 15, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Quarter Finals', '2025-01-21', '21', NULL, 4, 1, 14, 17, 21, 13, 13, 21, 1, 21, NULL, NULL, NULL),
(49, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-21', '22:14', NULL, 2, 19, 17, 11, 21, 2, 2, 21, 21, 2, NULL, NULL, NULL),
(50, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-21', '22', NULL, 20, 16, 4, 13, 21, 12, 12, 21, 21, 2, NULL, NULL, NULL),
(51, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-21', '22', NULL, 21, 16, 3, 9, 21, 1, 1, 21, 21, 1, NULL, NULL, NULL),
(52, 1, 13, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-21', '13', NULL, 16, 12, 1, 2, 21, 2, 2, 21, 2, 21, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `match_details`
--

CREATE TABLE `match_details` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `match_type` enum('singles','doubles','mixed') NOT NULL,
  `points_scored` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('M','F') NOT NULL,
  `uid` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `name`, `dob`, `age`, `sex`, `uid`, `password`, `created_by`, `updated_at`, `category_id`, `created_at`) VALUES
(1, 'Sreesha', '2008-01-01', 16, 'F', 3, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 1, '2025-01-17 04:14:30', 0, '2025-01-19 07:43:12'),
(2, 'Eric James', '2009-05-02', 15, 'M', 1, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 1, '2025-01-17 04:14:30', 0, '2025-01-19 07:43:12'),
(3, 'Akshaj Tiwari', '2012-01-01', 12, 'M', 2, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 1, '2025-01-17 04:14:30', 0, '2025-01-19 07:43:12'),
(4, 'Lakshmita', '2011-01-01', 13, 'F', 4, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 1, '2025-01-17 04:14:30', 0, '2025-01-19 07:43:12'),
(6, 'Lee Chong Wei', '1980-01-03', 44, 'M', 5, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 1, '2025-01-17 04:14:30', 0, '2025-01-19 07:43:12'),
(9, 'Lakshaya', '2010-01-01', 15, 'F', 10, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(10, 'Gokulan', '1990-01-01', 35, 'M', 9, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(11, 'Zanpear', '1978-05-01', 46, 'M', 6, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(12, 'Pandyraj', '1968-01-01', 57, 'M', 7, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 20, '2025-01-19 07:43:12'),
(13, 'Vijay', '1970-01-30', 54, 'M', 8, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(14, 'Tai Tzu Ying', '1998-01-01', 27, 'F', 11, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(15, 'An Se Young', '2000-01-01', 25, 'F', 13, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(16, 'Okuhara', '1998-01-01', 27, 'F', 14, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(17, 'Anitha Anthony', '2008-01-01', 17, 'F', 15, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(18, 'Carolina', '1995-06-06', 29, 'F', 16, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(19, 'PV Sindhu', '1995-06-07', 29, 'F', 12, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(20, 'Victor Axelsen', '1995-05-07', 29, 'M', 17, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(21, 'Lin Dan', '1986-02-06', 38, 'M', 18, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', 4, '2025-01-17 04:14:30', 1, '2025-01-19 07:43:12'),
(22, 'Player', '2000-06-06', 24, 'M', 19, '$2y$10$2lIVZTjOymQ2m2mqgVHd8OW4KDQmS.pdH5ysk7aGrsL1X6zHNh7Ea', NULL, '2025-01-19 10:05:07', 1, '2025-01-19 07:43:12'),
(25, 'Playertwo', '1994-05-09', 30, 'M', 100, '$2y$10$d5IWpbP2No9zZsgA2n.4vOiuGXIPeT3YTyPMr4GmBnKS7xhR9SPFq', NULL, '2025-01-19 10:08:26', 1, '2025-01-19 10:08:26'),
(26, 'pthree', '1998-05-04', 26, 'M', 101, '$2y$10$YWuR5YIwt8VpQqhIYK2l8.E0LMz2wVwfkAp1ar2hVoXg5VDlda4.y', NULL, '2025-01-19 10:29:49', 1, '2025-01-19 10:29:49'),
(27, 'pfive', '1999-12-27', 25, 'M', 102, '$2y$10$vHXgPAeJUKD1UCevkXIAwOeyTC7SrSwp.C9rLBDiZi52YUe2QD8DS', NULL, '2025-01-19 12:18:31', 1, '2025-01-19 10:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `player_access`
--

CREATE TABLE `player_access` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_access`
--

INSERT INTO `player_access` (`id`, `player_id`, `user_id`, `created_at`) VALUES
(2, 9, 4, '2025-01-01 10:50:08'),
(3, 10, 4, '2025-01-01 10:50:55'),
(4, 11, 4, '2025-01-01 10:51:56'),
(5, 12, 4, '2025-01-01 11:35:13'),
(6, 13, 4, '2025-01-01 12:06:34'),
(7, 14, 4, '2025-01-06 11:29:29'),
(8, 15, 4, '2025-01-06 11:30:03'),
(9, 16, 4, '2025-01-06 11:39:10'),
(10, 17, 4, '2025-01-06 11:39:50'),
(11, 18, 4, '2025-01-06 11:40:23'),
(12, 19, 4, '2025-01-06 11:41:22'),
(13, 20, 4, '2025-01-06 11:42:09'),
(14, 21, 4, '2025-01-06 11:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL DEFAULT year(curdate()),
  `moderated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `created_by`, `year`, `moderated_by`) VALUES
(1, 'ABPL3', 1, 2024, 4),
(2, 'Super Series 2024', 2, 2024, NULL),
(3, 'Winter Series', 4, 2024, NULL),
(6, 'ACE Championship', 1, 2025, 6),
(7, 'xxxxsx', 1, 2025, NULL),
(13, 'uuuuh', 5, 2025, NULL),
(14, 'xxxxaa', 4, 2025, NULL),
(15, 'xxdds', 4, 2025, NULL),
(17, 'zzz', 7, 2025, NULL),
(18, 'zzzz', 7, 2025, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tournament_categories`
--

CREATE TABLE `tournament_categories` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_categories`
--

INSERT INTO `tournament_categories` (`id`, `tournament_id`, `category_id`) VALUES
(3, 2, 4),
(16, 6, 4),
(32, 3, 1),
(33, 3, 11),
(34, 3, 20),
(35, 2, 1),
(74, 1, 1),
(75, 1, 2),
(76, 1, 3),
(77, 1, 13),
(78, 1, 17),
(79, 1, 20),
(80, 1, 21),
(81, 1, 23),
(82, 1, 3),
(83, 1, 26),
(84, 1, 14),
(85, 1, 15),
(86, 1, 13),
(87, 1, 32),
(94, 7, 1),
(97, 13, 18),
(98, 14, 11),
(99, 15, 11),
(100, 14, 11),
(101, 14, 11),
(102, 14, 11),
(103, 14, 11),
(104, 14, 1),
(105, 14, 11),
(106, 14, 14),
(107, 17, 11),
(108, 17, 12),
(109, 17, 13),
(110, 17, 15),
(111, 17, 15),
(112, 18, 11);

-- --------------------------------------------------------

--
-- Table structure for table `tournament_moderators`
--

CREATE TABLE `tournament_moderators` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tournament_moderators`
--

INSERT INTO `tournament_moderators` (`id`, `tournament_id`, `user_id`) VALUES
(18, 1, 4),
(1, 1, 6),
(7, 2, 6),
(5, 6, 4),
(3, 13, 6),
(10, 14, 7),
(15, 17, 7),
(16, 18, 7);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `role` enum('admin','user','visitor','moderator') NOT NULL DEFAULT 'visitor',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `mobile_no`, `notes`, `role`, `created_at`, `email`, `last_login`) VALUES
(1, 'user', '$2y$10$q0I/ctXI5pI0oUUcaTsqV.mTNeYf8evE0xKimNAvhSEooL7CIFjAW', '11111111', 'First user', 'user', '2024-12-29 03:50:28', 'user@user.com', NULL),
(2, 'admin', '$2y$10$Vzemd6vNZoJ7tsir9lxqKuBfkPhks/ZL3mB6YRRNKRLg3H8THFdba', '7432001215', 'Admin account', 'admin', '2024-12-29 04:11:42', 'admin@admin.com', NULL),
(4, 'xxx', '$2y$10$gv7QhzSUciynNAlwFyLSaOgPqgw9IE8jIHZn5qWhDK03QXYWNV6bm', '333', 'Hello...', 'admin', '2024-12-29 12:10:35', 'xxx@xxxx.com', NULL),
(5, 'user2', '$2y$10$h2N1Jb3tCQ72X.KWuQaB8eUfBfJa61DULmbLDzMArIlUdtpj4im.m', '2222222222', NULL, 'user', '2024-12-31 15:28:19', 'user2@jdjdj.com', NULL),
(6, 'user1', '$2y$10$630Wk4DbeWyToUcclXn66.2YMBCpUb8/ZwAvZwsbMU72PF3nNWdB2', '2222222222', NULL, 'user', '2025-01-10 05:55:38', 'asda@sd.asda', NULL),
(7, 'zzz', '$2y$10$jm1Bobqhw9.TxO8.u6bC8urfHloJHZJH9bqhyRfuaSZ7HixBj.xHS', '1111111111', NULL, 'user', '2025-01-11 03:53:39', 'xxxaa@sdad.dsa', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_notes`
--

CREATE TABLE `user_notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_access`
--
ALTER TABLE `category_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_id` (`category_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `player1_id` (`player1_id`),
  ADD KEY `player2_id` (`player2_id`),
  ADD KEY `fk_player3` (`player3_id`),
  ADD KEY `fk_player4` (`player4_id`),
  ADD KEY `idx_stage` (`stage`);

--
-- Indexes for table `match_details`
--
ALTER TABLE `match_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD UNIQUE KEY `uid_2` (`uid`),
  ADD UNIQUE KEY `uid_3` (`uid`);

--
-- Indexes for table `player_access`
--
ALTER TABLE `player_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_moderated_by` (`moderated_by`);

--
-- Indexes for table `tournament_categories`
--
ALTER TABLE `tournament_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tournament_moderators`
--
ALTER TABLE `tournament_moderators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournament_id` (`tournament_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_notes`
--
ALTER TABLE `user_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `category_access`
--
ALTER TABLE `category_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `match_details`
--
ALTER TABLE `match_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `player_access`
--
ALTER TABLE `player_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tournament_categories`
--
ALTER TABLE `tournament_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `tournament_moderators`
--
ALTER TABLE `tournament_moderators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_notes`
--
ALTER TABLE `user_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);

--
-- Constraints for table `category_access`
--
ALTER TABLE `category_access`
  ADD CONSTRAINT `category_access_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `category_access_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_player3` FOREIGN KEY (`player3_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `fk_player4` FOREIGN KEY (`player4_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player1_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`player2_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `match_details`
--
ALTER TABLE `match_details`
  ADD CONSTRAINT `match_details_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `player_access`
--
ALTER TABLE `player_access`
  ADD CONSTRAINT `player_access_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_access_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `fk_moderated_by` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournaments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_categories`
--
ALTER TABLE `tournament_categories`
  ADD CONSTRAINT `tournament_categories_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `tournament_moderators`
--
ALTER TABLE `tournament_moderators`
  ADD CONSTRAINT `tournament_moderators_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_moderators_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notes`
--
ALTER TABLE `user_notes`
  ADD CONSTRAINT `user_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
