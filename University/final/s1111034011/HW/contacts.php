<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>通訊錄</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <center>
        <div id="wrap">
            <!-- 功能列表 -->
            <header id="header" class="clearheader">
                <?php
                include "nav.html";
                ?>
            </header>
            <!-- 內容區域 -->
            <main id="contents">
                <h2>通訊錄管理</h2>
                <?php
                session_start();
                $records_per_page = 3;
                if (isset($_GET["page"]))
                    $page = $_GET["page"];
                else
                    $page = 1;
                require_once("DB_open.php");
                //設定
                if (isset($_SESSION["SQL"]))
                    $sql = $_SESSION["SQL"];
                    
                else
                    $sql = "SELECT * FROM students ORDER BY name";
                    $sql = isset($_SESSION["SQL"]) ? $_SESSION["SQL"] : "SELECT * FROM students ORDER BY name";
                //執行
                $result = mysqli_query($link, $sql);
                $total_fields = mysqli_num_fields($result);
                $total_records = mysqli_num_rows($result);
                //總頁數
                $total_pages = ceil($total_records / $records_per_page);
                
                $offset = ($page - 1) * $records_per_page;
                mysqli_data_seek($result, $offset);
                echo "記錄總數:$total_records 筆<br/>";
                echo "<table border=1 style='margin: 0 auto';><tr><td>編號</td>";
                echo "<td>姓名</td><td>住址</td><td>生日</td><td>帳號名</td><td>密碼</td><td >功能</td></tr>";
                $j = 1;
                while ($rows = mysqli_fetch_array($result, MYSQLI_NUM) and $j <= $records_per_page) {
                    echo "<tr>";
                    for ($i = 0; $i <= $total_fields - 1; $i++) {
                        echo "<td>" . $rows[$i] . "</td>";
                    }
                    echo "<td ><a href='edit.php?action=edit&id=";
                    echo $rows[0] . "'><b> 編輯 </b>|";
                    echo "<a href='edit.php?action=del&id=";
                    echo $rows[0] . "'><b> 刪除 </b></a></td>";
                    echo "</tr>";
                    echo "</tr>";
                    $j++;

                }
                echo "<table><br>";
                if ($page > 1)
                    echo "<a href='contacts.php?page=" . ($page - 1) . "'>上一頁</a>| ";
                for ($i = 1; $i <= $total_pages; $i++)
                    if ($i != $page)
                        echo "<a href=\"contacts.php?page=" . $i . "\">" . $i . " " . "</a>";
                    else
                        echo "   " . $i . " ";
                if ($page < $total_pages)
                    echo "|<a href='contacts.php?page=" . ($page + 1) . "'>下一頁</a>";
                mysqli_free_result(($result));
                require_once("DB_close.php")
                    ?>
                <hr /><a href="contacts.php"> 首頁 |</a>
                <a href="add.php"> 新增聯絡資料 |</a>
                <a href="search.php"> 搜尋通訊錄 </a>
    </center>
    </main>
    <!-- 頁尾資訊 -->
    <footer id="footer">
        <?php
        require "infoFooter.html";
        ?>
    </footer>
    </div>
</body>

</html>