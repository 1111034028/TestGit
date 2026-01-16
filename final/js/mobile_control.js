// State Syncing
let currentToken = '';
let syncInterval = null;

function initMobileControl(token) {
    currentToken = token;
    console.log("Mobile control initialized with token:", token);
    
    // Bind volume slider
    const volSlider = document.getElementById('vol-slider');
    if(volSlider) {
        volSlider.addEventListener('input', function() {
            sendCommand('set_volume', this.value);
        });
    }

    // Start State Polling
    startSyncing();
}

function startSyncing() {
    if (syncInterval) clearInterval(syncInterval);
    
    // 1. Sync Player State (1s is enough for UI)
    syncInterval = setInterval(() => {
        syncPlayerState();
    }, 1000);

    // 2. Check for token updates (Low priority, 5s is fine)
    setInterval(() => {
        checkTokenHealth();
    }, 5000);
}

function syncPlayerState() {
    fetch(`api_get_state.php?token=${currentToken}`)
        .then(res => res.json())
        .then(state => {
            if (state && state.title) {
                document.getElementById('track-title').innerText = state.title;
                document.getElementById('track-artist').innerText = state.artist;
                document.getElementById('album-art').src = state.cover;
                document.getElementById('bg-art').style.backgroundImage = `url('${state.cover}')`;
                
                const btn = document.getElementById('play-btn');
                btn.innerText = state.isPlaying ? '❚❚' : '▶';
                
                if (state.duration > 0) {
                    const fill = document.getElementById('progress-bar-fill');
                    const percent = (state.currentTime / state.duration) * 100;
                    fill.style.width = percent + '%';
                }
            }
        })
        .catch(err => console.warn("Player state sync error"));
}

function checkTokenHealth() {
    fetch(`api_sync_mobile_token.php?token=${currentToken}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'new_token') {
                console.log("Detected new token on computer, refreshing...");
                window.location.href = `mobile_control.php?token=${data.token}`;
            }
        })
        .catch(err => {});
}

function sendCommand(cmd, payload = '') {
    const formData = new FormData();
    formData.append('token', currentToken);
    formData.append('command', cmd);
    formData.append('payload', payload);

    fetch('api_send_command.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status !== 'success') {
            console.error("Command failed", data);
            showToast("與電腦連結已中斷");
        }
    })
    .catch(err => {
        console.error("Network error", err);
    });
}

function togglePlay() {
    sendCommand('toggle_play');
    const btn = document.getElementById('play-btn');
    if(btn.innerText === '▶') btn.innerText = '❚❚';
    else btn.innerText = '▶';
}

function showToast(msg) {
    const toast = document.getElementById('status-toast');
    if(toast) {
        toast.innerText = msg;
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 2000);
    }
}
