-- Active: 1758177509499@@127.0.0.1@3306@mental_health_platform
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 03, 2025 at 02:08 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

USE mental_health_platform;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mental_health_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int NOT NULL,
  `actor_type` enum('user','admin','konselor') NOT NULL,
  `actor_id` int NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `actor_type`, `actor_id`, `action`, `details`, `created_at`) VALUES
(1, 'admin', 1, 'create_user', '{\"user_id\":7,\"email\":\"hendro@astral.us\",\"role\":\"user\"}', '2025-12-02 19:53:12'),
(2, 'admin', 1, 'create_user', '{\"user_id\":8,\"email\":\"maru@astral.us\",\"role\":\"admin\"}', '2025-12-02 20:01:11'),
(3, 'admin', 1, 'update_user', '{\"user_id\":7,\"updated\":[\"name=?\",\"email=?\",\"role=?\"]}', '2025-12-02 20:01:46'),
(4, 'admin', 1, 'create_user', '{\"user_id\":9,\"email\":\"momo@astral.us\",\"role\":\"user\"}', '2025-12-02 21:56:43'),
(5, 'admin', 1, 'update_user', '{\"user_id\":9,\"updated\":{\"name\":\"Momo mew\",\"email\":\"momo@astral.us\",\"role\":\"user\"}}', '2025-12-02 21:57:14'),
(6, 'admin', 1, 'update_user', '{\"user_id\":9,\"updated\":{\"name\":\"Momo mewW\",\"email\":\"momo@astral.us\",\"role\":\"user\"}}', '2025-12-02 22:07:43'),
(7, 'admin', 1, 'delete_user', '{\"user_id\":7}', '2025-12-02 22:07:50'),
(8, 'admin', 1, 'create_konselor', '{\"konselor_id\":1,\"email\":\"Aw@A.C\"}', '2025-12-02 22:10:12'),
(9, 'admin', 1, 'create_konselor', '{\"konselor_id\":2,\"email\":\"Aw@A.CS\"}', '2025-12-02 22:11:09'),
(10, 'admin', 1, 'update_konselor', '{\"konselor_id\":1,\"updated\":{\"name\":\"A\",\"email\":\"Aw@A.C\",\"password\":\"updated\"}}', '2025-12-02 22:11:22'),
(11, 'admin', 1, 'delete_user', '{\"user_id\":3}', '2025-12-02 22:38:18'),
(12, 'admin', 1, 'update_konselor', '{\"konselor_id\":2,\"updated\":{\"name\":\"hENDRI\",\"email\":\"Aw@A.CSx\"}}', '2025-12-02 22:38:27'),
(13, 'admin', 1, 'delete_konselor', '{\"konselor_id\":2}', '2025-12-02 22:38:39'),
(14, 'admin', 1, 'delete_konselor', '{\"konselor_id\":1}', '2025-12-02 22:38:42'),
(15, 'admin', 1, 'create_konselor', '{\"konselor_id\":3,\"email\":\"hendri@astral.us\"}', '2025-12-02 22:39:45'),
(16, 'admin', 1, 'create_konselor', '{\"konselor_id\":4,\"email\":\"EsdeeKid@astral.us\"}', '2025-12-02 22:56:50'),
(17, 'admin', 1, 'create_konselor', '{\"konselor_id\":5,\"email\":\"tastetec@astral.us\"}', '2025-12-02 23:02:13'),
(18, 'admin', 1, 'update_konselor', '{\"konselor_id\":4,\"updated\":{\"name\":\"Tenxi Widjaya\",\"email\":\"j4w1r@astral.us\"}}', '2025-12-02 23:02:31'),
(19, 'admin', 1, 'update_konselor', '{\"konselor_id\":4,\"updated\":{\"name\":\"Tenxi Widjaya\",\"email\":\"j4w1r@astral.us\",\"password\":\"updated\"}}', '2025-12-02 23:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `log_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `article_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_message`
--

CREATE TABLE `chat_message` (
  `message_id` int NOT NULL,
  `session_id` int NOT NULL,
  `sender_type` enum('user','konselor') NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_session`
--

CREATE TABLE `chat_session` (
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `konselor_id` int NOT NULL,
  `started_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `ended_at` datetime DEFAULT NULL,
  `is_trial` tinyint(1) DEFAULT '1',
  `status` enum('active','ended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `issue_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konselor`
--

CREATE TABLE `konselor` (
  `konselor_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text,
  `profile_picture` varchar(255) DEFAULT NULL,
  `experience_years` int DEFAULT '0',
  `rating` float DEFAULT '0',
  `online_status` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `konselor`
--

INSERT INTO `konselor` (`konselor_id`, `name`, `email`, `password`, `bio`, `profile_picture`, `experience_years`, `rating`, `online_status`, `created_at`) VALUES
(1, 'Hendri', 'hendri@astral.us', '$2y$10$HZzVsZaetGhGwa5.McPfqO6kZfEyDzkSeJUfkkEMwPnz1Rop5BEv.', NULL, NULL, 0, 0, 0, '2025-12-02 22:39:45'),
(2, 'EsdeeKid', 'EsdeeKid@astral.us', '$2y$10$.LTEpEW8RbVQwOfAhp3jpO3y0WSPwnFEfcL2S0Z5NpaekV8ftxpw2', NULL, NULL, 0, 0, 0, '2025-12-02 22:56:50'),
(3, 'Carti', 'pboycarti@astral.us', 'konselor', 'schyeahh', NULL, 5, 4.9, 0, '2025-12-02 22:59:23'),
(4, 'Tenxi Widjaya', 'j4w1r@astral.us', '$2y$10$ep03ovedH9nDd1V73YT4gesskiN9wA.lcASwFkjnGRkh6OtlYUdba', 'Dia suka baju hitamku celana camo ku', NULL, 3, 5, 0, '2025-12-02 23:00:56'),
(5, 'Tecca', 'tastetec@astral.us', '$2y$10$WgVsTtqnhs8tkGA.CH63UehGZ.XBsM0HO7rQjc4TUcgXuN/BBNldu', NULL, NULL, 0, 0, 0, '2025-12-02 23:02:13');

-- --------------------------------------------------------

--
-- Table structure for table `konselor_profile`
--

CREATE TABLE `konselor_profile` (
  `profile_id` int NOT NULL,
  `konselor_id` int NOT NULL,
  `communication_style` enum('S','G','B') NOT NULL,
  `approach_style` enum('O','D','B') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konselor_specialization`
--

CREATE TABLE `konselor_specialization` (
  `konselor_id` int NOT NULL,
  `issue_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matching_history`
--

CREATE TABLE `matching_history` (
  `match_id` int NOT NULL,
  `user_id` int NOT NULL,
  `konselor_id` int NOT NULL,
  `score` float NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `session_id` int NOT NULL,
  `amount` int NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `proof_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `subscription_id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan` enum('daily','weekly','monthly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `profile_picture`, `role`, `created_at`) VALUES
(1, 'Admin Astral', 'admin@astral.us', '$2y$10$/sOLTxbDBWzQNecQPOsn5.o9gWa7U4XFUk/iLfs8.XukZA.fPcUZe', NULL, 'admin', '2025-12-01 22:59:02'),
(2, 'zabbix', 'n@unila.ac.id', '$2y$10$dmwnAg6TCzIHhqqeLbHPEuId4C667dUtInd1tRRvilT67jtaMOM9e', NULL, 'user', '2025-11-30 12:53:32'),
(4, 'root', 'admin@unila.ac.id', 'root', NULL, 'admin', '2025-12-01 22:15:33'),
(6, 'ray', 'ray@mail.com', '$2y$10$k.y0W7iEjPTjRjSYfQmcputVAjkVWlzjoA4dbrXuCwDb/5G6eiItu', NULL, 'user', '2025-12-01 23:11:11'),
(8, 'Maru', 'maru@astral.us', '$2y$10$Al3fo4M/MOihY1yPS.uIqu/yqn54.Yc6x8WA3m0Fv.SgkE/Uo1dWq', NULL, 'admin', '2025-12-02 20:01:11'),
(9, 'Momo mewW', 'momo@astral.us', '$2y$10$wTEfxNDioSk7bOSTF.6q8.fFKQ4CdouaQ6VJB1K/cwsQj11zE4lsy', NULL, 'user', '2025-12-02 21:56:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_issue`
--

CREATE TABLE `user_issue` (
  `user_id` int NOT NULL,
  `issue_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `pref_id` int NOT NULL,
  `user_id` int NOT NULL,
  `communication_pref` enum('S','G','B') NOT NULL,
  `approach_pref` enum('O','D','B') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_survey`
--

CREATE TABLE `user_survey` (
  `survey_id` int NOT NULL,
  `user_id` int NOT NULL,
  `q1` tinyint NOT NULL,
  `q2` tinyint NOT NULL,
  `q3` tinyint NOT NULL,
  `q4` tinyint NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`);

--
-- Indexes for table `chat_message`
--
ALTER TABLE `chat_message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `chat_session`
--
ALTER TABLE `chat_session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `konselor_id` (`konselor_id`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`issue_id`);

--
-- Indexes for table `konselor`
--
ALTER TABLE `konselor`
  ADD PRIMARY KEY (`konselor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `konselor_profile`
--
ALTER TABLE `konselor_profile`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `konselor_id` (`konselor_id`);

--
-- Indexes for table `konselor_specialization`
--
ALTER TABLE `konselor_specialization`
  ADD PRIMARY KEY (`konselor_id`,`issue_id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indexes for table `matching_history`
--
ALTER TABLE `matching_history`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `konselor_id` (`konselor_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_issue`
--
ALTER TABLE `user_issue`
  ADD PRIMARY KEY (`user_id`,`issue_id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`pref_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_survey`
--
ALTER TABLE `user_survey`
  ADD PRIMARY KEY (`survey_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_message`
--
ALTER TABLE `chat_message`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_session`
--
ALTER TABLE `chat_session`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `issue_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `konselor`
--
ALTER TABLE `konselor`
  MODIFY `konselor_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `konselor_profile`
--
ALTER TABLE `konselor_profile`
  MODIFY `profile_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matching_history`
--
ALTER TABLE `matching_history`
  MODIFY `match_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `subscription_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `pref_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_survey`
--
ALTER TABLE `user_survey`
  MODIFY `survey_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_message`
--
ALTER TABLE `chat_message`
  ADD CONSTRAINT `chat_message_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_session` (`session_id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_session`
--
ALTER TABLE `chat_session`
  ADD CONSTRAINT `chat_session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_session_ibfk_2` FOREIGN KEY (`konselor_id`) REFERENCES `konselor` (`konselor_id`) ON DELETE CASCADE;

--
-- Constraints for table `konselor_profile`
--
ALTER TABLE `konselor_profile`
  ADD CONSTRAINT `konselor_profile_ibfk_1` FOREIGN KEY (`konselor_id`) REFERENCES `konselor` (`konselor_id`) ON DELETE CASCADE;

--
-- Constraints for table `konselor_specialization`
--
ALTER TABLE `konselor_specialization`
  ADD CONSTRAINT `konselor_specialization_ibfk_1` FOREIGN KEY (`konselor_id`) REFERENCES `konselor` (`konselor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `konselor_specialization_ibfk_2` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`issue_id`) ON DELETE CASCADE;

--
-- Constraints for table `matching_history`
--
ALTER TABLE `matching_history`
  ADD CONSTRAINT `matching_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matching_history_ibfk_2` FOREIGN KEY (`konselor_id`) REFERENCES `konselor` (`konselor_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `chat_session` (`session_id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription`
--
ALTER TABLE `subscription`
  ADD CONSTRAINT `subscription_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_issue`
--
ALTER TABLE `user_issue`
  ADD CONSTRAINT `user_issue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_issue_ibfk_2` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`issue_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_survey`
--
ALTER TABLE `user_survey`
  ADD CONSTRAINT `user_survey_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
