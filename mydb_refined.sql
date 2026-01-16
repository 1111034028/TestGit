-- Refined Database Schema and Data for Music Stream Project

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `mydb`
CREATE DATABASE IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mydb`;

-- --------------------------------------------------------

-- Table structure for `students`
CREATE TABLE IF NOT EXISTS `students` (
  `sno` char(4) NOT NULL COMMENT '學號',
  `name` varchar(10) NOT NULL COMMENT '姓名',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `birthday` date NOT NULL COMMENT '生日',
  `username` char(10) NOT NULL COMMENT '帳號',
  `password` char(10) NOT NULL COMMENT '密碼',
  `role` varchar(10) NOT NULL DEFAULT 'user',
  `picture` varchar(255) DEFAULT NULL COMMENT '頭像路徑',
  PRIMARY KEY (`sno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sample data for `students`
INSERT IGNORE INTO `students` (`sno`, `name`, `address`, `birthday`, `username`, `password`, `role`, `picture`) VALUES
('S002', '江小魚', '新北市中和區', '1999-01-02', 'smallfish', '1234', 'user', NULL),
('S003', '周傑倫', '台北市松山區', '2001-05-10', 'jay', '1234', 'user', NULL),
('S100', '黃亮鈞', '台中市南區仁義街235號三樓之五', '2005-05-24', 'xyz', '1234', 'admin', NULL),
('S106', '黃亮鈞', '台中市南區', '2026-01-02', 'x', 'x', 'admin', NULL);

-- --------------------------------------------------------

-- Table structure for `songs`
CREATE TABLE IF NOT EXISTS `songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploader_id` char(4) DEFAULT NULL,
  `upload_date` datetime NOT NULL,
  `play_count` int(11) DEFAULT 0,
  `genre` varchar(50) DEFAULT NULL,
  `cover_image` longblob DEFAULT NULL,
  `cover_type` varchar(50) DEFAULT NULL,
  `last_played_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `songs_uploader_fk` FOREIGN KEY (`uploader_id`) REFERENCES `students` (`sno`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `playlists`
CREATE TABLE IF NOT EXISTS `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(4) NOT NULL COMMENT '使用者ID',
  `name` varchar(100) NOT NULL COMMENT '歌單名稱',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `playlists_user_fk` FOREIGN KEY (`user_id`) REFERENCES `students` (`sno`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

-- Table structure for `playlist_songs`
CREATE TABLE IF NOT EXISTS `playlist_songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `ps_playlist_fk` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ps_song_fk` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

-- Table structure for `music_marks`
CREATE TABLE IF NOT EXISTS `music_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(4) NOT NULL,
  `song_id` int(11) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `message` text DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `marks_user_fk` FOREIGN KEY (`user_id`) REFERENCES `students` (`sno`) ON DELETE CASCADE,
  CONSTRAINT `marks_song_fk` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `contact_messages`
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(4) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'new',
  PRIMARY KEY (`id`),
  CONSTRAINT `contact_user_fk` FOREIGN KEY (`user_id`) REFERENCES `students` (`sno`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `contact_replies`
CREATE TABLE IF NOT EXISTS `contact_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `sender_role` enum('admin','user') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `replies_message_fk` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `remote_sessions`
CREATE TABLE IF NOT EXISTS `remote_sessions` (
  `session_token` varchar(32) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'waiting',
  `current_state` longtext DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `remote_commands`
CREATE TABLE IF NOT EXISTS `remote_commands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_token` varchar(32) NOT NULL,
  `command` varchar(50) NOT NULL,
  `payload` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `commands_session_fk` FOREIGN KEY (`session_token`) REFERENCES `remote_sessions` (`session_token`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `album`
CREATE TABLE IF NOT EXISTS `album` (
  `album_id` int(11) NOT NULL AUTO_INCREMENT,
  `album_date` datetime DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `picurl` varchar(255) NOT NULL,
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for `sdgs`
CREATE TABLE IF NOT EXISTS `sdgs` (
  `sdg` int(2) NOT NULL,
  `img` char(30) NOT NULL,
  `title` varchar(100) NOT NULL,
  `detail` varchar(300) NOT NULL,
  PRIMARY KEY (`sdg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for `sdgs`
INSERT IGNORE INTO `sdgs` (`sdg`, `img`, `title`, `detail`) VALUES
(1, '1.jpg', 'SDG 1 終結貧窮', '消除各地一切形式的貧窮'),
(2, '2.jpg', 'SDG 2 消除飢餓', '確保糧食安全，消除飢餓，促進永續農業'),
(3, '3.jpg', 'SDG 3 健康與福祉', '確保及促進各年齡層健康生活與福祉'),
(4, '4.jpg', 'SDG 4 優質教育', '確保有教無類、公平以及高品質的教育，及提倡終身學習'),
(5, '5.jpg', 'SDG 5 性別平權', '實現性別平等，並賦予婦女權力'),
(6, '6.jpg', 'SDG 6 淨水及衛生', '確保所有人都能享有水、衛生及其永續管理'),
(7, '7.jpg', 'SDG 7 可負擔的潔淨能源', '確保所有的人都可取得負擔得起、可靠、永續及現代的能源'),
(8, '8.jpg', 'SDG 8 合適的工作及經濟成長', '促進包容且永續的經濟成長，讓每個人都有一份好工作'),
(9, '9.jpg', 'SDG 9 工業化、創新及基礎建設', '建立具有韌性的基礎建設，促進包容且永續的工業，並加速創新'),
(10, '10.jpg', 'SDG 10 減少不平等', '減少國內及國家間的不平等'),
(11, '11.jpg', 'SDG 11 永續城鄉', '建構具包容、安全、韌性及永續特質的城市與鄉村'),
(12, '12.jpg', 'SDG 12 責任消費及生產', '促進綠色經濟，確保永續消費及生產模式'),
(13, '13.jpg', 'SDG 13 氣候行動', '完備減緩調適行動，以因應氣候變遷及其影響'),
(14, '14.jpg', 'SDG 14 保育海洋生態', '保育及永續利用海洋生態系，以確保生物多樣性並防止海洋環境劣化'),
(15, '15.jpg', 'SDG 15 保育陸域生態', '保育及永續利用陸域生態系，確保生物多樣性並防止土地劣化'),
(16, '16.jpg', 'SDG 16 和平、正義及健全制度', '促進和平多元的社會，確保司法平等，建立具公信力且廣納民意的體系'),
(17, '17.jpg', 'SDG 17 多元夥伴關係', '建立多元夥伴關係，協力促進永續願景');

COMMIT;
