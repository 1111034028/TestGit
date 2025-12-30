<?php
// 編輯相簿
if (!empty($_GET['id'])) {
    require_once("DB_open.php");

    // 查詢 id 參數所指定編號的記錄，從資料庫將原有的資料取出
    $sql = 'SELECT * FROM album WHERE album_id = "' . $_GET["id"] . '" ';
    // echo $sql;
    $result = mysqli_query($link, $sql);
    // 將查詢到的資料（只有一筆）放在 $row 陣列
    $row = mysqli_fetch_array($result);

    require_once("DB_close.php"); // 引入資料庫關閉設定檔
} else {
    // 如果沒有 id 參數，表示此為錯誤執行，所以轉向回主頁面
    header("Location:album.php");
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>相簿管理</title>
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/albumCSS.css" media="all">
</head>
<body>
<div id="wrap">
    <!-- 功能列表 -->
    <header id="header" class="clearheader">
        <?php include "nav.html"; ?>
    </header>

    <!-- 內容區域 -->
    <main id="contents">
        <table width="90%" border="0" align="center" cellpadding="4" cellspacing="0">
            <tr>
                <td>
                    <div class="subjectDiv">相簿管理 - 編輯相簿</div>
                    <div class="normalDiv">
                        <form action="albumEditAct.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
                            <p>相片名稱：<input type="text" name="album_title" id="album_title" 
                                value="<?php echo $row['title']; ?>" /></p>
                            <p>拍攝時間：<input type="text" name="album_date" id="album_date" 
                                value="<?php echo $row['album_date']; ?>" /></p>
                            <p>拍攝地點：<input type="text" name="album_location" id="album_location" 
                                value="<?php echo $row['location']; ?>" /></p>
                            <p>照片：
                                <img src="photos/<?php echo $row['picurl']; ?>"  alt="暫無圖片"
                                    height="120" border="0" />
                            </p>
                            <input name="album_id" type="hidden" value="<?php echo $row['album_id']; ?>">
                            <input type="submit" name="Edit" id="Edit" value="確定修改">
                            <a href="album.php">回相簿管理</a>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
    </main>

    <!-- 頁尾資訊 -->
    <footer id="footer">
        <?php include "infoFooter.html"; ?>
    </footer>
</div>
</body>
</html>
