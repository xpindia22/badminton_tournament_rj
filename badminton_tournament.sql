-- phpMyAdmin SQL Dump
-- version 5.2.1deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 03, 2025 at 07:21 AM
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
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `age_group` varchar(255) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_by`, `age_group`, `sex`) VALUES
(1, 'U17BS', 1, 'Under 17', 'M'),
(2, 'U15BS', 1, 'Under 15', 'M'),
(3, 'U15BD', 1, 'Under 15', 'M'),
(4, 'U17BD', 2, 'Under 17', 'M'),
(6, 'U15GS', 4, 'Under 15', 'F'),
(7, 'U15GD', 1, 'Under 15', 'F'),
(8, 'U13BS', 1, 'Under 13', 'M'),
(11, 'Open Mens Singles', 4, 'Between 5 - 100', 'M'),
(12, 'Open Womens Single', 4, 'Between 5 - 100', 'F'),
(13, 'Open XD', 4, 'Under 100', 'Mixed'),
(14, 'Open Mens Doubles', 4, 'Open', 'M'),
(15, 'Open Women Doubles', 4, 'Open', 'F'),
(16, 'U17GS', 4, 'Under 17', 'F'),
(17, 'U17GD', 4, 'Under 17', 'F'),
(18, 'U17GD', 4, 'Under 17', 'F'),
(19, 'U13GS', 4, 'Under 13', 'F'),
(20, 'Senior 40 Plus Mens Single', 4, 'Over 40', 'M'),
(21, 'Senior 40 Plus Mens Doubles', 4, 'Over 40', 'M'),
(22, 'Senior 40 Plus Women Single', 4, 'Over 40', 'F'),
(23, 'Senior 40 Plus Women Doubles', 4, 'Over 40', 'F'),
(25, 'U19BS', 4, 'Under 19', 'M'),
(26, 'U19BD', 4, 'Under 19', 'M'),
(27, 'U19GS', 4, 'Under 19', 'F'),
(28, 'U19GD', 4, 'Under 19', 'F'),
(29, 'U19XD', 4, 'Under 19', 'Mixed'),
(30, 'U17XD', 4, 'Under 17', 'Mixed'),
(31, 'U15XD', 4, 'Under 15', 'Mixed'),
(32, 'Senior 40 Plus XD', 4, 'Over 40', 'Mixed');

-- --------------------------------------------------------

--
-- Table structure for table `category_access`
--

CREATE TABLE `category_access` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `pool` enum('A','B') DEFAULT NULL,
  `player1_id` int(11) NOT NULL,
  `player2_id` int(11) NOT NULL,
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
  `stage` varchar(22) NOT NULL,
  `match_date` date DEFAULT NULL,
  `match_time` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `team1_player1_id` int(11) NOT NULL DEFAULT 0,
  `team1_player2_id` int(11) NOT NULL DEFAULT 0,
  `team2_player1_id` int(11) DEFAULT 0,
  `team2_player2_id` int(11) DEFAULT 0,
  `set1_team1_points` int(11) NOT NULL DEFAULT 0,
  `set1_team2_points` int(11) DEFAULT 0,
  `set2_team1_points` int(11) DEFAULT 0,
  `set2_team2_points` int(11) DEFAULT 0,
  `set3_team1_points` int(11) DEFAULT 0,
  `set3_team2_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `tournament_id`, `category_id`, `pool`, `player1_id`, `player2_id`, `pre_quarter`, `quarter`, `semi`, `final`, `set1_player1_points`, `set1_player2_points`, `set2_player1_points`, `set2_player2_points`, `set3_player1_points`, `set3_player2_points`, `created_by`, `stage`, `match_date`, `match_time`, `date`, `team1_player1_id`, `team1_player2_id`, `team2_player1_id`, `team2_player2_id`, `set1_team1_points`, `set1_team2_points`, `set2_team1_points`, `set2_team2_points`, `set3_team1_points`, `set3_team2_points`) VALUES
