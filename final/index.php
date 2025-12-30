<?php
session_start(); // 啟用交談期

// 權限檢查
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>Final Project - Dashboard</title>
    <!-- 引用本地獨立樣式表 -->
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include "nav.php"; ?>

    <div id="content-container" style="text-align: center;">
        <span style="background: var(--success-color); color: white; padding: 6px 15px; border-radius: 50px; font-size: 0.85rem; font-weight: 700;">
            SYSTEM ACTIVE
        </span>
        
        <h1 class="welcome-title">
            歡迎 [<span class="user-name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>] 進入網站！
        </h1>
        
        <p style="color: var(--text-muted); font-size: 1.1rem;">
            這是您的期末報告專屬後台，所有資源已完全獨立運行。
        </p>
        
        <div class="dashboard-grid">
            <div class="dash-card">
                <h3 style="color: var(--primary-accent);">📊 專案分析</h3>
                <p style="color: var(--text-muted);">系統已脫離作業目錄 (hw)，獨立載入樣式與腳本。</p>
            </div>
            <div class="dash-card">
                <h3 style="color: var(--success-color);">⚙️ 安全控管</h3>
                <p style="color: var(--text-muted);">Session 驗證已整合至本地 login 流程。</p>
            </div>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
