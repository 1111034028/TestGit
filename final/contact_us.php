<?php session_start(); ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>聯絡客服 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <style>
        .contact-layout { padding: 20px; }
        @media (max-width: 768px) {
            .contact-layout { grid-template-columns: 1fr; gap: 30px; }
        }
    </style>
</head>
<body>
    <!-- Nav removed -->

    <div id="content-container" style="max-width: 1000px;">
        <div class="contact-layout">
            <!-- Left Side: Info -->
            <div class="contact-info-box">
                <h2 style="font-size: 2.2rem; margin-bottom: 20px;">與我們聯繫</h2>
                <p style="line-height: 1.8; margin-bottom: 40px; color: rgba(255,255,255,0.8);">
                    無論是使用上的問題、合作提案，或是單純想給予建議，我們都樂意傾聽您的聲音。
                </p>
                
                <div style="margin-bottom: 25px;">
                    <strong style="display: block; font-size: 0.9rem; opacity: 0.7;">EMAIL US</strong>
                    <span style="font-size: 1.2rem;">hello@musicstream.com</span>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <strong style="display: block; font-size: 0.9rem; opacity: 0.7;">CALL US</strong>
                    <span style="font-size: 1.2rem;">+886 2 1234 5678</span>
                </div>
                
                <div style="margin-top: 40px;">
                    <p style="font-size: 0.9rem;">追蹤我們</p>
                    <div style="font-size: 1.5rem; display: flex; gap: 20px;">
                        <span>📷</span>
                        <span>📘</span>
                        <span>🐦</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div>
                <h3 style="margin-bottom: 20px; font-size: 1.5rem;">發送訊息</h3>
                <form action="#" method="post" class="music-form" onsubmit="alert('感謝您的訊息！我們已收到並將儘快處理。'); return false;">
                    <div class="form-group">
                        <label>您的姓名 (Name)</label>
                        <input type="text" required placeholder="怎麼稱呼您？" style="background: #222; border: 1px solid #333;">
                    </div>
                    
                    <div class="form-group">
                        <label>電子郵件 (Email)</label>
                        <input type="email" required placeholder="example@mail.com" style="background: #222; border: 1px solid #333;">
                    </div>
                    
                    <div class="form-group">
                        <label>訊息類別</label>
                        <select style="background: #222; border: 1px solid #333;">
                            <option>一般諮詢</option>
                            <option>帳號問題</option>
                            <option>功能故障回報</option>
                            <option>版權申訴</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>詳細內容</label>
                        <textarea rows="6" required placeholder="請詳細描述您的問題或是建議..." style="width: 100%; padding: 15px; background: #222; color: white; border: 1px solid #333; border-radius: 4px; resize: vertical; box-sizing: border-box; font-family: inherit; font-size: 1rem;"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">確認發送</button>
                </form>
            </div>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
