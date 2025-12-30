<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>永續發展目標 SDGs (資料庫版)</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <?php
    function printItem($image, $title, $detail)
    {
        echo "<div class='item'>";
        echo "<img src='img/" . $image . "' alt=''>";
        echo "<h4>" . $title . "</h4>";
        echo "<p>" . $detail . "</p>";
        echo "</div>";
    }
    ?>
</head>

<body>
    <div id="wrap">
        <!-- 功能列表 -->
        <header id="header" class="clearheader">
            <?php
            include "nav.html";
            ?>
        </header>

        <!-- 內容區域 -->
        <main class="clearheader">
            <h2>永續發展目標 SDGs (從資料庫讀取)</h2>
            <div class="list">
                <?php
                require_once("../DB/DB_open.php");      // 引入資料庫連結設定檔
                $sql = "SELECT * FROM sdgs";      // 設定SQL查詢字串
                $result = mysqli_query($link, $sql); // 執行SQL查詢

                while ($rows = mysqli_fetch_array($result, MYSQLI_NUM)) {
                    $m = $rows[1]; // img
                    $t = $rows[2]; // title
                    $d = $rows[3]; // detail
                    printItem($m, $t, $d);
                }
                
                mysqli_free_result($result);      // 釋放佈局的記憶體
                require_once("../DB/DB_close.php");     // 引入資料庫關閉設定檔
                ?>
            </div>
        </main>

        <!-- 頁尾資訊 -->
        <footer id="footer">
            <?php
            include "foot.html";
            ?>
        </footer>
    </div>
</body>

</html>
