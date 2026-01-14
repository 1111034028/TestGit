<?php
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Invalid Session Token.");
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>音樂遙控</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --primary: #fff;
            --secondary: rgba(255, 255, 255, 0.6);
        }

        body {
            margin: 0;
            padding: 0;
            background: #121212;
            color: white;
            font-family: 'Inter', system-ui, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: background 0.5s ease;
        }

        /* Ambient Background (Blurred Art) */
        #bg-art {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('img/music.png'); /* Default */
            background-size: cover;
            background-position: center;
            filter: blur(60px) brightness(0.4);
            z-index: -1;
            transition: background-image 0.5s ease;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 30px;
            padding-top: 60px; /* Safe area */
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
            justify-content: space-between;
        }

        /* Album Art */
        .album-art-container {
            width: 100%;
            aspect-ratio: 1/1;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border-radius: 12px;
            overflow: hidden;
            background: #222;
        }

        #album-art {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Info */
        .track-info {
            text-align: left;
            margin-bottom: 20px;
        }

        #track-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #track-artist {
            font-size: 1.1rem;
            color: var(--secondary);
            margin-top: 5px;
        }

        /* Progress Bar (Visual Only for now) */
        .progress-container {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .progress-bar-bg {
            width: 100%;
            height: 4px;
            background: rgba(255,255,255,0.2);
            border-radius: 2px;
            position: relative;
        }
        
        #progress-bar-fill {
            width: 0%;
            height: 100%;
            background: #fff;
            border-radius: 2px;
            position: absolute;
        }

        /* Controls */
        .controls-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .btn {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            opacity: 0.9;
            transition: transform 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
            opacity: 0.7;
        }

        .btn-large {
            font-size: 3.5rem;
            width: 80px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }

        /* Secondary Controls (Volume, Shuffle, etc) */
        .controls-secondary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px;
        }

        .btn-sec {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        
        .btn-sec.active {
            color: #1DB954; /* Green accent */
        }
        
        /* Volume Slider */
        .volume-container {
            display: flex;
            align-items: center;
            flex: 1;
            margin: 0 20px;
        }
        
        input[type=range] {
            width: 100%;
            -webkit-appearance: none;
            background: transparent;
        }
        
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 12px;
            width: 12px;
            border-radius: 50%;
            background: #fff;
            margin-top: -4px;
        }
        
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 4px;
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
        }

        #status-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: none;
            z-index: 100;
        }
    </style>
</head>
<body>

    <div id="bg-art"></div>
    <div id="status-toast">已連線</div>

    <div class="container">
        <!-- Album Art -->
        <div class="album-art-container">
            <img id="album-art" src="img/music.png" alt="專輯封面">
        </div>

        <!-- Info -->
        <div class="track-info">
            <div id="track-title">暫無播放</div>
            <div id="track-artist">請在電腦端選擇歌曲</div>
        </div>
        
        <!-- Progress -->
        <div class="progress-container">
            <div class="progress-bar-bg">
                <div id="progress-bar-fill"></div>
            </div>
        </div>

        <!-- Main Controls -->
        <div class="controls-main">
            <button class="btn" onclick="sendCommand('prev')">⏮</button>
            <button class="btn btn-large" id="play-btn" onclick="togglePlay()">▶</button>
            <button class="btn" onclick="sendCommand('next')">⏭</button>
        </div>

        <!-- Secondary Controls -->
        <div class="controls-secondary">
             <button class="btn btn-sec" onclick="sendCommand('toggle_loop')">🔁</button>
             <div class="volume-container">
                 <span style="font-size: 0.8rem; margin-right: 8px;">🔈</span>
                 <input type="range" id="vol-slider" min="0" max="1" step="0.05" value="1">
                 <span style="font-size: 0.8rem; margin-left: 8px;">🔊</span>
             </div>
             <button class="btn btn-sec" onclick="sendCommand('toggle_shuffle')">🔀</button>
        </div>
    </div>

    <!-- Silent Audio for Media Session -->
    <audio id="silent-audio" loop>
        <source src="data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4LjI5LjEwMAAAAAAAAAAAAAAA//oeAAAAAAAAAAAAAAAAAAAAAAAASW5mbwAAAA8AAAAEAAABIADAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD//////////////////////////////////////////////////////////////////wAAAAAATGF2YzU4LjU0AAAAAAAAAAAAAAAAJAAAAAAAAAAAASAAAAAAAABIAAAAAAAA//oeRn+AAAAAABIAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//oeRn+AAAAAABIAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//oeRn+AAAAAABIAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//oeRn+AAAAAABIAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//oeRn+AAAAAABIAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//" type="audio/mpeg">
    </audio>

    <script src="js/mobile_control.js"></script>
    <script>
        initMobileControl('<?php echo htmlspecialchars($token); ?>');
    </script>
</body>
</html>
