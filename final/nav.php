<header id="final-header">
    <div class="logo">Final Project</div>
    <ul class="final-menu">
        <li><a href="index.php">首頁</a></li>
        <li><a href="../hw/index.php">回到作業首頁</a></li>
        <?php if(isset($_SESSION["login_session"]) && $_SESSION["login_session"] === true): ?>
            <li><a href="logout.php" style="color: #d63031;">登出 (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a></li>
        <?php else: ?>
            <li><a href="login.php">登入</a></li>
        <?php endif; ?>
    </ul>
</header>
