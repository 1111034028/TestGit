<?php
// DB/DB_close.php - 標準化關閉連線
if (isset($link)) {
    mysqli_close($link);
}
?>
