-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-01-07 18:35:25
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `mydb`
--
CREATE DATABASE IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `mydb`;

-- --------------------------------------------------------

--
-- 資料表結構 `album`
--
-- 建立時間： 2024-12-25 02:04:38
-- 最後更新： 2025-01-07 11:53:55
--

DROP TABLE IF EXISTS `album`;
CREATE TABLE `album` (
  `album_id` int(11) NOT NULL,
  `album_date` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `picurl` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `album`
--

TRUNCATE TABLE `album`;
--
-- 傾印資料表的資料 `album`
--

INSERT INTO `album` (`album_id`, `album_date`, `location`, `title`, `picurl`) VALUES
(1, '2024-12-25 12:25:25', 'A1', 'minion1', 'minion1.png'),
(2, '2024-12-25 12:25:25', 'A1', 'minion2', 'minion2.png'),
(3, '2024-12-25 12:25:25', 'A1', 'minion3', 'minion3.png'),
(4, '2024-12-25 12:25:25', 'A1', 'minion4', 'minion4.png'),
(5, '2024-12-25 12:25:25', 'A1', 'minion5', 'minion5.png'),
(6, '2024-12-25 12:25:25', 'A1', 'minion6', 'minion6.png'),
(7, '2024-12-25 12:25:25', 'A1', 'minion7', 'minion7.png'),
(8, '2024-12-25 12:25:25', 'A1', 'minion8', 'minion8.png'),
(9, '2024-12-25 12:25:25', 'A1', 'minion9', 'minion9.png');

-- --------------------------------------------------------

--
-- 資料表結構 `animals`
--
-- 建立時間： 2024-12-29 08:37:05
-- 最後更新： 2025-01-07 17:21:24
--

DROP TABLE IF EXISTS `animals`;
CREATE TABLE `animals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `animals`
--

TRUNCATE TABLE `animals`;
--
-- 傾印資料表的資料 `animals`
--

INSERT INTO `animals` (`id`, `name`, `type`, `image`, `description`) VALUES
(4, 'Jerry', '唾液科', 'https://image1.gamme.com.tw/news2/2018/23/75/pZqao6OXkqOaqA.jpg', '吐口水'),
(5, 'Tom', '整形外科', 'https://cdn.cybassets.com/s/files/26010/ckeditor/pictures/content_aeacea87-af6a-4d93-a0bc-c0d397644bbe.jpg', '太可愛'),
(7, 'Betty', '精神科', 'https://img.shoplineapp.com/media/image_clips/620b69a4b62f160029421122/original.png?1644915107', '沒精神'),
(10, 'Nacy', '口腔科', 'https://cdn2.ettoday.net/images/6145/6145394.jpg', '太吵'),
(11, 'Cherry', '口腔科', 'https://www.hikari-tw.com/image/catalog/article/5.small/ham-5.png', '口腔發炎'),
(12, 'Merry', '整形外科', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSgpzC9-X-vo8FQgSyxR8DY6neGB5tA9bqxEg&s', '刺太多');

-- --------------------------------------------------------

--
-- 資料表結構 `prescriptions`
--
-- 建立時間： 2025-01-02 13:52:10
-- 最後更新： 2025-01-07 15:10:28
--

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `animal_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `useage` varchar(255) NOT NULL,
  `schedule` varchar(255) NOT NULL,
  `side_effects` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `prescriptions`
--

TRUNCATE TABLE `prescriptions`;
--
-- 傾印資料表的資料 `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `animal_id`, `medicine_name`, `dosage`, `useage`, `schedule`, `side_effects`, `created_at`) VALUES
(2, 4, '止吐藥', '1', '口服', '早餐後', '笑', '2025-01-02 13:54:05'),
(3, 4, '消炎藥', '3', '口服', '三餐飯後', '嗜睡', '2025-01-03 07:37:35'),
(4, 4, '消炎藥', '3', '塗抹在患部', '隨時', '癢', '2025-01-03 08:08:48'),
(5, 4, '止吐藥', '3', '口服', '早餐', '', '2025-01-07 15:10:28');

-- --------------------------------------------------------

--
-- 資料表結構 `sdgs`
--
-- 建立時間： 2024-12-21 06:13:55
--

DROP TABLE IF EXISTS `sdgs`;
CREATE TABLE `sdgs` (
  `sdg` int(2) NOT NULL,
  `img` char(30) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `detail` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `sdgs`
--

TRUNCATE TABLE `sdgs`;
--
-- 傾印資料表的資料 `sdgs`
--

INSERT INTO `sdgs` (`sdg`, `img`, `title`, `detail`) VALUES
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

-- --------------------------------------------------------

--
-- 資料表結構 `students`
--
-- 建立時間： 2024-12-21 06:13:55
-- 最後更新： 2025-01-07 17:31:51
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `sno` char(4) NOT NULL,
  `name` varchar(10) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `username` char(10) DEFAULT NULL,
  `password` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `students`
--

TRUNCATE TABLE `students`;
--
-- 傾印資料表的資料 `students`
--

INSERT INTO `students` (`sno`, `name`, `address`, `birthday`, `username`, `password`) VALUES
('S001', '陳會安', '新北市五股區', '2000-10-05', 'hueyan', '1234'),
('S002', '江小魚', '新北市中和區', '1999-01-02', 'smallfish', '1234'),
('S003', '周傑倫', '台北市松山區', '2001-05-10', 'jay', '1234'),
('S004', '蔡依玲', '台北市大安區', '1998-07-22', 'jolin', '1234'),
('S005', '張會妹', '台北市信義區', '1999-03-01', 'chiang', '1234'),
('S006', '張無忌', '台北市內湖區', '2000-03-10', 'chiang1234', '1234'),
('S007', 'Nacy', '台中市南屯區', '2025-01-01', 'Nacy', '1234');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`album_id`);

--
-- 資料表索引 `animals`
--
ALTER TABLE `animals`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `animal_id` (`animal_id`);

--
-- 資料表索引 `sdgs`
--
ALTER TABLE `sdgs`
  ADD PRIMARY KEY (`sdg`);

--
-- 資料表索引 `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`sno`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `album`
--
ALTER TABLE `album`
  MODIFY `album_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `animals`
--
ALTER TABLE `animals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
