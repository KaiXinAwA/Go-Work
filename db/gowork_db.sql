-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-05-11 10:13:53
-- 服务器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `gowork_db`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`admin_id`, `user_id`, `role`, `created_at`) VALUES
(1, 1, 'Super Admin', '2025-03-30 14:36:04');

-- --------------------------------------------------------

--
-- 表的结构 `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `resume_path` varchar(255) DEFAULT NULL,
  `is_hidden_from_user` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `applications`
--

INSERT INTO `applications` (`application_id`, `job_id`, `user_id`, `applied_date`, `status`, `resume_path`, `is_hidden_from_user`, `created_at`) VALUES
(6, 6, 2, '2025-04-27 12:33:41', 'Viewed', '67e95c25ca53c_Boarding Pass.pdf', 0, '2025-04-27 12:33:41'),
(7, 7, 6, '2025-05-04 13:56:12', 'Shortlisted', '681767bb9649d_database_lab5.pdf', 0, '2025-05-04 13:56:12'),
(8, 7, 16, '2025-05-10 03:53:16', 'Shortlisted', NULL, 0, '2025-05-07 13:09:06'),
(9, 7, 17, '2025-05-10 16:12:56', 'Rejected', '681f7adfc5a15_lab 3.pdf', 0, '2025-05-10 16:12:56'),
(10, 6, 17, '2025-05-10 16:13:04', 'Pending', '681f7adfc5a15_lab 3.pdf', 0, '2025-05-10 16:13:04');

-- --------------------------------------------------------

--
-- 表的结构 `backup_career_history`
--

CREATE TABLE `backup_career_history` (
  `career_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `backup_career_history`
--

INSERT INTO `backup_career_history` (`career_id`, `user_id`, `job_title`, `company_name`, `start_date`, `end_date`, `description`, `created_at`, `updated_at`) VALUES
(2, 6, 'wqq', 'eee', '2025-03-13', '2025-04-10', 'qwq', '2025-04-13 08:01:58', '2025-04-13 08:01:58'),
(3, 6, 'dsad', 'asdad', '2025-04-12', '2025-04-06', 'adad', '2025-04-13 09:31:02', '2025-04-13 09:31:02');

-- --------------------------------------------------------

--
-- 表的结构 `backup_user_profiles`
--

CREATE TABLE `backup_user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `completion_status` varchar(50) DEFAULT NULL,
  `education_highlights` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `backup_user_profiles`
--

INSERT INTO `backup_user_profiles` (`profile_id`, `user_id`, `full_name`, `phone`, `address`, `city`, `state`, `country`, `resume_path`, `education`, `institution`, `completion_status`, `education_highlights`, `skills`, `last_updated`) VALUES
(1, 2, 'JiaYing', '1111111', '11', '11', '11', '11', '67e95c25ca53c_Boarding Pass.pdf', '11', NULL, NULL, NULL, NULL, '2025-03-30 15:45:43'),
(4, 6, 'dsadad', '123', 'qeqwe', 'Sibu', 'Sarawak', 'Malaysia', '67fb6f9fc9b06_Lab2.pdf', 'eqwewe', 'qeqe', 'In Progress', 'qeweq', 'eqeqweqwe', '2025-04-20 06:23:09'),
(9, 11, 'addasd', '123', '11', '11', '11', '111', NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-27 02:40:57');

-- --------------------------------------------------------

--
-- 表的结构 `career_history`
--

CREATE TABLE `career_history` (
  `career_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `career_history`
--

INSERT INTO `career_history` (`career_id`, `user_id`, `job_title`, `company_name`, `start_date`, `end_date`, `description`, `created_at`, `updated_at`) VALUES
(2, 6, 'wqq', 'eee', '2025-03-13', '2025-04-10', 'qwq', '2025-04-13 08:01:58', '2025-04-13 08:01:58'),
(3, 6, 'dsad', 'asdad', '2025-04-12', '2025-04-06', 'adad', '2025-04-13 09:31:02', '2025-04-13 09:31:02'),
(5, 17, 'Programming', 'Sunlight Sdn.Bhd', '2025-01-11', '2025-04-11', 'Sweeping, mopping, serve customers', '2025-05-10 16:09:58', '2025-05-10 16:09:58');

