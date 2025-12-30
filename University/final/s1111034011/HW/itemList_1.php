<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>SDGs</title>
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
        <header id="header" class="clearheader">
            <?php
            require "nav.html";
            ?>
        </header>
        <main class="clearheader">
            <h2>永續發展目標SDGs(從資料庫讀取)</h2>
            <div class="list">
                <?php
                require_once("DB_open.php");
                $sql = "SELECT * FROM sdgs";
                $result = mysqli_query($link, $sql);
                while ($rows = mysqli_fetch_array($result, MYSQLI_NUM)) {
                    $m = $rows[1];
                    $t = $rows[2];
                    $d = $rows[3];
                    printItem($m, $t, $d);
                }
                mysqli_free_result($result);
                require_once("DB_close.php");
                ?>
            </div>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>