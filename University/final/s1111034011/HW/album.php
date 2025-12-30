<?php
require_once("DB_open.php");
$pageRow_records = 8;
$num_pages = 1;
if (isset($_GET['page'])) {
    $num_pages = $_GET['page'];
}
$startRow_records = ($num_pages - 1) * $pageRow_records;
$query_Album = "SELECT album_id, album_date, location, title, picurl FROM album ORDER BY album_date DESC";
$query_Album = $query_Album . " LIMIT {$startRow_records}, {$pageRow_records}";
$query_count = "SELECT COUNT(*) From album";
$result = mysqli_query($link, $query_Album);
$result_count = mysqli_query($link, $query_count);
$total_records = mysqli_fetch_row($result_count)[0];
$total_pages = ceil($total_records / $pageRow_records);
require_once("DB_close.php");
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>album.php</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/albumCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.html";
            ?>
        </header>
        <main id="contents">
            <script language="JavaScript" type="text/javascript">
                function checkDelete() {
                    return confirm('Are you sure?');
                }
            </script>
            <table width="100%" border="0" align="center" cellpadding="4" cellspacing="0">
                <tr>
                    <td>
                        <div class="subjectDiv">相簿管理</div>
                        <div class="actionDiv">照片總數:
                            <?php echo $total_records; ?>，<a href="albumAdd.php">新增照片</a>
                        </div>
                        <p></p>
                        <?php
                        if ($total_records <= 0)
                            echo "<h4>暫無圖片</h4>";
                        else {
                            while ($row_RecAlbum = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                echo "<div class='albumDiv'>";
                                echo "<div class='picDiv'>";
                                echo '<a href="photos/' . $row_RecAlbum["picurl"] . '">';
                                echo '<img src="photos/' . $row_RecAlbum["picurl"]
                                    . '" alt="暫無圖片" width="120" height="120" border="0"/>';
                                echo "</a></div>";

                                echo '<div class="albuminfo">';
                                echo '<p>' . $row_RecAlbum["title"] . '<br/>'
                                    . $row_RecAlbum["album_date"] . '<br />'
                                    . $row_RecAlbum["location"] . '<p>';
                                echo "</div>";
                                echo '<a href="albumEdit.php?id=' . $row_RecAlbum["album_id"] . '" class="btn">
                                        編輯</a>&nbsp;&nbsp;';
                                echo '<a href="albumDelete.php?id=' . $row_RecAlbum["album_id"] . '"class="btn"
                                        onclick="return checkDelete()">刪除</a>';
                                echo "</div>";
                            }
                        }
                        ?>
                        <!-- 顯示頁碼 -->
                        <div class="navDiv">
                            <?php if ($num_pages > 1) { // 若不是第一頁則顯示 ?>
                                <a href="?page=1">|&lt;</a> <a href="?page=<?php echo $num_pages - 1; ?>">&lt;&lt;</a>
                            <?php } else { ?>
                                |&lt; &lt; &lt;
                            <?php } ?>

                            <?php
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $num_pages) {
                                    echo $i . " ";
                                } else {
                                    echo "<a href=\"?page=$i\">$i</a> ";
                                }
                            }
                            ?>

                            <?php if ($num_pages < $total_pages) { // 若不是最後一頁則顯示 ?>
                                <a href="?page=<?php echo $num_pages + 1; ?>">&gt;&gt;</a> 
                                <a href="?page=<?php echo $total_pages; ?>">&gt;|</a>
                            <?php } else { ?>
                                &gt; &gt;&gt;|
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            </table>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
        </di>
</body>

</html>