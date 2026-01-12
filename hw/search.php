<?php require_once('../final/auth_check.php'); ?>
<?php
require_once("../DB/DB_open.php");

$keyword = "";
$search_result = null;

if (isset($_GET["keyword"])) {
    $keyword = $_GET["keyword"];
    if ($keyword != "") {
        $sql = "SELECT * FROM students WHERE name LIKE '%$keyword%' OR address LIKE '%$keyword%' OR sno LIKE '%$keyword%'";
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
                    搜尋結果: <?php echo mysqli_num_rows($search_result); ?> 筆
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
