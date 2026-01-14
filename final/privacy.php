<?php session_start(); ?>
<?php
$page_title = "隱私權政策 - 音樂串流平台";
require_once("inc/header.php");
?>
<body style="background-color: #121212; color: white;">
    <!-- Header with Hamburger and Title (Only show if standalone/top-level) -->
    <div id="page-header" style="display: none; align-items: center; padding: 15px 25px; background: #121212; color: white; border-bottom: 1px solid #282828;">
        <a href="index.php" style="color:white; text-decoration:none; display:flex; align-items:center;">
             <div style="font-size: 1.5rem; margin-right: 20px; cursor: pointer;">☰</div>
             <div style="font-weight: bold; font-size: 1.2rem; letter-spacing: 1px;">Music Stream</div>
        </a>
    </div>
    <script>
        if (window.self === window.top) {
            document.getElementById('page-header').style.display = 'flex';
        }
    </script>

    <div id="content-container" style="padding-top: 20px;">
        <div style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px;">隱私權政策</h1>
            <p style="color: var(--text-secondary);">我們重視您的隱私，並致力於透明化資料處理方式。</p>
        </div>

        <div class="info-container">
            <!-- Sidebar Navigation (Visual Only for now) -->
            <div style="display: none; @media (min-width: 768px) { display: block; }">
                <div style="position: sticky; top: 100px;">
                    <ul style="list-style: none; padding: 0; border-left: 2px solid #333;">
                        <li style="margin-bottom: 15px; padding-left: 20px; border-left: 2px solid var(--accent-color); color: white; font-weight: bold;">收集的資訊</li>
                        <li style="margin-bottom: 15px; padding-left: 20px; color: var(--text-secondary);">使用方式</li>
                        <li style="margin-bottom: 15px; padding-left: 20px; color: var(--text-secondary);">資訊安全</li>
                        <li style="margin-bottom: 15px; padding-left: 20px; color: var(--text-secondary);">Cookie 政策</li>
                    </ul>
                </div>
            </div>

            <!-- Content Area -->
            <div>
                <div class="text-card">
                    <h3 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                        <span style="background: var(--accent-color); width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                        1. 我們收集的資訊
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        當您註冊本服務時，我們會收集您的基本個人資料（如姓名、Email、學號）。若您是創作者，我們亦會儲存您上傳的音樂檔案、封面圖片及相關元數據。此外，系統會自動記錄您的播放歷程以提供個人化推薦。
                    </p>
                </div>

                <div class="text-card">
                    <h3 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                        <span style="background: var(--accent-color); width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                        2. 資訊使用方式
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        您的資料主要用於：
                        <br>• 驗證您的身份並管理帳戶安全。
                        <br>• 提供音樂串流、上傳及搜尋功能。
                        <br>• 分析平台使用數據以優化系統效能。
                        <br>• 通知您關於服務的重要變更。
                    </p>
                </div>

                <div class="text-card">
                    <h3 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                        <span style="background: var(--accent-color); width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                        3. 資料保護承諾
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        我們採用業界標準的加密技術來傳輸您的敏感資料。您的密碼在資料庫中以加密形式儲存。除非法律要求或徵得您的同意，我們絕不會將您的個資販售或提供給第三方。
                    </p>
                </div>
                
                <p style="font-size: 0.8rem; color: #555; text-align: center; margin-top: 40px;">
                    最後更新日期：2025年1月12日
                </p>
            </div>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
