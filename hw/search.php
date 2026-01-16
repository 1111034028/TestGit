<?php require_once('../final/inc/auth_guard.php'); ?>
<?php
require_once("../DB/DB_open.php");

$keyword = "";
$search_result = null;
$total_records = 0;
$total_pages = 0;
$page = 1;
$limit = 5;

if (isset($_GET["keyword"])) {
    $keyword = $_GET["keyword"];
    if ($keyword != "") {
        // 分頁設定
        if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; };  
        $start_from = ($page-1) * $limit;  

        // 查詢總筆數
        $sql_count = "SELECT COUNT(*) FROM students WHERE name LIKE '%$keyword%' OR address LIKE '%$keyword%' OR sno LIKE '%$keyword%'";
        $rs_result = mysqli_query($link, $sql_count);  
        $row_count = mysqli_fetch_row($rs_result);  
        $total_records = $row_count[0];  
        $total_pages = ceil($total_records / $limit); 

        // 查詢資料 (含分頁)
        $sql = "SELECT * FROM students WHERE name LIKE '%$keyword%' OR address LIKE '%$keyword%' OR sno LIKE '%$keyword%' LIMIT $start_from, $limit";
        $search_result = mysqli_query($link, $sql);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搜尋通訊錄</title>
    <link rel="stylesheet" href="css/navCSS.css">
    <link rel="stylesheet" href="css/indexCSS.css">
    <link rel="stylesheet" href="css/contacts.css">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents" class="wide-content">
            <h2>搜尋通訊錄</h2>
            
            <div class="search-box">
                <form action="search.php" method="get">
                    <input type="text" name="keyword" placeholder="請輸入關鍵字 (學號、姓名或住址)" value="<?php echo htmlspecialchars($keyword); ?>" style="width: 300px; display: inline-block;">
                    <input type="submit" value="搜尋" style="display: inline-block;">
                </form>
            </div>

            <?php if ($search_result): ?>
                <div style="margin-bottom: 20px; color: #888;">
                    搜尋結果: <?php echo $total_records; ?> 筆
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
                    if (mysqli_num_rows($search_result) > 0) {
                        while ($row = mysqli_fetch_assoc($search_result)) {
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
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>查無資料</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>

                <!-- 分頁導覽 -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php 
                    $params = "keyword=" . urlencode($keyword);
                    
                    // 最前頁
                    if($page > 1){
                        echo "<a href='search.php?$params&page=1'>最前頁</a>";
                        echo "<a href='search.php?$params&page=".($page-1)."'>上一頁</a>";
                    }
                    
                    // 數字頁碼
                    for ($i=1; $i<=$total_pages; $i++) {
                        $active = ($i == $page) ? "active" : "";
                        echo "<a href='search.php?$params&page=".$i."' class='".$active."'>".$i."</a>"; 
                    }

                    // 最後頁
                    if($page < $total_pages){
                        echo "<a href='search.php?$params&page=".($page+1)."'>下一頁</a>";
                        echo "<a href='search.php?$params&page=".$total_pages."'>最後頁</a>";
                    }
                    ?>
                </div>
                <?php endif; ?>

            <?php endif; ?>

            <div class="footer-links" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="index.php">首頁</a> |
                <a href="add.php">新增聯絡資料</a> |
                <a href="search.php">搜尋通訊錄</a> |
                <a href="contacts.php">回通訊錄頁面</a>
            </div>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