--
-- 触发器 `career_history`
--
DELIMITER $$
CREATE TRIGGER `before_career_history_insert` BEFORE INSERT ON `career_history` FOR EACH ROW BEGIN
    DECLARE user_type_val INT;
    
    -- Get the user's type
    SELECT user_type INTO user_type_val
    FROM users
    WHERE user_id = NEW.user_id;
    
    -- If not a jobseeker, prevent the insert
    IF user_type_val != 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only job seekers can have career history entries';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `company_culture` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `license_path` varchar(255) DEFAULT NULL,
  `license_status` enum('Pending','Approved','Not Approved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `companies`
--

INSERT INTO `companies` (`company_id`, `user_id`, `company_name`, `description`, `company_culture`, `contact_number`, `address`, `city`, `state`, `country`, `license_path`, `license_status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Sunlight', 'GoGoGo~', '{\"values\":{\"work_environment\":\"collaborative\",\"overtime\":\"structured\",\"management\":\"autonomous\",\"work_life_balance\":\"flexible\",\"dress_code\":\"traditional\",\"communication\":\"formal\",\"decision_making\":\"analytical\",\"innovation\":\"innovative\",\"social_events\":\"energetic\",\"feedback\":\"considerate\"},\"description\":\"describe settingsss\"}', '099-9999099', 'No2, Jalan University', 'Sibu', 'Sarawak', 'Malaysia', '67e959e8555c6_Boarding Pass.pdf', 'Approved', '2025-03-30 14:40:01', '2025-05-04 13:21:49'),
(4, 13, 'Starlight', 'Good Company', '{\"values\":{\"work_environment\":\"collaborative\",\"overtime\":\"structured\",\"management\":\"autonomous\",\"work_life_balance\":\"flexible\",\"dress_code\":\"traditional\",\"communication\":\"formal\",\"decision_making\":\"collaborative\",\"innovation\":\"innovative\",\"social_events\":\"energetic\",\"feedback\":\"direct\"},\"description\":\"Good\"}', NULL, NULL, NULL, NULL, NULL, '68176fc7ec810_database_lab5.pdf', 'Approved', '2025-05-04 13:41:13', '2025-05-10 14:42:01');

-- --------------------------------------------------------

--
-- 表的结构 `culture_quiz_options`
--

CREATE TABLE `culture_quiz_options` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `culture_value` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `culture_quiz_options`
--

INSERT INTO `culture_quiz_options` (`option_id`, `question_id`, `option_text`, `culture_value`, `created_at`) VALUES
(1, 1, 'Open office with collaborative spaces', 'collaborative', '2025-04-27 11:29:46'),
(2, 1, 'Quiet, individual workspaces', 'focused', '2025-04-27 11:29:46'),
(3, 1, 'Flexible, with option to work remotely', 'flexible', '2025-04-27 11:29:46'),
(4, 1, 'Dynamic, high-energy environment', 'energetic', '2025-04-27 11:29:46'),
(5, 2, 'I\'m willing to work overtime when necessary to meet deadlines', 'dedicated', '2025-04-27 11:37:49'),
(6, 2, 'I prefer strict 9-5 hours with overtime only in emergencies', 'structured', '2025-04-27 11:37:49'),
(7, 2, 'I prefer flexibility - sometimes I work late, other times I leave early', 'flexible', '2025-04-27 11:37:49'),
(8, 2, 'I\'m happy to work long hours if the work is meaningful', 'passionate', '2025-04-27 11:37:49'),
(9, 3, 'Hands-off, with lots of autonomy', 'autonomous', '2025-04-27 11:38:22'),
(10, 3, 'Structured with clear guidance', 'structured', '2025-04-27 11:38:22'),
(11, 3, 'Collaborative and mentoring', 'collaborative', '2025-04-27 11:38:22'),
(12, 3, 'Results-oriented without micromanagement', 'results-focused', '2025-04-27 11:38:22'),
(13, 4, 'Essential - I value my personal time highly', 'balanced', '2025-04-27 11:38:22'),
(14, 4, 'Important, but I can be flexible when needed', 'flexible', '2025-04-27 11:38:22'),
(15, 4, 'I\'m willing to prioritize work during busy periods', 'dedicated', '2025-04-27 11:38:22'),
(16, 4, 'I enjoy when work and personal life blend together', 'integrated', '2025-04-27 11:38:22'),
(17, 5, 'Business professional (suits, formal wear)', 'traditional', '2025-04-27 11:38:22'),
(18, 5, 'Business casual', 'moderate', '2025-04-27 11:38:22'),
(19, 5, 'Casual (jeans, t-shirts)', 'casual', '2025-04-27 11:38:22'),
(20, 5, 'No dress code/anything goes', 'relaxed', '2025-04-27 11:38:22'),
(21, 6, 'Direct, face-to-face conversations', 'direct', '2025-04-27 11:38:22'),
(22, 6, 'Email or written communication', 'formal', '2025-04-27 11:38:22'),
(23, 6, 'Instant messaging and collaboration tools', 'tech-savvy', '2025-04-27 11:38:22'),
(24, 6, 'A mix of all communication methods', 'adaptable', '2025-04-27 11:38:22'),
(25, 7, 'Quick decisions, even with incomplete information', 'agile', '2025-04-27 11:38:23'),
(26, 7, 'Careful analysis with all available data', 'analytical', '2025-04-27 11:38:23'),
(27, 7, 'Collaborative decision-making with team input', 'collaborative', '2025-04-27 11:38:23'),
(28, 7, 'Balance of data and intuition', 'balanced', '2025-04-27 11:38:23'),
(29, 8, 'Critical - I want to work on cutting-edge projects', 'innovative', '2025-04-27 11:38:23'),
(30, 8, 'Important, but proven methods also matter', 'balanced', '2025-04-27 11:38:23'),
(31, 8, 'I prefer established processes and stability', 'traditional', '2025-04-27 11:38:23'),
(32, 8, 'I enjoy improving existing processes incrementally', 'incremental', '2025-04-27 11:38:23'),
(33, 9, 'Active team building and outdoor activities', 'energetic', '2025-04-27 11:38:23'),
(34, 9, 'Professional networking events', 'professional', '2025-04-27 11:38:23'),
(35, 9, 'Casual social gatherings like happy hours', 'social', '2025-04-27 11:38:23'),
(36, 9, 'I prefer minimal work social events', 'independent', '2025-04-27 11:38:23'),
(37, 10, 'Direct and straightforward', 'direct', '2025-04-27 11:38:23'),
(38, 10, 'Private, constructive conversations', 'considerate', '2025-04-27 11:38:23'),
(39, 10, 'Written feedback with time to process', 'reflective', '2025-04-27 11:38:23'),
(40, 10, 'Regular, structured performance reviews', 'structured', '2025-04-27 11:38:23');

-- --------------------------------------------------------

--
-- 表的结构 `culture_quiz_questions`
--

CREATE TABLE `culture_quiz_questions` (
  `question_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `culture_quiz_questions`
--

INSERT INTO `culture_quiz_questions` (`question_id`, `question_text`, `created_at`) VALUES
(1, 'What type of work environment do you prefer?', '2025-04-27 11:29:46'),
(2, 'How do you feel about working overtime?', '2025-04-27 11:29:46'),
(3, 'What management style do you work best with?', '2025-04-27 11:29:46'),
(4, 'How important is work-life balance to you?', '2025-04-27 11:29:46'),
(5, 'What type of dress code do you prefer?', '2025-04-27 11:29:46'),
(6, 'How do you prefer to communicate with colleagues?', '2025-04-27 11:29:46'),
(7, 'What is your preferred approach to decision making?', '2025-04-27 11:29:46'),
(8, 'How important is innovation in your work?', '2025-04-27 11:29:46'),
(9, 'What type of company social events do you enjoy?', '2025-04-27 11:29:46'),
(10, 'How do you prefer to receive feedback?', '2025-04-27 11:29:46');

-- --------------------------------------------------------

--
-- 表的结构 `gowork_workers`
--

CREATE TABLE `gowork_workers` (
  `worker_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT 'General',
  `can_manage_users` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `gowork_workers`
--

INSERT INTO `gowork_workers` (`worker_id`, `user_id`, `department`, `can_manage_users`, `created_at`) VALUES
(1, 11, 'License Department', 1, '2025-04-27 04:20:37');

-- --------------------------------------------------------

--
-- 表的结构 `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `salary_min` decimal(10,2) DEFAULT NULL,
  `salary_max` decimal(10,2) DEFAULT NULL,
  `location` varchar(100) NOT NULL,
  `requirements` text DEFAULT NULL,
  `job_type` varchar(50) NOT NULL,
  `categories` text DEFAULT NULL,
  `posted_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `state` varchar(50) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `jobs`
--

INSERT INTO `jobs` (`job_id`, `company_id`, `job_title`, `description`, `salary_min`, `salary_max`, `location`, `requirements`, `job_type`, `categories`, `posted_date`, `is_active`, `state`, `region`, `category`, `skills`) VALUES
(6, 1, 'Programming', 'Good Job', 100.00, 200.00, 'Sibu', 'Good in Programming', 'Full-time', 'Technology &amp; IT - Programming', '2025-04-27 05:42:55', 1, NULL, NULL, NULL, NULL),
(7, 4, 'Coding', 'Good Job', 100.00, 200.00, 'Kuching', 'Good in Coding', 'Part-time', 'Technology & IT - Programming', '2025-05-04 07:55:49', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`) VALUES
(13, 'xiaoying171158@gmail.com', '91bac0cbddf542746d9595e1379aa9aab5ca87ffa0f73f187fe310e36e84ff55', '2025-05-10 10:18:48'),
(15, 'jiaying090105@gmail.com', '051e4017b252bb1a701325f9b9fbb38e21c4a205d45140cd2da269b051d140ac', '2025-05-10 10:20:00');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `user_type` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `profile_picture`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$llRRhU71gXHqDOJPa6noh.NToPORuZx4seVqUJ88SSY12av/oKI7y', NULL, 4, '2025-03-30 14:36:04', '2025-03-31 01:35:05'),
(2, 'user', 'user@gmail.com', '$2y$10$llRRhU71gXHqDOJPa6noh.NToPORuZx4seVqUJ88SSY12av/oKI7y', NULL, 1, '2025-03-30 14:37:13', '2025-03-31 01:57:25'),
(3, 'company', 'company@gmail.com', '$2y$10$llRRhU71gXHqDOJPa6noh.NToPORuZx4seVqUJ88SSY12av/oKI7y', '67e96039d6af7_palm-tree.png', 2, '2025-03-30 14:40:01', '2025-03-31 01:57:31'),
(6, 'Hi', 'xiaoying171158@gmail.com', '$2y$10$cCm4UfDEN/r44t5Yx0UuKu/uCICVF7frSh52TXkYqakxjgJBPFsGC', '67fb7159aec01_WechatIMG1.jpeg', 1, '2025-04-13 04:48:29', '2025-05-10 16:18:48'),
(11, 'rrrr', 'worker@gmail.com', '$2y$10$IzJLxfU.3rlnRMdU9m/9ieky3x3Cz/8y7zNjbfBDp4PrBwdHromDm', NULL, 3, '2025-04-27 02:40:57', '2025-05-04 10:33:04'),
(13, 'Starlight', 'company3@gmail.com', '$2y$10$u/Pi2XHHSPm.bJoQtlPHieg1mCAHi5JcZneyublz.AkQU4p9LeSv.', NULL, 2, '2025-05-04 13:41:13', '2025-05-04 13:41:13'),
(16, 'testyeah', 'testuser@gmail.com', '$2y$10$cbwUYk7w6p.AOHBgPt2rY.7RTLr9pchfOgpYoD9/AqovaZMBLe1IK', '681b5b5840c2c_herta.jpg', 1, '2025-05-07 10:11:06', '2025-05-10 16:28:05'),
(17, 'JiaYing', 'jiaying090105@gmail.com', '$2y$10$w6PopnD0NWCtmr30MLfn4uMfdLNmdeMV7B5FPglCoTqmlpDU.VC6m', NULL, 1, '2025-05-10 14:11:23', '2025-05-10 16:20:00');

-- --------------------------------------------------------

--
-- 表的结构 `user_culture_results`
--

CREATE TABLE `user_culture_results` (
  `result_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `culture_profile` text NOT NULL COMMENT 'JSON object of culture profile data',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user_culture_results`
--

INSERT INTO `user_culture_results` (`result_id`, `user_id`, `culture_profile`, `created_at`, `updated_at`) VALUES
(1, 2, '{\"options\":{\"1\":\"2\",\"2\":\"5\",\"3\":\"10\",\"4\":\"13\",\"5\":\"18\",\"6\":\"21\",\"7\":\"25\",\"8\":\"30\",\"9\":\"34\",\"10\":\"39\"},\"values\":{\"focused\":\"focused\",\"dedicated\":\"dedicated\",\"structured\":\"structured\",\"balanced\":\"balanced\",\"moderate\":\"moderate\",\"direct\":\"direct\",\"agile\":\"agile\",\"professional\":\"professional\",\"reflective\":\"reflective\"}}', '2025-04-27 11:39:04', '2025-05-04 07:42:34'),
(2, 3, '{\"options\":{\"1\":\"1\",\"2\":\"5\",\"3\":\"9\",\"4\":\"13\",\"5\":\"17\",\"6\":\"21\",\"7\":\"25\",\"8\":\"29\",\"9\":\"33\",\"10\":\"37\"},\"values\":{\"collaborative\":\"collaborative\",\"dedicated\":\"dedicated\",\"autonomous\":\"autonomous\",\"balanced\":\"balanced\",\"traditional\":\"traditional\",\"direct\":\"direct\",\"agile\":\"agile\",\"innovative\":\"innovative\",\"energetic\":\"energetic\"}}', '2025-04-27 12:25:53', '2025-05-04 07:42:34'),
(3, 16, '{\"options\":{\"1\":\"1\",\"2\":\"5\",\"3\":\"9\",\"4\":\"13\",\"5\":\"17\",\"6\":\"22\",\"7\":\"25\",\"8\":\"29\",\"9\":\"33\",\"10\":\"37\"},\"values\":{\"collaborative\":\"collaborative\",\"dedicated\":\"dedicated\",\"autonomous\":\"autonomous\",\"balanced\":\"balanced\",\"traditional\":\"traditional\",\"formal\":\"formal\",\"agile\":\"agile\",\"innovative\":\"innovative\",\"energetic\":\"energetic\",\"direct\":\"direct\"}}', '2025-05-07 10:31:30', '2025-05-07 10:31:30'),
(4, 17, '{\"options\":{\"1\":\"1\",\"2\":\"5\",\"3\":\"9\",\"4\":\"13\",\"5\":\"17\",\"6\":\"22\",\"7\":\"25\",\"8\":\"29\",\"9\":\"33\",\"10\":\"37\"},\"values\":{\"collaborative\":\"collaborative\",\"dedicated\":\"dedicated\",\"autonomous\":\"autonomous\",\"balanced\":\"balanced\",\"traditional\":\"traditional\",\"formal\":\"formal\",\"agile\":\"agile\",\"innovative\":\"innovative\",\"energetic\":\"energetic\",\"direct\":\"direct\"}}', '2025-05-10 14:17:13', '2025-05-10 15:09:41');

-- --------------------------------------------------------

--
-- 表的结构 `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `completion_status` varchar(50) DEFAULT NULL,
  `education_highlights` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `full_name`, `phone`, `address`, `city`, `state`, `country`, `resume_path`, `education`, `institution`, `completion_status`, `education_highlights`, `skills`, `last_updated`) VALUES
(1, 2, 'JiaYing', '1111111', '11', '11', '11', '11', '67e95c25ca53c_Boarding Pass.pdf', '11', NULL, NULL, NULL, NULL, '2025-03-30 15:45:43'),
(4, 6, 'MaryLa', '087-8787877', 'No1, Jalan University', 'Sibu', 'Sarawak', 'Malaysia', '681767bb9649d_database_lab5.pdf', 'eqwewe', 'qeqe', 'In Progress', 'qeweq', 'Programming', '2025-05-04 14:34:30'),
(9, 11, 'addasd', '123', '11', '11', '11', '111', NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-27 02:40:57'),
(11, 16, 'Night', '087-8787867', 'No1, Jalan University', 'Sibu', 'Sarawak', 'Malaysia', '681b36f1cdfc8_lab 3.pdf', 'Bachelor of Computer Science', 'University of Technology Sarawak', 'In Progress', 'Learn to code in C++ and C languages', 'Good in coding', '2025-05-07 13:04:10'),
(12, 17, 'JiaYing', '087-8787866', 'No3, Jalan University2', 'Sibu', 'Sarawak', 'Malaysia', '681f7adfc5a15_lab 3.pdf', 'Bachelor of Computer Science', 'University of Technology Sarawak', 'In Progress', 'Learn coding', 'Good Coding, yeah', '2025-05-10 16:27:13');

--
-- 转储表的索引
--

--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 表的索引 `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `backup_career_history`
--
ALTER TABLE `backup_career_history`
  ADD PRIMARY KEY (`career_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `backup_user_profiles`
--
ALTER TABLE `backup_user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 表的索引 `career_history`
--
ALTER TABLE `career_history`
  ADD PRIMARY KEY (`career_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 表的索引 `culture_quiz_options`
--
ALTER TABLE `culture_quiz_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- 表的索引 `culture_quiz_questions`
--
ALTER TABLE `culture_quiz_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- 表的索引 `gowork_workers`
--
ALTER TABLE `gowork_workers`
  ADD PRIMARY KEY (`worker_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 表的索引 `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `idx_jobs_state` (`state`),
  ADD KEY `idx_jobs_category` (`category`);

--
-- 表的索引 `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 表的索引 `user_culture_results`
--
ALTER TABLE `user_culture_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_culture_results_user_id` (`user_id`);

--
-- 表的索引 `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `backup_career_history`
--
ALTER TABLE `backup_career_history`
  MODIFY `career_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `backup_user_profiles`
--
ALTER TABLE `backup_user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `career_history`
--
ALTER TABLE `career_history`
  MODIFY `career_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `culture_quiz_options`
--
ALTER TABLE `culture_quiz_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用表AUTO_INCREMENT `culture_quiz_questions`
--
ALTER TABLE `culture_quiz_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `gowork_workers`
--
ALTER TABLE `gowork_workers`
  MODIFY `worker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `user_culture_results`
--
ALTER TABLE `user_culture_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 限制导出的表
--

--
-- 限制表 `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `career_history`
--
ALTER TABLE `career_history`
  ADD CONSTRAINT `fk_career_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `culture_quiz_options`
--
ALTER TABLE `culture_quiz_options`
  ADD CONSTRAINT `culture_quiz_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `culture_quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- 限制表 `gowork_workers`
--
ALTER TABLE `gowork_workers`
  ADD CONSTRAINT `gowork_workers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- 限制表 `user_culture_results`
--
ALTER TABLE `user_culture_results`
  ADD CONSTRAINT `user_culture_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
