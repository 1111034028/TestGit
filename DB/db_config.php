<?php
// DB/db_config.php - 標準化資料庫連線設定
$link = @mysqli_connect("localhost", "root", "") 
        or die("資料庫連線失敗，請檢查 XAMPP 是否開啟！<br/>");

mysqli_select_db($link, "finaldb");
mysqli_set_charset($link, "utf8mb4");

/**
 * 關閉連線的輔助函式
 */
function db_close_connection($link) {
    if ($link) {
        mysqli_close($link);
    }
}
?>
