<?php
session_start();
require_once("../DB/DB_open.php");

// Generate a random token
if (empty($_SESSION['mobile_token'])) {
    $_SESSION['mobile_token'] = bin2hex(random_bytes(16));
    
    // Insert into DB
    $token = $_SESSION['mobile_token'];
    $sql = "INSERT INTO mobile_tokens (token, status) VALUES ('$token', 'pending') 
            ON DUPLICATE KEY UPDATE status='pending'";
    mysqli_query($link, $sql);
} else {
    $token = $_SESSION['mobile_token'];
    // Ensure it exists in DB (in case of session persistence but db wipe)
    $check = mysqli_query($link, "SELECT token FROM mobile_tokens WHERE token='$token'");
    if (!$check || mysqli_num_rows($check) == 0) {
        mysqli_query($link, "INSERT INTO mobile_tokens (token, status) VALUES ('$token', 'pending')");
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>手機互動配對</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background: #121212;
            color: white;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background: #1e1e1e;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        h1 { margin-bottom: 10px; }
        p { color: #aaa; margin-bottom: 30px; }
        #qrcode {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 0 auto;
            width: fit-content;
        }
        .status {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #1DB954;
            min-height: 20px;
            transition: all 0.3s;
        }
        .refresh-btn {
            margin-top: 20px;
            background: transparent;
            border: 1px solid #555;
            color: #aaa;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .refresh-btn:hover {
            border-color: white;
            color: white;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>手機互動配對</h1>
        <p>請使用手機掃描下方 QR Code 進行互動連線</p>
        
        <div id="qrcode"></div>
        
        <div class="status" id="status-text">等待掃描...</div>
        
        <button class="refresh-btn" onclick="forceRefresh()">↻ 刷新 QR Code</button>
        <div style="margin-top: 20px;">
           <a href="index.php" style="color: #aaa; text-decoration: none; font-size: 0.9rem;">返回首頁</a>
        </div>
    </div>

    <script>
        const token = "<?php echo $token; ?>";
        // Convert local IP/localhost to the actual public hostname if using ngrok
        const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
        const mobileUrl = baseUrl + "mobile_control.php?token=" + token;

        console.log("Mobile URL:", mobileUrl);

        // Generate QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: mobileUrl,
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Polling status
        function checkStatus() {
            fetch('api_check_mobile_status.php?token=' + token)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'connected') {
                        document.getElementById('status-text').innerText = "連線成功！";
                        document.getElementById('status-text').style.color = "#1db954";
                        document.getElementById('qrcode').style.opacity = "0.2";
                        // Ideally we notify the main window or redirect
                    } else {
                        document.getElementById('status-text').innerText = "等待掃描...";
                    }
                })
                .catch(err => console.error(err));
        }
        
        function forceRefresh() {
             fetch('api_refresh_token.php')
                .then(() => location.reload());
        }

        // Poll every 2 seconds
        setInterval(checkStatus, 2000);
    </script>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
