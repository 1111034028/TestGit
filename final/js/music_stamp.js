// js/music_stamp.js
// 心情音域留言邏輯

let currentStampData = null; // 儲存附近通知的暫存資料

// 開啟音域邏輯
function openStampModal() {
    const modal = document.getElementById('stamp-modal');
    const loading = document.getElementById('stamp-loading');
    const form = document.getElementById('stamp-form');
    const error = document.getElementById('stamp-error');

    // 重設 UI
    modal.style.display = 'flex';
    loading.style.display = 'block';
    form.style.display = 'none';
    error.style.display = 'none';

    // 檢查是否有歌曲正在播放
    if (currentIndex < 0 || !queue[currentIndex]) {
        loading.style.display = 'none';
        error.innerText = "請先播放一首歌曲，才能將它釘選在此地！";
        error.style.display = 'block';
        return;
    }

    // 獲取位置
    if (!navigator.geolocation) {
        loading.style.display = 'none';
        error.innerText = "瀏覽器不支援定位功能";
        error.style.display = 'block';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const song = queue[currentIndex];

            // 填充表單
            document.getElementById('stamp-lat').value = lat;
            document.getElementById('stamp-lng').value = lng;
            document.getElementById('stamp-song-id').value = song.id;
            
            document.getElementById('stamp-song-title').innerText = song.title;
            document.getElementById('stamp-song-artist').innerText = song.artist;
            document.getElementById('stamp-song-cover').src = song.cover;
            
            // 顯示表單
            loading.style.display = 'none';
            form.style.display = 'block';

            // Reverse geocode
            fetchLocationName(lat, lng);
        },
        (err) => {
            console.error(err);
            loading.style.display = 'none';
            // 特定錯誤處理
            let errorMsg = err.message;
            if (err.code === 3) errorMsg = "定位逾時，請檢查 GPS 或網路訊號";
            else if (err.code === 1) errorMsg = "您拒絕了定位權限";
            else if (err.code === 2) errorMsg = "無法偵測到位置";
            
            error.innerText = "定位失敗：" + errorMsg;
            error.style.display = 'block';
        },
        { enableHighAccuracy: false, timeout: 20000, maximumAge: 60000 }
    );
}

function closeStampModal() {
    document.getElementById('stamp-modal').style.display = 'none';
}

function submitStamp(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = "發佈中...";

    const formData = new FormData(e.target);
    const locationName = document.getElementById('stamp-location-text').innerText;
    formData.append('location_name', locationName);
    
    fetch('api_save_stamp.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            closeStampModal();
            const locName = data.location_name || "未知地點";
            showAlert('成功', `音域留言已發佈！\nAI 判定地點：${locName}`);
        } else {
            alert('發佈失敗: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('發生網絡錯誤');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = "發佈音域";
    });
}

function fetchLocationName(lat, lng) {
    const textEl = document.getElementById('stamp-location-text');
    if (!textEl) return;
    
    textEl.innerText = "正在分析地點...";
    
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=zh-TW`;
    
    fetch(url, {
        headers: { 'Accept-Language': 'zh-TW', 'User-Agent': 'MusicStamp/1.0' }
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.address) {
            const a = data.address;
            const poi = a.amenity || a.shop || a.tourism || a.building || a.office || a.historic || a.leisure || a.craft;
            const road = a.road || a.pedestrian || a.suburb;
            
            let name = "";
            if (poi && road) name = `${poi} (${road})`;
            else if (poi) name = poi;
            else if (road) name = road;
            else name = data.display_name.split(',')[0];
            
            textEl.innerText = name;
        } else {
            textEl.innerText = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
        }
    })
    .catch(() => {
        textEl.innerText = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
    });
}

// 探索邏輯（背景掃描）
// 每 60 秒執行一次或依需求執行
function scanNearbyStamps() {
    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition((pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        
        // 半徑 200 公尺
        fetch(`api_get_stamps.php?lat=${lat}&lng=${lng}&radius=200`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                // 顯示最近的一個
                const stamp = data[0]; 
                showStampNotification(stamp);
            }
        })
        .catch(err => console.error("Scan error", err));
    });
}

function showStampNotification(stamp) {
    currentStampData = stamp;
    const notif = document.getElementById('stamp-notification');
    
    document.getElementById('notif-message').innerText = `"${stamp.message}"`;
    document.getElementById('notif-song').innerText = stamp.title;
    
    // 格式化距離
    let dist = Math.round(stamp.distance);
    document.getElementById('notif-dist').innerText = `距離 ${dist} 公尺 • ${stamp.user_name || '匿名'}`;
    
    notif.style.display = 'block';
    
    // 15 秒後自動隱藏
    setTimeout(() => {
        notif.style.display = 'none';
    }, 15000);
}

function playStamp() {
    if (!currentStampData) return;
    
    const notif = document.getElementById('stamp-notification');
    notif.style.display = 'none';
    
    // 自訂播放邏輯
    // 我們建立一個臨時序列物件或直接播放
    // 如果更新了 playContextSong 會比較容易，但目前先手動注入
    
    // 建立與播放器相容的歌曲物件
    const song = {
        id: currentStampData.song_real_id,
        title: currentStampData.title,
        artist: currentStampData.artist,
        file_path: 'music/' + currentStampData.file_path, // 假設 API 路徑相符
        cover: currentStampData.cover
    };
    
    // 使用統一的 insertAndPlaySong 以保持一致性
    if (typeof insertAndPlaySong === 'function') {
        insertAndPlaySong(
            currentStampData.title,
            currentStampData.artist,
            'music/' + currentStampData.file_path,
            currentStampData.cover || ('get_cover.php?id=' + currentStampData.song_real_id),
            currentStampData.song_real_id
        );
    } else {
        // 備案
        queue.splice(currentIndex + 1, 0, song);
        loadSong(currentIndex + 1);
    }
    
    const msg = `正在播放 ${currentStampData.user_name} 的音域留言`;
    if(typeof showAlert === 'function') showAlert('音域模式', msg);
}

// 載入時啟動自動掃描（低頻率）
setTimeout(scanNearbyStamps, 3000); // 載入後不久檢查一次
setInterval(scanNearbyStamps, 60000); // 每分鐘檢查一次
