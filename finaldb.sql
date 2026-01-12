-- 建立資料庫
CREATE DATABASE IF NOT EXISTS `finaldb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `finaldb`;

-- --------------------------------------------------------

--
-- 資料表結構 `students`
--

CREATE TABLE IF NOT EXISTS `students` (
  `sno` char(4) NOT NULL COMMENT '學號',
  `name` varchar(10) NOT NULL COMMENT '姓名',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `birthday` date NOT NULL COMMENT '生日',
  `username` char(10) NOT NULL COMMENT '帳號',
  `password` char(10) NOT NULL COMMENT '密碼',
  PRIMARY KEY (`sno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 轉存資料表中的資料 `students`
--

INSERT INTO `students` (`sno`, `name`, `address`, `birthday`, `username`, `password`) VALUES
('S001', '陳會安', '新北市五股區', '2000-10-05', 'hueyan', '1234'),
('S002', '江小魚', '新北市中和區', '1999-01-02', 'smallfish', '1234'),
('S003', '周傑倫', '台北市松山區', '2001-05-10', 'jay', '1234'),
('S004', '蔡依玲', '台北市大安區', '1998-07-22', 'jolin', '1234'),
('S005', '張會妹', '台北市信義區', '1999-03-01', 'chiang', '1234'),
('S006', '張無忌', '台北市內湖區', '2000-03-01', 'chiang1234', '1234');

-- --------------------------------------------------------

--
-- 資料表結構 `sdgs`
--

CREATE TABLE IF NOT EXISTS `sdgs` (
  `sdg` int(2) NOT NULL COMMENT '編號',
  `img` char(30) NOT NULL COMMENT '圖片檔名',
  `title` varchar(100) NOT NULL COMMENT '標題',
  `detail` varchar(300) NOT NULL COMMENT '詳細說明',
  PRIMARY KEY (`sdg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 轉存資料表中的資料 `sdgs`
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
-- 資料表結構 `album`
--

CREATE TABLE IF NOT EXISTS `album` (
  `album_id` int(11) NOT NULL AUTO_INCREMENT,
  `album_date` datetime DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `picurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



