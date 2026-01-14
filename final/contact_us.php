<?php session_start(); ?>
<?php
$page_title = "聯絡客服 - 音樂串流平台";
$extra_css = '<style>
        .contact-layout { padding: 20px; }
        @media (max-width: 768px) {
            .contact-layout { grid-template-columns: 1fr; gap: 30px; }
        }
    </style>';
require_once("inc/auth_guard.php"); // Restrict access
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

    <div id="content-container" style="max-width: 1000px; padding-top: 20px;">
        <div class="contact-layout">
            <div class="contact-info-box">
                <h2 style="font-size: 2.5rem; margin-bottom: 20px; background: linear-gradient(45deg, #1db954, #1ed760); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">聯絡我們</h2>
                <div style="color: #ccc; margin-bottom: 30px; line-height: 1.6;">
                    我們重視您的每一個反饋。<br>
                    無論是功能建議、系統問題回報，或是商業合作洽談，<br>
                    都歡迎與我們聯繫，我們將盡快回覆您。
                </div>

                <?php if(isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
                    <div style="margin-bottom: 40px;">
                        <a href="my_messages.php" class="btn-secondary" style="display: inline-flex; align-items: center; padding: 12px 25px; border-radius: 50px; border: 1px solid #444; background: #222; transition: all 0.3s;">
                            <span style="margin-right: 8px;">📂</span> 查看我的客服紀錄
                        </a>
                    </div>
                <?php endif; ?>
                
                <div style="margin-bottom: 30px; padding-left: 15px; border-left: 3px solid #1db954;">
                    <strong style="display: block; font-size: 0.85rem; color: #888; text-transform: uppercase; margin-bottom: 5px;">客服信箱 (Email)</strong>
                    <span style="font-size: 1.1rem; font-family: monospace;">hello@musicstream.com</span>
                </div>
                
                <div style="margin-bottom: 30px; padding-left: 15px; border-left: 3px solid #1db954;">
                    <strong style="display: block; font-size: 0.85rem; color: #888; text-transform: uppercase; margin-bottom: 5px;">客服專線 (Phone)</strong>
                    <span style="font-size: 1.1rem; font-family: monospace;">+886 2 1234 5678</span>
                </div>
                
                <div style="margin-top: 50px;">
                    <p style="font-size: 0.9rem; color: #aaa; margin-bottom: 15px;">關注我們</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="#" style="width: 40px; height: 40px; background: #282828; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#282828'">📷</a>
                        <a href="#" style="width: 40px; height: 40px; background: #282828; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#282828'">📘</a>
                        <a href="#" style="width: 40px; height: 40px; background: #282828; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#282828'">🐦</a>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div style="background: #181818; padding: 30px; border-radius: 12px; border: 1px solid #282828;">
                <h3 style="margin-top: 0; margin-bottom: 25px; font-size: 1.5rem; border-bottom: 1px solid #333; padding-bottom: 15px;">填寫聯絡表單</h3>
                <form action="contact_act.php" method="post" class="music-form" id="contactForm">
                    <div class="form-group">
                        <label style="font-size: 0.9rem; color: #ccc;">您的姓名 (Name)</label>
                        <input type="text" name="name" required placeholder="請輸入聯絡名稱" style="background: #222; border: 1px solid #333; padding: 12px; border-radius: 6px;">
                    </div>
                    
                    <div class="form-group">
                        <label style="font-size: 0.9rem; color: #ccc;">電子郵件 (Email)</label>
                        <input type="email" name="email" required placeholder="name@example.com" style="background: #222; border: 1px solid #333; padding: 12px; border-radius: 6px;">
                    </div>
                    
                    <div class="form-group">
                        <label style="font-size: 0.9rem; color: #ccc;">問題分類</label>
                        <select name="category" style="background: #222; border: 1px solid #333; padding: 12px; border-radius: 6px;">
                            <option value="一般諮詢">💡 一般諮詢</option>
                            <option value="帳號問題">🔒 帳號問題</option>
                            <option value="功能故障回報">🐛 功能故障回報</option>
                            <option value="版權申訴">⚖️ 版權申訴</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label style="font-size: 0.9rem; color: #ccc;">詳細內容</label>
                        <textarea name="message" rows="5" required placeholder="請詳細描述您的需求，我們將儘快為您服務..." style="width: 100%; padding: 12px; background: #222; color: white; border: 1px solid #333; border-radius: 6px; resize: vertical; box-sizing: border-box; font-family: inherit; font-size: 1rem; line-height: 1.5;"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-weight: bold; border-radius: 50px; margin-top: 10px;">送出訊息</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/contact.js"></script>

    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
