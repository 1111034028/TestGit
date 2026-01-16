// Remote Control Client Logic (Desktop)

let pollInterval = null;
const POLL_RATE = 1500; // 1.5s
let syncInterval = null;

window.currentRemoteToken = null;

function initRemoteControl() {
    const modal = document.getElementById('remote-modal');
    if (!modal) return;
    
    modal.style.display = 'flex';
    
    // Only generate QR if container is empty (first time or after refresh)
    const qrContainer = document.getElementById('qr-code-container');
    if (qrContainer && qrContainer.innerHTML === '') {
        loadRemoteToken(false);
    }
}

function refreshRemoteToken() {
    loadRemoteToken(true);
}

function loadRemoteToken(forceNew = false) {
    const qrContainer = document.getElementById('qr-code-container');
    const hint = document.getElementById('mobile-url-hint');
    if (!qrContainer || !hint) return;
    
    qrContainer.innerHTML = '';
    hint.innerText = forceNew ? "正在重新產生連線碼..." : "正在取得連線碼...";
    
    const url = forceNew ? 'api_get_mobile_token.php?refresh=1' : 'api_get_mobile_token.php';
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.currentRemoteToken = data.token;
                
                const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
                const mobileUrl = baseUrl + "mobile_control.php?token=" + data.token;
                
                new QRCode(qrContainer, {
                    text: mobileUrl,
                    width: 200,
                    height: 200
                });
                
                let hintMsg = "";
                if (baseUrl.includes('localhost')) {
                    hintMsg = `<span style="color: #ff4757; font-weight: bold;">注意：檢測到 localhost</span><br><small style="color: #888;">手機無法直接連線至 localhost，請改用您的區網 IP 或 ngrok 網址開啟電腦版。</small>`;
                } else {
                    hintMsg = forceNew ? "已更新連線碼！" : "掃描上方 QR Code 開始互動";
                }

                hintMsg += `<div style="margin-top: 10px; font-size: 0.8rem; word-break: break-all; background: #eee; padding: 5px; user-select: text;">
                    <a href="${mobileUrl}" target="_blank" style="color:#0984e3; text-decoration: underline;">${mobileUrl}</a>
                </div>`;
                
                hint.innerHTML = hintMsg;
                
                if (window.showToast && forceNew) window.showToast("連線碼已刷新", "info");
                
                startWaitingForConnection();
            } else {
                hint.innerText = "連線碼取得失敗";
            }
        })
        .catch(err => {
            console.error("Token Load Error:", err);
            hint.innerText = "系統錯誤";
        });
}

let statusInterval = null;

// Flag to prevent double starting
let isSyncing = false;

function startWaitingForConnection() {
    if (statusInterval) clearInterval(statusInterval);
    
    console.log("Waiting for mobile connection...");
    // Only check connection status initially (Lightweight)
    statusInterval = setInterval(checkConnectStatus, 2000);
}

function startFullSync() {
    if (isSyncing) return;
    isSyncing = true;
    
    console.log("Mobile connected! Starting full sync...");
    
    // 1. Start Command Polling
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(() => {
        fetch('api_get_commands.php')
            .then(res => res.json())
            .then(commands => {
                if (Array.isArray(commands) && commands.length > 0) {
                    commands.forEach(cmd => executeCommand(cmd));
                }
            })
            .catch(() => {});
    }, POLL_RATE);

    // 2. Start State Pushing
    if (syncInterval) clearInterval(syncInterval);
    syncInterval = setInterval(pushState, 2000);
    
    // Add Event Listeners for instant updates (only once)
    const audio = document.getElementById('audio-player');
    if(audio && !audio.hasAttribute('data-sync-attached')) {
        audio.addEventListener('play', pushState);
        audio.addEventListener('pause', pushState);
        audio.setAttribute('data-sync-attached', 'true');
    }
}

function checkConnectStatus() {
    if (!window.currentRemoteToken) return;
    
    fetch('api_check_mobile_status.php?token=' + window.currentRemoteToken)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'connected') {
                // Mobile is connected
                const modal = document.getElementById('remote-modal');
                if (modal && modal.style.display !== 'none') {
                    modal.style.display = 'none';
                }
                
                // Show toast regardless of modal state to inform user
                if (window.showToast) window.showToast("手機已成功連線！", "success");
                
                // Stop checking status, start syncing data
                clearInterval(statusInterval);
                statusInterval = null;
                startFullSync();
            }
        })
        .catch(err => console.error("Status Check Error:", err));
}



function executeCommand(cmdObj) {
  const { command, payload } = cmdObj;
  console.log("Remote Command:", command, payload);

  const audio = document.getElementById('audio-player');
  const mainPlayBtn = document.getElementById('main-play-btn');

  switch (command) {
    case "toggle_play":
      if (typeof togglePlay === "function") togglePlay();
      break;

    case "play":
       if (audio) audio.play();
       break;
       
    case "pause":
       if (audio) audio.pause();
       break;

    case "next":
      if (typeof nextSong === "function") nextSong();
      break;

    case "prev":
      if (typeof prevSong === "function") prevSong();
      break;

    case "set_volume": // Mobile slider 0-1
      const vol = parseFloat(payload);
      if (!isNaN(vol) && audio) {
        audio.volume = vol;
        const volSlider = document.getElementById("volume-slider");
        if(volSlider) volSlider.value = vol;
        const volVal = document.getElementById("volume-value");
        if(volVal) volVal.innerText = Math.round(vol * 100) + "%";
      }
      break;
      
    case "seek": // 0-100 percentage
      const percent = parseFloat(payload);
      if(!isNaN(percent) && audio && audio.duration) {
          audio.currentTime = (percent / 100) * audio.duration;
      }
      break;

    case "toggle_loop":
      if (typeof toggleLoop === "function") toggleLoop();
      break;
      
    case "toggle_shuffle":
      if (typeof toggleShuffle === "function") toggleShuffle();
      break;

    default:
      console.warn("Unknown command:", command);
  }
  
  // Force a state push immediately after command
  setTimeout(pushState, 200);
}

function pushState() {
    const audio = document.getElementById('audio-player');
    const title = document.getElementById('player-title')?.innerText || '';
    const artist = document.getElementById('player-artist')?.innerText || '';
    const cover = document.getElementById('player-cover')?.src || '';
    
    if (!audio) return;
    
    const payload = {
        title: title,
        artist: artist,
        cover: cover,
        isPlaying: !audio.paused,
        currentTime: audio.currentTime,
        duration: audio.duration || 0
    };
    
    fetch('api_push_state.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).catch(()=>{}); // Ignore errors
}

// Auto initialized on load to register session for auto-discovery
loadRemoteToken(false);
startWaitingForConnection();
