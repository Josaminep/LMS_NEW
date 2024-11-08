-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2024 at 05:14 AM
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
-- Database: `lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'teacher1', 'teacher1@example.com', '$2y$10$.l9rolRxzjHaGK84Fh6IhuzweLhDcg0D1f3nBuQzx4LTzPEWJnsnm', '2024-10-16 14:48:24'),
(2, 'admin', 'admin@gmail.com', '$2y$10$TmnlqDQVoJb3H7BA0uza7O3iwA9WEuKU8IixgWZRDLuIy.ue2rzA2', '2024-11-06 12:18:08');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `assessment_title` varchar(255) NOT NULL,
  `assessment_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `course_id`, `instructor_id`, `assessment_title`, `assessment_description`, `created_at`) VALUES
(9, 10, 6, 'Create a Simple Web Systrem', 'TEST', '2024-11-06 03:34:55'),
(10, 10, 6, 'test 223', 'haksog', '2024-11-06 03:42:25'),
(11, 9, 7, 'CREATE A WEBSITE SYSTEM WITH CRUD FUCNTIONALITY', 'Have a Create,Read, Update and Delete', '2024-11-06 08:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_feedback`
--

CREATE TABLE `assessment_feedback` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('instructor','student') NOT NULL,
  `comment` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_feedback`
--

INSERT INTO `assessment_feedback` (`id`, `submission_id`, `assessment_id`, `user_id`, `user_type`, `comment`, `created_at`) VALUES
(7, 8, 0, 5, 'instructor', 'check done, not enough 300 words', '2024-11-06 02:06:31'),
(8, 9, 0, 5, 'instructor', 'sadff', '2024-11-06 02:08:38'),
(9, 10, 0, 6, 'instructor', 'lack of design', '2024-11-06 02:51:06'),
(10, 11, 0, 6, 'instructor', 'AWRTRETERT', '2024-11-06 03:29:43'),
(11, 14, 0, 6, 'instructor', 'DONE, LACK OF DESIGN', '2024-11-06 03:35:31'),
(12, 15, 0, 6, 'instructor', 'kosdkprtjuerop', '2024-11-06 03:43:11'),
(13, 16, 0, 7, 'instructor', 'What&#039;s that', '2024-11-06 08:07:29');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_submissions`
--

CREATE TABLE `assessment_submissions` (
  `id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `submission_text` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_submissions`
--

INSERT INTO `assessment_submissions` (`id`, `assessment_id`, `student_id`, `course_id`, `submission_text`, `created_at`) VALUES
(12, 7, 6, 10, 'VISIT THE SITE', '2024-11-06 03:34:15'),
(13, 8, 6, 10, 'VISIT THE SITE', '2024-11-06 03:34:15'),
(14, 9, 6, 10, 'VISIT THE SIE', '2024-11-06 03:35:11'),
(15, 10, 6, 10, 'argtrrere', '2024-11-06 03:42:47'),
(16, 11, 6, 9, 'HABCDEFGHIJKLMNOP', '2024-11-06 08:07:14');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `student_id`, `content`, `created_at`) VALUES
(23, 14, 6, 'BRUHHHHHHHHHHHH', '2024-11-06 07:31:57'),
(24, 15, 6, 'comment 2 test 2 hi', '2024-11-06 07:44:22'),
(25, 15, 6, 'hahahakdog', '2024-11-06 08:04:36'),
(26, 16, 6, 'hakdog', '2024-11-06 08:07:50'),
(27, 15, 6, 'thanks', '2024-11-07 02:00:31'),
(28, 14, 6, 'HIIIIIIIIIIIIIIIIIII', '2024-11-07 02:00:51'),
(29, 16, 6, 'whyyy', '2024-11-07 02:08:45');

-- --------------------------------------------------------

--
-- Table structure for table `completed_modules`
--

CREATE TABLE `completed_modules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text DEFAULT NULL,
  `course_image` varchar(255) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `instructor_name` varchar(255) DEFAULT NULL,
  `overview` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `course_description`, `course_image`, `instructor_id`, `instructor_name`, `overview`) VALUES
(9, 'Database Management', 'Introduction to databases using SQL for data storage and retrieval. asd', 'uploads/DB.png', 7, NULL, NULL),
(10, 'Web Development Essentials', 'Learn HTML, CSS, and JavaScript to build and style websites.', 'uploads/web.png', 6, NULL, NULL),
(11, 'Introduction to Programming', 'Basics of coding with foundational languages like Python and Java.', 'uploads/Intro.png', 17, NULL, NULL),
(13, 'Cybersecurity Basics', 'Covers principles of securing networks and protecting data.', 'uploads/Cyber.png', 17, NULL, NULL),
(14, 'test', 'test', NULL, 22, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`) VALUES
(27, 1, 11),
(28, 2, 11),
(31, 1, 10),
(33, 4, 10);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_text` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `user_id`, `post_text`, `image`, `created_at`) VALUES
(1, 1, 'hello guys are you there? ', 'uploads/360_F_631479392_ZIDxzFDUoBYg8VchTRJ7TjQWlmD0J920.jpg', '2024-10-17 08:36:35'),
(2, 1, 'pogi ko talaga', 'uploads/462113568_1752756108829540_4771275651811918770_n.jpg', '2024-10-17 08:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `name`, `email`, `password`, `created_at`, `profile_picture`) VALUES
(5, 'John Doe', 'johndoe@mail.com', '$2y$10$UKKy5orhSD0pboTVAgjNautmJBV0T2bnYSiAMAjt8MSAbocWFXSUu', '2024-11-02 13:43:41', ''),
(6, 'John Smith', 'john.smith@example.com', '$2y$10$V3HGUNCV0zcCIQfi8oEiwuHR3SeL4Q.UyYTDKYSyNeLh.u8YGcG46', '2024-11-04 11:33:33', ''),
(7, 'Jane Doe', 'jane.doe@example.com', '$2y$10$ZNnEPdFc8TlkQNN23j15NepxZI5HtY8RFbTT5vw/5LTrsMZkAoitS', '2024-11-04 11:33:55', ''),
(8, 'Emily Johnson', 'emily.johnson@example.com', '$2y$10$RQHokWnB4vEU/lnxVNRT5.CpJ1kzVQm1RsKIJy7cceqgGBYtEfuWi', '2024-11-04 11:34:26', ''),
(17, 'John Doe', 'johndoe@example.com', '$2y$10$PS9QZjObUkri2F59sK1bs.yX86LFEEnYu8DCEjc6Th8vtlZ/fvdQy', '2024-11-06 11:06:16', ''),
(20, 'test', 'test@mail.com', '$2y$10$Va6HCZd0K11dwUQpyXX7DOZGlxtNwgxie6eldZCSs553kwubiojhm', '2024-11-06 11:09:35', 'uploads/professor.jpg'),
(22, 'asdfg', 'asdfg@yahoo.com', '$2y$10$Mx9EVK1aVXX1D.OAjzHScOukaBBZAhg6IYsTAgSWmmk0kd.Q3bxxK', '2024-11-06 11:11:19', 'uploads/profile_picture/professor.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `module_file` varchar(255) DEFAULT NULL,
  `video_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `module_file`, `video_file`, `created_at`) VALUES
