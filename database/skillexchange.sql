-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 27, 2025 at 08:17 PM
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
-- Database: `skillexchange`
--

-- --------------------------------------------------------

--
-- Table structure for table `endorsements`
--

CREATE TABLE `endorsements` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `endorsements`
--

INSERT INTO `endorsements` (`id`, `sender_id`, `receiver_id`, `request_id`, `rating`, `feedback`, `created_at`) VALUES
(1, 12, 4, 23, 4, 'very good collaboraton', '2025-06-22 14:16:14');

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `user_id` int(11) NOT NULL,
  `total_swaps` int(11) DEFAULT 0,
  `teach_count` int(11) DEFAULT 0,
  `learn_count` int(11) DEFAULT 0,
  `endorsements_given` int(11) DEFAULT 0,
  `endorsements_received` int(11) DEFAULT 0,
  `xp` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `request_id`, `message`, `sent_at`) VALUES
(4, 12, 4, 35, 'you beautiful :)', '2025-06-23 16:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `is_seen`) VALUES
(11, 4, '📩 You received a new skill swap request!', 0, '2025-06-23 12:25:29', 1),
(12, 12, '📩 You received a new skill swap request!', 0, '2025-06-23 12:33:27', 1),
(13, 12, '📩 You received a new skill swap request!', 0, '2025-06-23 12:39:02', 1),
(14, 12, '📩 You received a new skill swap request!', 0, '2025-06-23 12:39:21', 1),
(15, 12, '📩 You received a new skill swap request!', 0, '2025-06-23 12:51:45', 1),
(16, 4, '📩 You received a new skill swap request!', 0, '2025-06-23 12:54:59', 1),
(17, 12, '📩 You received a new skill swap request!', 0, '2025-06-23 13:01:46', 1),
(18, 4, '📩 You received a new skill swap request!', 0, '2025-06-23 13:08:55', 1),
(19, 4, '💬 You received a new message!', 0, '2025-06-23 16:48:33', 1),
(20, 12, '📩 You received a new skill swap request!', 0, '2025-06-23 20:03:28', 1),
(21, 13, '📩 You received a new skill swap request!', 0, '2025-06-23 20:04:11', 1),
(22, 12, '📩 You received a new skill swap request!', 0, '2025-06-26 21:26:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_completed` tinyint(1) DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`, `is_completed`, `scheduled_at`) VALUES
(35, 12, 4, 'Accepted', '2025-06-23 13:08:55', 0, '2025-06-27 18:59:00'),
(36, 13, 12, 'Accepted', '2025-06-23 20:03:28', 0, '2025-06-30 01:36:00'),
(37, 12, 13, 'Pending', '2025-06-23 20:04:11', 0, NULL),
(38, 4, 12, 'Pending', '2025-06-26 21:26:53', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT 'images/default.png',
  `teach_skills` text DEFAULT NULL,
  `learn_skills` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(100) DEFAULT 'unspecified',
  `skill_level` varchar(50) DEFAULT 'beginner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `profile_pic`, `teach_skills`, `learn_skills`, `bio`, `location`, `skill_level`) VALUES
(4, 'Bhakti Garg', 'btbtc22014_bhakti@banasthali.in', '$2y$10$BWepB6YvTWMt6TLPQuCok.qEAddpMMN.OoRi8rLEx64iyCSjvWwg6', '2025-06-12 15:49:59', 'images/profile_4.jpg', 'Python, Html.', 'C++, SQL.', '', 'Mumbai', 'Beginner'),
(12, 'Tanisha Garg', 'bhaktigarg1602@gmail.com', '$2y$10$W7jnBGJEs9qrptKbeBqp7Oj0DpsoDSfUPaQagkPDX/d6w7bAELdoG', '2025-06-22 13:47:47', 'images/profile_12.jpg', 'C++, Sql', 'Html, Python', NULL, 'unspecified', 'beginner'),
(13, 'Trisha Agrawal', 'bhaktigarg237@gmail.com', '$2y$10$gs5Ngz8HmA/0Rxx2KktbyeT4Er3fYFptPwv3dnol7lO5m.pdG1786', '2025-06-23 20:01:16', 'images/profile_13.jpg', 'python, javascript', 'Sql, C++', NULL, 'unspecified', 'beginner');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `endorsements`
--
ALTER TABLE `endorsements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `endorsements`
--
ALTER TABLE `endorsements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
