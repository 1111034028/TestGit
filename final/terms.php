<?php session_start(); ?>
<?php
$page_title = "服務條款 - 音樂串流平台";
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
        // Only show header if NOT in an iframe (standalone mode)
        if (window.self === window.top) {
            document.getElementById('page-header').style.display = 'flex';
        }
    </script>

    <div id="content-container" style="padding-top: 20px;">
        <div class="hero-section" style="padding: 40px 20px; text-align: left; background: none;">
            <h1 class="hero-title" style="font-size: 3rem;">服務條款</h1>
            <p class="hero-subtitle" style="margin: 0; max-width: 100%;">
                使用 Music Stream 即代表您同意以下規範。請仔細閱讀以保障您的權益。
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div class="text-card" style="border-left-color: #0984e3;">
                <h3 style="color: #74b9ff;">01. 帳號規範</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    您需對您帳號下的所有活動負責。請妥善保管您的密碼。若發現遭盜用，請立即通知我們。禁止使用虛假身分註冊。
                </p>
            </div>
            
            <div class="text-card" style="border-left-color: #d63031;">
                <h3 style="color: #ff7675;">02. 禁止行為</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    禁止上傳病毒、惡意程式或任何侵犯他人智慧財產權的內容。禁止對本平台進行逆向工程或攻擊。
                </p>
            </div>
            
            <div class="text-card" style="border-left-color: #fdcb6e;">
                <h3 style="color: #ffeaa7;">03. 內容授權</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    您保留您原創內容的所有權。但上傳後，即代表授權本平台進行存儲、轉碼及公開播放等必要之操作。
                </p>
            </div>
            
            <div class="text-card" style="border-left-color: #a29bfe;">
                <h3 style="color: #a29bfe;">04. 服務變更</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    我們保留隨時修改、暫停或終止部分或全部服務的權利，且不對因此造成的任何損失負責。
                </p>
            </div>
        </div>

        <div style="margin-top: 40px; padding: 20px; border-top: 1px solid #333;">
            <p style="font-size: 0.9rem; color: #777;">
                若您對條款有任何疑問，請透過 <a href="contact_us.php" style="color: var(--accent-color);">聯絡客服</a> 與我們聯繫。
            </p>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