(77, 11, 'Demo 1', NULL, 'uploads/video1.mp4', '2024-11-05 11:49:06'),
(78, 11, 'Demo 2', NULL, 'uploads/video2.mp4', '2024-11-05 11:55:00'),
(79, 11, 'Demo 3', NULL, 'uploads/How To Learn Programming for BEGINNERS_ (2022_2023)(720P_HD) (1).mp4', '2024-11-05 12:01:30'),
(80, 14, '', 'uploads/ITEW5-Web-Security-CSRF-CSP-in-PHP.pdf', NULL, '2024-11-08 04:02:49'),
(81, 13, '', 'uploads/NCSP-2023-2028-FINAL.pdf', NULL, '2024-11-08 04:10:22'),
(82, 9, '', 'uploads/ITEW5-Web-Security-CSRF-CSP-in-PHP.pdf', NULL, '2024-11-08 04:10:47');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `reply_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reply_content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`reply_id`, `comment_id`, `user_id`, `reply_content`, `created_at`) VALUES
(4, 8, 5, 'hdfhdh', '2024-11-06 02:08:58'),
(5, 8, 5, 'jhvkh', '2024-11-06 02:09:18'),
(6, 23, 6, 'SUPPPPPPPPPPPPPPPP', '2024-11-06 07:33:16'),
(7, 24, 6, 'reply 2 test 2 hi hello', '2024-11-06 07:44:43'),
(8, 24, 6, 'haist sa wakas', '2024-11-06 08:04:16'),
(9, 26, 7, 'you failed bruh', '2024-11-06 08:08:05'),
(10, 24, 6, 'congrats bruh', '2024-11-07 01:59:54'),
(11, 28, 6, 'WC', '2024-11-07 02:01:58'),
(12, 29, 7, 'idk man', '2024-11-07 02:09:04');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `username`, `email`, `password`, `created_at`, `name`, `code`, `approved`, `reset_token`) VALUES
(1, 'mhernandez', 'maria.h@example.com', '$2y$10$6Vd9D/p5MoFe9A9OAYmzauuexnpYH4betvp1gp1wga6dbwaOJ5sPy', '2024-11-05 01:26:01', 'Maria Hernandez', '', 0, '0'),
(2, 'alexj	', 'alex.j@example.com', '$2y$10$LV3nJNUbIkkAM/rypDwO8u4giknffpbVEdcUsBm4aUrr34qVJwk7u', '2024-11-05 01:26:27', 'Alex Johnson', '', 0, '0'),
(3, 'sarahlee', 'sarah.lee@example.com', '$2y$10$xOnTR9ZBofT.khd7a9wheur0wE4IKoQwnX7SmqgzNyyjtHwlTFw9u', '2024-11-05 01:26:54', 'Sarah Lee', '', 0, '0'),
(4, 'seeyah', 'jeanseeyah@mail.com', '$2y$10$bQPTYSrmJT8NHym3UQkBuenjiQe6FKpFCRMDUJF3dP/2w9MUsKRx.', '2024-11-06 02:29:14', 'Jean Seeyah', 'XZA735', 0, '0'),
(5, 'adf', 'admin@gmail.com', '$2y$10$9N9MtE3qfAEO5dKBQzHsCu5082HlFrZyxL7tuvOsTxSnOs0iRCWIG', '2024-11-06 02:31:45', 'ads', 'GMU740', 1, '0');

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollments`
--

CREATE TABLE `student_enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessment_feedback`
--
ALTER TABLE `assessment_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `completed_modules`
--
ALTER TABLE `completed_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`reply_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `assessment_feedback`
--
ALTER TABLE `assessment_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `completed_modules`
--
ALTER TABLE `completed_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `completed_modules`
--
ALTER TABLE `completed_modules`
  ADD CONSTRAINT `completed_modules_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `completed_modules_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD CONSTRAINT `student_enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
