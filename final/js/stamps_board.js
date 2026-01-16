// js/stamps_board.js

let map = null;
let markers = [];

function switchView(mode) {
    const listView = document.getElementById('list-view');
    const mapView = document.getElementById('map-view');
    const btnList = document.getElementById('btn-list-view');
    const btnMap = document.getElementById('btn-map-view');
    const pagination = document.getElementById('pagination-container');
    
    if (mode === 'list') {
        listView.style.display = 'grid';
        mapView.style.display = 'none';
        btnList.classList.add('active');
        btnMap.classList.remove('active');
        if (pagination) pagination.style.display = 'flex';
    } else {
        listView.style.display = 'none';
        mapView.style.display = 'block';
        btnList.classList.remove('active');
        btnMap.classList.add('active');
        if (pagination) pagination.style.display = 'none';
        
        // 若是初次載入則初始化地圖
        if (!map) {
            initMap();
        } else {
            setTimeout(() => { map.invalidateSize(); }, 200);
        }
    }
}

function initMap() {
    // 預設中心（台灣或第一則留言）
    let center = [23.973875, 120.982024]; // Taiwan center
    let zoom = 7;
    
    if (allStamps.length > 0) {
        // 使用第一則留言作為中心並放大
        center = [parseFloat(allStamps[0].lat), parseFloat(allStamps[0].lng)];
        zoom = 13;
    }

    map = L.map('leaflet-map').setView(center, zoom);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // 分組處理重疊地點 (距離精算聚合)
    const groups = [];
    const threshold = 0.0003; // 約 30 公尺內的標記都會聚合

    allStamps.forEach(stamp => {
        let joined = false;
        for (let group of groups) {
            const first = group[0];
            const dLat = stamp.lat - first.lat;
            const dLng = stamp.lng - first.lng;
            const dist = Math.sqrt(dLat * dLat + dLng * dLng);
            
            if (dist < threshold) {
                group.push(stamp);
                joined = true;
                break;
            }
        }
        if (!joined) {
            groups.push([stamp]);
        }
    });

    groups.forEach(stamps => {
        const first = stamps[0];
        const count = stamps.length;
        
        // 建立帶有使用者頭像的自訂 DivIcon（如果有重疊，顯示數量）
        const badgeHtml = count > 1 ? `<div style="position:absolute; right:-5px; top:-5px; background:#ff4757; color:white; border-radius:50%; width:20px; height:20px; font-size:12px; display:flex; align-items:center; justify-content:center; border:2px solid white; z-index:10; font-weight:bold;">${count}</div>` : '';
        
        const customIcon = L.divIcon({
            className: 'custom-avatar-marker',
            html: `<div style="
                width: 44px; 
                height: 44px; 
                background: white; 
                border-radius: 50% 50% 0 50%; 
                transform: rotate(45deg); 
                box-shadow: 0 3px 10px rgba(0,0,0,0.4); 
                border: 2px solid white; 
                overflow: visible; 
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                ">
                ${badgeHtml}
                <img src="${first.user_pic}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(first.user)}&background=${first.user_color || '666'}&color=fff'" style="
                    width: 40px; 
                    height: 40px; 
                    border-radius: 50%; 
                    transform: rotate(-45deg); 
                    object-fit: cover;
                ">
            </div>`,
            iconSize: [44, 44],
            iconAnchor: [22, 44], 
            popupAnchor: [0, -45]
        });

        const marker = L.marker([first.lat, first.lng], { icon: customIcon }).addTo(map);
        
        // 點擊邏輯：若有多個留言則開啟左側面板，只有一個則顯示 Popup
        marker.on('click', () => {
            if (count > 1) {
                openMarkerPanel(stamps);
            } else {
                closeMarkerPanel(); // 確保面板關閉
                const popupContent = createPopupContent(first);
                marker.bindPopup(popupContent).openPopup();
            }
        });
        
        markers.push({
            marker: marker,
            lat: first.lat,
            lng: first.lng,
            stamps: stamps
        });
    });
}

