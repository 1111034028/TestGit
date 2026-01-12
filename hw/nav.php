<div id="logo">
    <a href="https://www.nutc.edu.tw" target="_blank">
        <img id="logoimg" src="img/NUTC_logo.gif" alt="臺中科大">
    </a>
</div>
<nav>
    <ui class="menu">
        <li><a href="../final/login.php">期末報告</a></li>
        <li><a href="index.php">作業首頁</a></li>
        <li><a href="">課堂練習</a>
            <ul>
                <li><a href="tableForm.php">表格表單</a>
                    <ul>
                        <li><a href="tableForm.php#cat">貓咪資料</a></li>
                        <li><a href="tableForm.php#owner">飼主資料</a></li>
                    </ul>
                </li>
                <li><a href="input.php">本金和</a></li>
                <li><a href="photo.php">影像處理</a></li>
                <li><a href="itemList.php">項目清單</a>
                    <ul>
                        <li><a href="itemList.php">項目清單</a></li>
                        <li><a href="itemList_1.php">項目清單(DB)</a></li>
                    </ul>
                </li>
                <li><a href="getForm.php">Get表單</a></li>
                <li><a href="contacts.php">通訊錄</a></li>
                <li><a href="album.php">相簿管理</a></li>
            </ul>
        </li>
    </ui>
</nav>

<!-- 使用者功能區 (靠右) - 使用 PHP 直接渲染，不使用 ul/li，自行定義樣式 -->
<div style="float: right; margin: 25px 30px; font-family: 'Microsoft JhengHei', sans-serif;">
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if(isset($_SESSION["login_session"]) && $_SESSION["login_session"] === true) {
        // 已登入：顯示 "登出 (使用者名稱)" 純文字連結
        // 樣式參考：紅色文字 (#d63031)，無底色，無按鈕外觀
        echo '<a href="../final/logout.php" style="color: #d63031; text-decoration: none; font-weight: bold; font-size: 16px;">登出 (' . htmlspecialchars($_SESSION["username"]) . ')</a>';
    } else {
        // 未登入
        echo '<a href="../final/login.php" style="color: #0984e3; text-decoration: none; font-weight: bold; font-size: 16px;">登入</a>';
    }
    ?>
</div>
