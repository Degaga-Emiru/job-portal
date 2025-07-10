-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 11:12 AM
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
-- Database: `job_portal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Reviewed','Rejected','Accepted') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `user_id`, `resume_path`, `cover_letter`, `applied_at`, `status`) VALUES
(1, 10, 10, 'resume_10_1748809950.docx', 'hsdhsdsd dsadsssssdd', '2025-06-01 20:32:30', 'Pending'),
(2, 6, 10, 'resume_10_1748810209.pdf', 'gdsdd ddwdwdad dsdsddsad adss', '2025-06-01 20:36:49', 'Pending'),
(3, 6, 10, 'resume_10_1748810461.pdf', 'gdsdd ddwdwdad dsdsddsad adss', '2025-06-01 20:41:01', 'Pending'),
(4, 6, 10, 'resume_10_1748810486.pdf', 'gdsdd ddwdwdad dsdsddsad adss', '2025-06-01 20:41:26', 'Pending'),
(5, 6, 10, 'resume_10_1748810550.pdf', 'gdsdd ddwdwdad dsdsddsad adss fff', '2025-06-01 20:42:30', 'Pending'),
(6, 10, 1, 'resume_1_1748839689.docx', 'gfasfsf', '2025-06-02 04:48:09', 'Pending'),
(7, 6, 12, 'resume_12_1748845088.pdf', 'i\' m intereseted to this job', '2025-06-02 06:18:08', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','replied') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'Degaga Emiru', 'degagaemiru996@gmail.com', 'issue of portal', 'i\'m not able to access my portal what is the case . when i login it doesn\'t display the correct jobs for me why i need a messages', '2025-05-31 07:43:46', 'unread'),
(2, 'Mihretu Ayele', 'mihretu@gmail.com', 'Payment issue', 'when i want to pay my monthly payment it doesn\'t work good please i need some help', '2025-05-31 07:45:56', 'unread'),
(3, 'Mihretu Ayele', 'mihretu@gmail.com', 'Payment issue', 'when i want to pay my monthly payment it doesn\'t work good please i need some help', '2025-05-31 07:49:50', 'unread'),
(4, 'Dawit', 'dawit@gmial.com', 'issue of portal', 'my portal is not working wekll fdnfdfdfdf', '2025-06-02 04:49:17', 'unread'),
(5, 'Degaga', 'degaga@gmial.com', 'portal issue', 'my portal is not working well ...', '2025-06-02 06:18:52', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `company` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `salary` varchar(50) DEFAULT NULL,
  `type` enum('Full-time','Part-time','Contract','Internship') NOT NULL,
  `category` varchar(50) NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `employer_id`, `title`, `description`, `company`, `location`, `salary`, `type`, `category`, `posted_at`, `deadline`) VALUES
(1, 4, 'Web development', 'web development full stack manage and', 'alx', 'Hawassa', '50000', 'Contract', 'software development', '2025-05-31 04:54:01', '2025-05-05'),
(2, 4, 'Grapahics designer', 'dfgfgdfgdfgdfgfdgf ggfgsfdgsdfg f gsfgfgs f', 'D-tech', 'addis', '40000', 'Part-time', 'marketing', '2025-05-31 04:57:36', '2025-03-31'),
(3, 4, 'Software developer', 'full stack software developer with passionate to learn new skills and language', 'Ashawa Developers', 'Addis Ababa', '50,000', 'Part-time', 'Web Industry', '2025-05-31 06:16:14', '2025-04-05'),
(4, 4, 'Digital Marketing', 'Digital marketing with experience of 3+ years', 'UNHCR', 'Paris', '45,0000', 'Full-time', 'Digital marketing', '2025-05-31 06:55:00', '2025-05-06'),
(5, 4, 'Virtual Assistant', 'Provide Admintstrative support, manage schedule, and handle comunication tasks for client remotely', 'Growth Troops', 'Addis Ababa', '10,000', 'Internship', 'Customer success Manager', '2025-05-31 07:12:48', '2026-04-05'),
(6, 4, 'Amharic Transcriber', 'Transcribe audio content in amharic, ensuring accuracy and adherence to guidelines', 'Sigma AI', 'Addis Ababa', '54,000', 'Contract', 'Transcriber', '2025-05-31 07:16:57', '2026-05-06'),
(9, 4, 'Video Editor', 'editing the video with high quality like 5k and with miracle quality', 'Ashawa Developers', 'London', '45,000', 'Contract', 'Graphics', '2025-05-31 15:28:45', '2025-03-04'),
(10, 4, 'Video Graphics', 'editing the video with high quality like 5k and with miracle quality', 'Harvard', 'London', '45,000', 'Contract', 'Graphics', '2025-05-31 15:29:52', '2025-03-04'),
(11, 4, '11. Job Title: Sales Executive', 'Description: Sells company products/services, meets sales targets, and builds customer relationships.', 'Safaricom', 'Nairobi, Kenya', '1,200,000–1,800,000 per year', 'Full-time', 'Sales', '2025-06-02 08:29:43', '2025-05-06'),
(12, 4, 'Business Analyst', 'Description: Analyzes business processes and suggests data-driven solutions for improvement.', 'Emirates Group', 'UAE Dubai', '56,000-678,000', 'Contract', 'Sales', '2025-06-02 08:31:42', '2025-03-06'),
(13, 4, 'Job Title: Mechanical Engineer', 'Description: Designs and tests mechanical devices, including tools, engines, and machines.', 'BMW Group', 'Munich, Germany', '60,000-80,000', 'Internship', 'Engineering', '2025-06-02 08:34:30', '2025-04-04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('job_seeker','employer') NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `user_type`, `full_name`, `phone`, `bio`, `skills`, `education`, `created_at`) VALUES
(1, 'degaga_emiru', 'degagaemiru@gmail.com', '$2y$10$swB4dS./C7kG2ba96cv1g.kq2Uz9qZh4xy7jL3klwgKE1TlsLpL3a', 'job_seeker', 'Degaga Emiru', '0943091493', 'i\'m web developer passionate full stack developer', 'javascript, c++, css, html, php', 'bsc  degree, msc CS', '2025-05-30 18:41:38'),
(2, 'Sisay', 'sisaywolde@gmail.com', '$2y$10$yRGpdDcQH7ax2mco949ZtOFaARhHn0CNvPkNrGLStp3GwaJ..bYN2', 'employer', 'Sisay', 'Wolde', NULL, NULL, NULL, '2025-05-30 18:47:12'),
(3, 'mihiretu', 'mihiretu@gmail.com', '$2y$10$.EV1D90A4nn4CgX4Ntf7Eu6OUAoK4qpobVZloz7/A9Y6R6vRsb7yy', 'employer', 'Mihiretu Ayele', '0943091493', NULL, NULL, NULL, '2025-05-30 20:21:23'),
(4, 'Dawit', 'dawit@gmail.com', '$2y$10$Kxk65JH2dsICNfcp2kBPROsfISX4EUOIT5.M6/kvtn7JLHMVf/Oo2', 'employer', 'Dawit paulos', '090008884', 'i\'am patient adminstartor', 'php, c++, mysql , java', 'bsc  degree', '2025-05-31 04:04:48'),
(5, 'girma00_tes', 'girma@gmial.com', '$2y$10$u4ZNdMTJJMAxNAoJiFsh5uyf12eUCLxV93U47Y.tUXxZ/aA6IbDHm', 'employer', 'Girma Tesfaye', '0934393943', 'i\'m passionate full stack developer', 'javascript, c++, css, html, flutter, java, bootstarp, react js, node js', 'Msc Software Engineering', '2025-05-31 07:52:52'),
(6, 'solomon', 'sol@gmail.com', '$2y$10$2CFllAsyLNESLYuGF8uR1uSsKxU9P25cNtThSapdmRNEgTGzE7AGe', 'job_seeker', 'Solomon Ayele', '0984733432', NULL, NULL, NULL, '2025-05-31 15:12:04'),
(7, 'abdi', 'abdi@gmail.com', '$2y$10$Kf8yGE.SnoZHGQ8ovNewbO5REvq5FpKirIIk2FFs6mEPYsh.lt/EC', 'job_seeker', 'Abdi Gemechu', '090000000', NULL, NULL, NULL, '2025-05-31 15:25:46'),
(8, 'ayantu', 'ayantu@gmail.com', '$2y$10$yLljQO//FJoAxYMJ6yjOQenzDcV51ptBgW/x4PYlKoVeuMXwYYp8e', 'employer', 'Ayantu Emiru', '098845433', NULL, NULL, NULL, '2025-05-31 16:08:41'),
(9, 'abdalla', 'abdala@gmail.com', '$2y$10$hautEQx9udRsZwmyd0NQqunHXtHQRxXaFIdsSfkg5w/9/saXksFqm', 'job_seeker', 'Abdalla Gamtaa', '0982081152', 'hacker', 'html, css', 'phd', '2025-06-01 19:24:13'),
(10, 'segni', 'segni@gmail.com', '$2y$10$UA1N8ITd5g02Pgk12TMEXOqbqit5yJSCyPBoL5ufPnQnWYAsRI4za', 'job_seeker', 'Segni Igazu', '0976543212', NULL, NULL, NULL, '2025-06-01 20:23:41'),
(11, 'abdii', 'abdis@gmial.com', '$2y$10$ShN7sniEZYffyJHBRyo3KecgWV8NPY/w7vbwJ.pXNwJ4HJs8YErQa', 'job_seeker', 'Abdi Badhasa', '098374233', NULL, NULL, NULL, '2025-06-02 04:41:44'),
(12, 'dego', 'dego@gmail.com', '$2y$10$cChjfz.jyE8ekAnN8V4yP.joVv/ayI2Dwgi1oBJ3ppdYf551ZY9mG', 'job_seeker', 'Degaga Emiru Abate', '09836252521', NULL, NULL, NULL, '2025-06-02 06:15:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`);

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
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