function createPopupContent(stamp) {
    return `
        <div style="min-width: 200px; padding: 5px;">
            <div style="display:flex; gap: 10px; align-items:center; margin-bottom: 8px;">
                 <img src="${stamp.cover}" style="width: 48px; height: 48px; border-radius: 6px; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                 <div>
                     <div class="map-popup-title" style="margin:0; font-size:1.05rem; color: #333;">${stamp.user}</div>
                     <div style="font-size: 0.75rem; color: #888; display:flex; align-items:center; gap:3px;">🎧 正在分享</div>
                 </div>
            </div>
            <div style="font-size: 0.95rem; margin-bottom: 8px; color: #333; line-height: 1.4;">${stamp.message}</div>
            <div style="background: #f5f5f5; padding: 8px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="playBoardSong(${stamp.songId || 0}, '${stamp.title}', '${stamp.artist}', '${stamp.path || ''}', '${stamp.cover}')">
                <div style="overflow: hidden;">
                    <div style="font-weight: bold; color: #333; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${stamp.title}</div>
                    <div style="font-size: 0.75rem; color: #666;">${stamp.artist}</div>
                </div>
                <div style="color: #ff4757; font-size: 1.2rem;">▶</div>
            </div>
            <div style="margin-top: 5px; font-size: 0.7rem; color: #999; text-align: right;">
                📍 ${stamp.location}
            </div>
        </div>
    `;
}

function openMarkerPanel(stamps) {
    const panel = document.getElementById('marker-list-panel');
    const countEl = document.getElementById('marker-count');
    const contentEl = document.getElementById('marker-list-content');
    
    countEl.innerText = stamps.length;
    let html = '';
    
    stamps.forEach(stamp => {
        html += `
            <div style="background: #2a2a2a; border-radius: 10px; padding: 12px; margin-bottom: 10px; border: 1px solid #333;">
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
                    <img src="${stamp.user_pic}" style="width: 24px; height: 24px; border-radius: 50%;">
                    <div style="font-size: 0.85rem; color: #fff; font-weight: bold;">${stamp.user}</div>
                    <div style="font-size: 0.7rem; color: #888; margin-left: auto;">${stamp.time || ''}</div>
                </div>
                <div style="font-size: 0.9rem; color: #ddd; margin-bottom: 10px; line-height: 1.4; word-break: break-all;">${stamp.message}</div>
                <div style="display: flex; gap: 8px; align-items: stretch; width: 100%; overflow: hidden;">
                    <div style="flex: 1; min-width: 0; background: #1a1a1a; padding: 8px; border-radius: 6px; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='#222'" onmouseout="this.style.background='#1a1a1a'" onclick="playBoardSong(${stamp.songId}, '${stamp.title}', '${stamp.artist}', '${stamp.path}', '${stamp.cover}')">
                        <img src="${stamp.cover}" style="width: 32px; height: 32px; border-radius: 4px; flex-shrink: 0;">
                        <div style="flex: 1; overflow: hidden;">
                            <div style="font-size: 0.8rem; color: #eee; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${stamp.title}</div>
                            <div style="font-size: 0.7rem; color: #aaa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${stamp.artist}</div>
                        </div>
                        <div style="color: #ff4757; flex-shrink: 0;">▶</div>
                    </div>
                    <button onclick="jumpToList(${stamp.id})" style="background: #333; border: none; border-radius: 6px; color: #aaa; width: 40px; flex-shrink: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="在清單中查看" onmouseover="this.style.background='#444'; this.style.color='#fff'" onmouseout="this.style.background='#333'; this.style.color='#aaa'">
                        <img src="img/map.jpg" style="width: 18px; height: 18px; border-radius: 4px; opacity: 0.7;">
                    </button>
                </div>
            </div>
        `;
    });
    
    contentEl.innerHTML = html;
    panel.style.display = 'flex';
}