(1, 1, 2, 'A', 3, 2, 0, 0, 0, 0, 21, 11, 12, 21, 21, 13, 1, 'Pre Quarter Finals', '2024-12-31', '11:11AM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 1, 1, 'A', 2, 3, 0, 0, 0, 0, 21, 12, 12, 21, 21, 12, 1, 'Quarter Finals', '2024-12-26', '12:12AM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3, 1, 1, 'A', 2, 3, 0, 0, 0, 0, 28, 2, 2, 21, 24, 2, 1, 'Finals', '2024-12-24', '01:12PM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, 1, 3, 'A', 1, 4, 0, 0, 0, 0, 21, 2, 1, 21, 21, 1, 1, 'Pre Quarter Finals', '2024-06-01', '01:01AM', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5, 3, 7, NULL, 1, 4, 0, 0, 0, 0, 21, 2, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-01', '20:53', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 4, 4, NULL, 6, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, 'Quarterfinals', '2025-01-01', '06:55', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(7, 1, 1, NULL, 2, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Quarter Finals', '2025-01-01', '12:15', '2025-01-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(8, 1, 8, NULL, 2, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Finals', '2025-01-01', '09:19', '2025-01-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 1, 3, NULL, 1, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 'Pre Quarter Finals', '2025-01-01', '22:22', '2025-01-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(10, 3, 20, NULL, 11, 12, 0, 0, 0, 0, 21, 11, 12, 21, 21, 16, NULL, 'Pre Quarter Finals', '2025-01-02', '18:13', '2025-01-02', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(11, 4, 20, NULL, 6, 12, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, NULL, 'Pre Quarter Finals', NULL, '11:01', '2025-01-02', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(12, 4, 20, NULL, 6, 12, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, NULL, 'Pre Quarter Finals', NULL, '11:01', '2025-01-02', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(13, 3, 11, NULL, 10, 6, 0, 0, 0, 0, 28, 26, 24, 26, 28, 2, NULL, 'Pre Quarter Finals', '2025-01-03', '11:35', '2025-01-02', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(14, 1, 1, NULL, 2, 3, 0, 0, 0, 0, 21, 2, 2, 21, 21, 1, NULL, 'Pre Quarter Finals', NULL, '12:29', '2025-01-03', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(15, 1, 11, NULL, 2, 6, 0, 0, 0, 0, 21, 2, 2, 21, 21, 2, NULL, 'Pre Quarter Finals', NULL, '12:31', '2025-01-03', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

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
  `uid` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `name`, `dob`, `age`, `sex`, `uid`, `created_by`, `category_id`) VALUES
(1, 'Sreesha', '2008-01-01', 16, 'F', '3', 1, 0),
(2, 'Eric James', '2009-05-02', 15, 'M', '1', 1, 0),
(3, 'Akshaj Tiwari', '2012-01-01', 12, 'M', '2', 1, 0),
(4, 'Lakshmita', '2011-01-01', 13, 'F', '4', 1, 0),
(6, 'Lee Chong Wei', '1980-01-03', 44, 'M', '5', 1, 0),
(9, 'Lakshaya', '2010-01-01', 15, 'F', '10', 4, 1),
(10, 'Gokulan', '1990-01-01', 35, 'M', '9', 4, 1),
(11, 'Zanpear', '1978-05-01', 46, 'M', '6', 4, 1),
(12, 'Pandyraj', '1968-01-01', 57, 'M', '7', 4, 20),
(13, 'Vijay', '1970-01-30', 54, 'M', '8', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `player_access`
--

CREATE TABLE `player_access` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `player_access`
--

INSERT INTO `player_access` (`id`, `player_id`, `user_id`, `created_at`) VALUES
(2, 9, 4, '2025-01-01 10:50:08'),
(3, 10, 4, '2025-01-01 10:50:55'),
(4, 11, 4, '2025-01-01 10:51:56'),
(5, 12, 4, '2025-01-01 11:35:13'),
(6, 13, 4, '2025-01-01 12:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL DEFAULT year(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `created_by`, `year`) VALUES
(1, 'ABPL3', 1, 2024),
(2, 'Super Series 2024', 2, 2024),
(3, 'Winter Series', 3, 2024),
(4, 'Senior Championship', 5, 2015),
(5, 'Winners Trophy', 1, 2025),
(6, 'ACE Championship', 1, 2025);

-- --------------------------------------------------------

--
-- Table structure for table `tournament_categories`
--

CREATE TABLE `tournament_categories` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
(36, 4, 2),
(37, 4, 3),
(44, 1, 1),
(45, 1, 2),
(46, 1, 3),
(47, 1, 14),
(48, 1, 11);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile_no` varchar(15) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `role` enum('admin','user','visitor') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `mobile_no`, `notes`, `role`, `created_at`, `email`) VALUES
(1, 'user', '$2y$10$q0I/ctXI5pI0oUUcaTsqV.mTNeYf8evE0xKimNAvhSEooL7CIFjAW', '11111111', 'First user', 'user', '2024-12-29 03:50:28', 'user@user.com'),
(2, 'admin', '$2y$10$Vzemd6vNZoJ7tsir9lxqKuBfkPhks/ZL3mB6YRRNKRLg3H8THFdba', '7432001215', 'Admin account', 'admin', '2024-12-29 04:11:42', 'admin@admin.com'),
(4, 'xxx', '$2y$10$gv7QhzSUciynNAlwFyLSaOgPqgw9IE8jIHZn5qWhDK03QXYWNV6bm', '333', 'Hello...', 'admin', '2024-12-29 12:10:35', 'xxx@xxxx.com'),
(5, 'user2', '$2y$10$h2N1Jb3tCQ72X.KWuQaB8eUfBfJa61DULmbLDzMArIlUdtpj4im.m', '2222222222', NULL, 'user', '2024-12-31 15:28:19', 'user2@jdjdj.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `match_id` (`match_id`);

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
  ADD KEY `player2_id` (`player2_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournament_categories`
--
ALTER TABLE `tournament_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `player_access`
--
ALTER TABLE `player_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tournament_categories`
--
ALTER TABLE `tournament_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player1_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`player2_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `player_access`
--
ALTER TABLE `player_access`
  ADD CONSTRAINT `player_access_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_access_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_categories`
--
ALTER TABLE `tournament_categories`
  ADD CONSTRAINT `tournament_categories_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `tournament_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
