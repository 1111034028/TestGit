<?php session_start(); ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>關於我們 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
</head>
<body>
    <!-- Nav removed -->

    <div id="content-container">
        <div class="hero-section">
            <h1 class="hero-title">重新定義你的音樂體驗</h1>
            <p class="hero-subtitle">
                Music Stream 不僅僅是一個播放器，更是連結靈魂的橋樑。我們致力於讓每一次聆聽都成為一場獨特的旅程。
            </p>
        </div>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🎧</div>
                <h3>極致音質</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    支援高解析音訊播放，還原創作者最真實的聲音細節，帶給您身臨其境的聽覺饗宴。
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌍</div>
                <h3>全球連結</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    打破地域限制，讓在地創作者的聲音被世界聽見，同時探索來自全球的多元音樂文化。
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>極速體驗</h3>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    現代化的深色介面設計，搭配流暢的響應式操作，讓您專注於音樂本身，不受干擾。
                </p>
            </div>
        </div>

        <div style="margin-top: 60px; text-align: center; padding: 40px; background: rgba(255,255,255,0.03); border-radius: 20px;">
            <h2 style="font-size: 2rem; margin-bottom: 20px;">加入我們的行列</h2>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 30px auto;">
                無論您是才華洋溢的音樂人，還是熱愛音樂的聽眾，Music Stream 都是您展現自我與探索新聲的最佳舞台。
            </p>
            <a href="register.php" class="btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">立即免費註冊</a>
        </div>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