function jumpToList(id) {
    const index = allStamps.findIndex(s => s.id == id);
    if (index === -1) return;
    
    const limit = 9; // 需與 PHP 的 $limit 一致
    const targetPage = Math.floor(index / limit) + 1;
    
    const params = new URLSearchParams(window.location.search);
    const currentPage = parseInt(params.get('page')) || 1;
    
    if (currentPage === targetPage) {
        switchView('list');
        const target = document.getElementById('stamp-card-' + id);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.style.transition = 'all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            target.style.transform = 'scale(1.05)';
            target.style.boxShadow = '0 0 30px rgba(108, 92, 231, 0.6)';
            target.style.borderColor = '#6c5ce7';
            
            setTimeout(() => {
                target.style.transform = '';
                target.style.boxShadow = '';
                target.style.borderColor = '';
            }, 2000);
        }
    } else {
        // 跳轉到正確的分頁，並透過 Hash 標記
        window.location.href = `stamps_board.php?page=${targetPage}#stamp-card-${id}`;
    }
}

// 處理從其他分頁跳轉過來的自動捲動
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#stamp-card-')) {
        setTimeout(() => {
            const target = document.querySelector(hash);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.style.transform = 'scale(1.05)';
                target.style.boxShadow = '0 0 30px rgba(108, 92, 231, 1)';
                target.style.borderColor = '#6c5ce7';
                setTimeout(() => {
                    target.style.transform = '';
                    target.style.boxShadow = '';
                    target.style.borderColor = '';
                }, 3000);
            }
        }, 500);
    }
});

function closeMarkerPanel() {
    const panel = document.getElementById('marker-list-panel');
    if (panel) panel.style.display = 'none';
}

// 從列表視圖「在地圖上查看」按鈕呼叫的函式
function showOnMap(btn) {
    const card = btn.closest('.stamp-card');
    if (!card) return;
    
    try {
        const data = JSON.parse(card.getAttribute('data-json'));
        
        // 切換至地圖模式
        switchView('map');
        
        // 等待地圖初始化/渲染
        setTimeout(() => {
            map.flyTo([data.lat, data.lng], 16, {
                animate: true,
                duration: 1.5
            });
            
            // 開啟彈出視窗或面板
            markers.forEach(m => {
                // 浮點座標模糊比對
                if (Math.abs(m.lat - data.lat) < 0.0001 && Math.abs(m.lng - data.lng) < 0.0001) {
                    if (m.stamps && m.stamps.length > 1) {
                        openMarkerPanel(m.stamps);
                    } else {
                        m.marker.openPopup();
                    }
                }
            });
        }, 300);
        
    } catch(e) {
        console.error(e);
    }
}

// Bridge to play song (reuse existing logic from other pages)
// 播放歌曲橋接器（重複使用其他頁面的現有邏輯）
function playBoardSong(id, title, artist, path, cover) {
    if (window.parent && window.parent.loadQueue) {
        // 在「所有歌曲」情境下播放此曲（隨機/音樂庫）
        // 這確保了此曲播放完畢後會繼續播放。
        window.parent.loadQueue('all', 0, id);
    } else {
        console.warn("Parent player not found");
        // 備案：嘗試僅播放單曲
        if (window.parent && window.parent.playSong) {
             window.parent.playSong(title, artist, 'music/' + path, cover, id, 'one', 0, '音域留言');
        }
    }
}

// --- 編輯與刪除邏輯（自訂模態框） ---

let currentEditId = null;
let currentDeleteId = null;
let currentDeleteBtn = null; // 儲存按鈕參考以移除卡片

function editStamp(id) {
    currentEditId = id;
    const msgEl = document.getElementById('msg-' + id);
    const modal = document.getElementById('edit-stamp-modal');
    const input = document.getElementById('edit-stamp-input');
    
    input.value = msgEl.innerText;
    modal.style.display = 'flex';
    input.focus();
}

function closeEditModal() {
    document.getElementById('edit-stamp-modal').style.display = 'none';
    currentEditId = null;
}

