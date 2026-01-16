<?php require_once('../final/inc/auth_guard.php'); ?>
<?php
require_once("../DB/DB_open.php");

// 分頁設定
$limit = 5; // 每頁顯示筆數
if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; };  
$start_from = ($page-1) * $limit;  

// 查詢總筆數
$sql_count = "SELECT COUNT(*) FROM students";
$rs_result = mysqli_query($link, $sql_count);  
$row = mysqli_fetch_row($rs_result);  
$total_records = $row[0];  
$total_pages = ceil($total_records / $limit); 

// 查詢資料
$sql = "SELECT * FROM students LIMIT $start_from, $limit";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>通訊錄管理</title>
    <link rel="stylesheet" href="css/navCSS.css">
    <link rel="stylesheet" href="css/indexCSS.css">
    <link rel="stylesheet" href="css/contacts.css">
    <style>

    </style>
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents" class="wide-content">
            <h2>通訊錄管理</h2>
            <div style="margin-bottom: 20px; color: #888;">
                記錄總數: <?php echo $total_records; ?> 筆
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="10%">編號</th>
                        <th width="15%">姓名</th>
                        <th width="25%">住址</th>
                        <th width="15%">生日</th>
                        <th width="12%">帳號</th>
                        <th width="10%">密碼</th>
                        <th width="13%">功能</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['sno']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['birthday']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['password']); ?></td>
                        <td>
                            <a href="edit.php?action=edit&id=<?php echo $row['sno']; ?>" class="action-link edit-btn">編輯</a>
                            <a href="edit.php?action=delete&id=<?php echo $row['sno']; ?>" class="action-link del-btn" onclick="return confirm('確定要刪除嗎？');">刪除</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php 
                // 最前頁
                if($page > 1){
                    echo "<a href='contacts.php?page=1'>最前頁</a>";
                    echo "<a href='contacts.php?page=".($page-1)."'>上一頁</a>";
                }
                
                // 數字頁碼
                for ($i=1; $i<=$total_pages; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    echo "<a href='contacts.php?page=".$i."' class='".$active."'>".$i."</a>"; 
                }

                // 最後頁
                if($page < $total_pages){
                    echo "<a href='contacts.php?page=".($page+1)."'>下一頁</a>";
                    echo "<a href='contacts.php?page=".$total_pages."'>最後頁</a>";
                }
                ?>
            </div>

            <div class="footer-links" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="index.php">首頁</a> |
                <a href="add.php">新增聯絡資料</a> |
                <a href="search.php">搜尋通訊錄</a>
            </div>
            
            <div style="margin-top: 40px; font-size: 0.9em; color: #888;">
                班級：你的班級 / 姓名：你的姓名 / 學號：你的學號 <span style="color: #e84393;">●</span>
            </div>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