function submitEditStamp() {
    if (!currentEditId) return;
    
    const input = document.getElementById('edit-stamp-input');
    const newMsg = input.value.trim();
    const msgEl = document.getElementById('msg-' + currentEditId);
    
    if (newMsg) {
        // 呼叫 API
        const formData = new FormData();
        formData.append('id', currentEditId);
        formData.append('message', newMsg);
        
        fetch('api_edit_stamp.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                msgEl.innerText = newMsg;
                closeEditModal();
                // 重新整理以更新地圖標記文字
                location.reload(); 
            } else {
                alert('編輯失敗: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('發生錯誤');
        });
    }
}

function deleteStamp(id, btn) {
    currentDeleteId = id;
    currentDeleteBtn = btn;
    document.getElementById('delete-stamp-modal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-stamp-modal').style.display = 'none';
    currentDeleteId = null;
    currentDeleteBtn = null;
}

function confirmDeleteStamp() {
    if (!currentDeleteId) return;
    
    const formData = new FormData();
    formData.append('id', currentDeleteId);
    
    fetch('api_delete_stamp.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            closeDeleteModal();
            // 從 UI 移除卡片
            if (currentDeleteBtn) {
                const card = currentDeleteBtn.closest('.stamp-card');
                if (card) card.remove();
            }
            // 重新整理以更新地圖
            location.reload();
        } else {
            alert('刪除失敗: ' + data.message);
            closeDeleteModal();
        }
    })
    .catch(err => {
        console.error(err);
        alert('發生錯誤');
        closeDeleteModal();
    });
}

// Logic for New Stamp Modal
function openNewStampModal() {
    const modal = document.getElementById('new-stamp-modal');
    modal.style.display = 'flex';
    
    // Reset fields
    document.getElementById('new-stamp-form').reset();
    document.getElementById('new-stamp-lat').value = '';
    document.getElementById('new-stamp-lng').value = '';
    
    // Auto fetch location
    const statusText = document.getElementById('loc-text');
    const spinner = document.getElementById('loc-spinner');
    
    if (navigator.geolocation) {
        spinner.style.display = 'inline';
        statusText.innerText = '正在獲取目前位置...';
        statusText.style.color = '#aaa';
        
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('new-stamp-lat').value = pos.coords.latitude;
                document.getElementById('new-stamp-lng').value = pos.coords.longitude;
                statusText.innerText = '已獲取位置: ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4);
                spinner.style.display = 'none';
                statusText.style.color = '#55efc4';
            },
            (err) => {
                console.error(err);
                statusText.innerText = '無法獲取位置 (' + err.message + ')';
                spinner.style.display = 'none';
                statusText.style.color = '#ff7675';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        statusText.innerText = '您的瀏覽器不支援地理定位';
    }
}

function closeNewStampModal() {
    document.getElementById('new-stamp-modal').style.display = 'none';
}

function submitNewStamp(e) {
    e.preventDefault();
    
    const songId = document.getElementById('new-stamp-song').value;
    const message = document.getElementById('new-stamp-msg').value;
    const lat = document.getElementById('new-stamp-lat').value;
    const lng = document.getElementById('new-stamp-lng').value;
    
    if (!songId) { alert('請選擇歌曲'); return; }
    if (!message) { alert('請輸入留言'); return; }
    if (!lat || !lng) { 
        alert('尚未獲取位置資訊，請確認您已允許定位權限並稍後再試。'); 
        openNewStampModal(); 
        return; 
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerText;
    submitBtn.disabled = true;
    submitBtn.innerText = '發佈中...';
    
    const formData = new FormData();
    formData.append('song_id', songId);
    formData.append('message', message);
    formData.append('lat', lat);
    formData.append('lng', lng);
    
    fetch('api_save_stamp.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            closeNewStampModal();
            location.reload();
        } else {
            alert('發佈失敗: ' + (data.message || 'Unknown error'));
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        alert('發生錯誤');
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    });
}
