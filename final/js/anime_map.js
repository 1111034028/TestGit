// Music Stamp Map Logic (GitHub Style Layout via Iframe)

let map;
let allStamps = [];
let markers = []; 
const DEFAULT_ZOOM = 9;
const DEFAULT_CENTER = [23.97565, 120.973882]; 

let boardCurrentPage = 1;
const boardItemsPerPage = 6;

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    fetchStamps();
    createDetailModal(); 
    
    // Hijack Parent Search Bar
    setupParentSearch();
    
    // Auto Adjust UI for Parent Player
    initPlayerBarObserver();

    const toggleBtn = document.getElementById('sidebar-toggle');
    if(toggleBtn) {
        toggleBtn.addEventListener('click', toggleMapSidebar);
    }
});

function initMap() {
    // If map container not found (e.g. board view default?), handle gracefully
    if(!document.getElementById('map')) return;

    map = L.map('map', {
        zoomControl: false, 
        attributionControl: false
    }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

    // Google Maps Layer (Roadmap)
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps'
    }).addTo(map);
    
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Try to locate user automatically on load (High Accuracy GPS)
    map.locate({setView: true, maxZoom: 16, enableHighAccuracy: true, timeout: 5000});
    
    map.on('locationfound', function(e) {
        // GPS success: do nothing, map already moved
        console.log("GPS Location found:", e.latlng);
    });

    map.on('locationerror', function(e) {
        console.warn("GPS failed, trying IP Geolocation...", e.message);
        
        // Fallback: IP Geolocation (Approximate)
        fetch('https://ipapi.co/json/')
            .then(res => res.json())
            .then(data => {
                if(data.latitude && data.longitude) {
                    const lat = data.latitude;
                    const lng = data.longitude;
                    map.setView([lat, lng], 13);
                    console.log("IP Location found:", lat, lng);
                }
            })
            .catch(err => {
                console.error("IP Location failed:", err);
                // Last resort: Stay at Default (Nantou)
            });
    });
}

function switchView(viewName) {
    const tabMap = document.getElementById('tab-map');
    const tabBoard = document.getElementById('tab-board');
    const viewMap = document.getElementById('map-view');
    const viewBoard = document.getElementById('message-board-view');
    
    if (viewName === 'map') {
        if(tabMap) tabMap.classList.add('active');
        if(tabBoard) tabBoard.classList.remove('active');
        if(viewMap) viewMap.style.display = 'block';
        if(viewBoard) viewBoard.style.display = 'none';
        
        if(map) setTimeout(() => map.invalidateSize(), 100);
    } else {
        if(tabMap) tabMap.classList.remove('active');
        if(tabBoard) tabBoard.classList.add('active');
        if(viewMap) viewMap.style.display = 'none';
        if(viewBoard) viewBoard.style.display = 'block';
        
        boardCurrentPage = 1; // Reset to page 1 when switching to board
        renderMessageBoard();
    }
}

// --- Parent Search Bar Integration ---
let parentSearchInput = null;
let parentSearchForm = null;
let originalPlaceholder = '';

function setupParentSearch() {
    if (window.parent && window.parent.document) {
        // Try to find the search input in parent
        // According to index.php, class is 'search-input' and container is 'search-container'
        parentSearchInput = window.parent.document.querySelector('.search-input');
        parentSearchForm = window.parent.document.querySelector('.search-container');
        
        if (parentSearchInput) {
            // Save state
            originalPlaceholder = parentSearchInput.placeholder;
            
            // Set new state
            parentSearchInput.placeholder = "搜尋城市、作品、地標...";
            parentSearchInput.value = ''; // Clear prev search
            
            // Add listener
            parentSearchInput.addEventListener('input', handleParentSearchInput);
            
            // Prevent form submit
            if (parentSearchForm) {
                parentSearchForm.addEventListener('submit', handleParentSearchSubmit);
            }
        }
    }
    
    // Restore on unload
    window.addEventListener('unload', restoreParentSearch);
}

function restoreParentSearch() {
    if (parentSearchInput) {
        parentSearchInput.placeholder = originalPlaceholder;
        parentSearchInput.value = '';
        parentSearchInput.removeEventListener('input', handleParentSearchInput);
    }
    if (parentSearchForm) {
        parentSearchForm.removeEventListener('submit', handleParentSearchSubmit);
    }
}

function handleParentSearchInput(e) {
    const term = e.target.value.toLowerCase();
    filterMarkers(term);
}

function handleParentSearchSubmit(e) {
    e.preventDefault(); 
}

function filterMarkers(term) {
    if (!term) term = '';
    
    markers.forEach(marker => {
        const stamp = allStamps.find(s => s.id === marker.stampId);
        if (!stamp) return;
        
        const match = stamp.location_name.toLowerCase().includes(term) || 
                      stamp.message.toLowerCase().includes(term) ||
                      (stamp.title && stamp.title.toLowerCase().includes(term)) ||
                      (stamp.artist && stamp.artist.toLowerCase().includes(term));
                      
        if (match) {
            // Check if marker is already on map
            if(!map.hasLayer(marker)) {
                marker.addTo(map);
            }
        } else {
            map.removeLayer(marker);
        }
    });
    
    // Also filter message board if visible
    if(document.getElementById('message-board-view').style.display !== 'none') {
        boardCurrentPage = 1; // Reset page when filtering
        renderMessageBoard(term); // Pass term to refine board
    }
}

// --- Interaction Logic: Map Click & Upload ---

async function getReverseGeocode(lat, lng) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), 5000);

    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=zh-TW`, {
            signal: controller.signal
        });
        clearTimeout(id);
        
        if (!res.ok) throw new Error('Network response was not ok');
        
        const data = await res.json();
        
        let locName = '未知地點';
        if(data && data.address) {
            locName = data.name || 
                      data.address.amenity || 
                      data.address.building || 
                      data.address.road || 
                      data.address.suburb || 
                      data.address.city || 
                      data.display_name.split(',')[0];
        } else if (data && data.display_name) {
            locName = data.display_name.split(',')[0];
        }
        return { name: locName, full: data.display_name || '' };
    } catch(err) {
        console.error("Geocoding error", err);
        return { name: '未知地點', full: '' };
    }
}

function initMapClick() {
    if(!map) return;
    map.on('click', async function(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        
        // Initial Loading Popup
        const popup = L.popup()
            .setLatLng(e.latlng)
            .setContent(`
                <div style="text-align:center; min-width:180px;">
                    <div style="color:#666; margin-bottom:5px;">正在查詢地點資訊...</div>
                    <div class="spinner" style="border:2px solid #f3f3f3; border-top:2px solid #1db954; border-radius:50%; width:16px; height:16px; animation:spin 1s linear infinite; margin:0 auto;"></div>
                </div>
                <style>@keyframes spin {0% {transform: rotate(0deg);} 100% {transform: rotate(360deg);}}</style>
            `)
            .openOn(map);

        const geo = await getReverseGeocode(lat, lng);
        
        // Update Popup content
        popup.setContent(`
            <div style="text-align:center; padding:5px; color:#333;">
                <div style="font-weight:700; font-size:14px; margin-bottom:4px; color:#000;">${geo.name}</div>
                <div style="font-size:11px; color:#666; margin-bottom:8px;">${lat}, ${lng}</div>
                <button id="btn-add-here" 
                    style="background:#1db954; color:white; border:none; padding:8px 16px; border-radius:20px; cursor:pointer; font-size:13px; font-weight:600; box-shadow:0 2px 5px rgba(0,0,0,0.2);">
                    在此新增打卡
                </button>
            </div>
        `);
        
        setTimeout(() => {
            const btn = document.getElementById('btn-add-here');
            if(btn) {
                btn.onclick = () => {
                    map.closePopup();
                    openUploadModal({
                        lat: lat, 
                        lng: lng, 
                        locationName: geo.name
                    });
                };
            }
        }, 50);
    });
}

// Ensure initMapClick is called
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initMapClick, 1000);
});

// --- Upload Modal Logic (Cross-Frame) ---

function getParentDocument() {
    return (window.parent && window.parent.document) ? window.parent.document : document;
}

function createParentModal() {
    const pDoc = getParentDocument();
    if(pDoc.getElementById('parent-upload-modal')) return;

    const div = pDoc.createElement('div');
    div.id = 'parent-upload-modal';
    div.className = 'modal-overlay'; 
    div.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px);
        z-index: 9999; display: none; justify-content: center; align-items: center;
    `;
    
    // Background click to close
    div.onclick = function(e) {
        if(e.target === div) closeUploadModal();
    };
    
    div.innerHTML = `
        <div class="modal-box" style="width: 600px; max-width: 90%; max-height: 90vh; overflow-y: auto; background: #121212; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.7); border-radius: 16px; color:white; font-family:sans-serif;">
            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0; font-size:18px;">新建音域地圖打卡</h2>
                <button id="btn-close-parent-modal" style="background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            
            <div style="padding:20px;">
                <form id="parent-upload-form">
                     <!-- Step 1: Song Selection -->
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#1db954;">1. 選擇音樂 (必填)</label>
                        <div style="display:flex; gap:10px;">
                            <input type="text" id="parent-song-search-input" placeholder="輸入歌名或藝人搜尋..." style="flex:1; padding:10px; border-radius:4px; border:1px solid #333; background:#222; color:white;">
                        </div>
                        <div id="parent-song-results" style="margin-top:10px; max-height:200px; overflow-y:auto;"></div>
                        
                        <!-- Hidden Inputs -->
                        <input type="hidden" name="song_id" id="parent-selected_song_id" required>
                        
                        <div id="parent-selected-song-preview" style="display:none; margin-top:10px; background:rgba(29, 185, 84, 0.1); padding:10px; border-radius:6px; flex-direction:row; align-items:center;"></div>
                    </div>

                    <!-- Step 2: Location Info -->
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#1db954;">2. 地點資訊</label>
                        
                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; color:#aaa;">地點名稱</label>
                            <input type="text" name="location_name" id="parent-input-location-name" placeholder="例如：我的祕密基地 (留空可自動偵測)" style="width:100%; padding:10px; border-radius:4px; border:1px solid #333; background:#222; color:white; margin-top:4px;">
                        </div>
                        
                        <div style="display:flex; gap:10px; margin-bottom:12px;">
                            <div style="flex:1;">
                                <label style="font-size:12px; color:#aaa;">緯度 (Lat)</label>
                                <input type="text" name="lat" id="parent-input-lat" required readonly style="width:100%; padding:10px; border-radius:4px; border:1px solid #333; background:#181818; color:#777; margin-top:4px; cursor:not-allowed;">
                            </div>
                            <div style="flex:1;">
                                <label style="font-size:12px; color:#aaa;">經度 (Lng)</label>
                                <input type="text" name="lng" id="parent-input-lng" required readonly style="width:100%; padding:10px; border-radius:4px; border:1px solid #333; background:#181818; color:#777; margin-top:4px; cursor:not-allowed;">
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-size:12px; color:#aaa;">留言 / 心得</label>
                            <textarea name="message" rows="3" placeholder="想說的話..." style="width:100%; padding:10px; border-radius:4px; border:1px solid #333; background:#222; color:white; margin-top:4px;"></textarea>
                        </div>
                        
                         <div style="margin-bottom:12px;">
                            <label style="font-size:12px; color:#aaa;">上傳實景照片 (選填)</label>
                            <input type="file" id="parent-input-image" name="image" accept="image/*" style="width:100%; padding:10px; background:#222; border-radius:4px; margin-top:4px;">
                            <div id="parent-image-preview-container" style="margin-top:10px; display:none; border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,0.1); max-height: 300px;">
                                <img id="parent-image-preview" src="" style="width:100%; height:auto; display:block; object-fit:contain; background:#000;">
                            </div>
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <button type="submit" class="btn-submit" style="background:#1db954; color:white; border:none; padding:12px 24px; border-radius:30px; font-weight:bold; cursor:pointer;">確認建立</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    pDoc.body.appendChild(div);
    
    // Bind Events
    div.onclick = function(e) { if (e.target === div) closeUploadModal(); };
    pDoc.getElementById('btn-close-parent-modal').onclick = closeUploadModal;
    
    // Search Song Logic
    const searchInput = pDoc.getElementById('parent-song-search-input');
    searchInput.addEventListener('input', (e) => searchSongParent(e.target.value));
    searchInput.addEventListener('focus', (e) => searchSongParent(e.target.value));
    searchInput.addEventListener('click', (e) => searchSongParent(e.target.value));
    
    // Image Preview
    const imgInput = pDoc.getElementById('parent-input-image');
    if(imgInput) {
        imgInput.onchange = function(e) {
            const file = e.target.files[0];
            const previewResult = pDoc.getElementById('parent-image-preview');
            const previewContainer = pDoc.getElementById('parent-image-preview-container');
            if(file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewResult.src = evt.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                previewResult.src = '';
                previewContainer.style.display = 'none';
            }
        };
    }

    const form = pDoc.getElementById('parent-upload-form');
    form.onsubmit = handleParentUploadSubmit;
}

function openUploadModal(defaults = {}) {
    createParentModal(); 
    const pDoc = getParentDocument();
    const modal = pDoc.getElementById('parent-upload-modal');
    
    // Reset Form
    pDoc.getElementById('parent-upload-form').reset();
    pDoc.getElementById('parent-song-results').innerHTML = '';
    pDoc.getElementById('parent-selected-song-preview').style.display = 'none';
    pDoc.getElementById('parent-image-preview-container').style.display = 'none';
    pDoc.getElementById('parent-selected_song_id').value = '';

    const latInput = pDoc.getElementById('parent-input-lat');
    const lngInput = pDoc.getElementById('parent-input-lng');
    const locInput = pDoc.getElementById('parent-input-location-name');

    // Case 1: Coordinates provided (e.g. from Map Click or GPS)
    if(defaults.lat && defaults.lng) {
        latInput.value = defaults.lat;
        lngInput.value = defaults.lng;
        if(defaults.locationName && locInput) {
            locInput.value = defaults.locationName;
        } else if (locInput) {
            locInput.value = '正在取得位置名稱...';
            getReverseGeocode(defaults.lat, defaults.lng).then(geo => {
                if(locInput.value === '正在取得位置名稱...') locInput.value = geo.name;
            });
        }
    } 
    // Case 2: No coords provided (e.g. from FAB button) -> Use Device GPS
    else {
        showToast("正在取得導航位置...", "info");
        
        navigator.geolocation.getCurrentPosition(async (pos) => {
            const lat = pos.coords.latitude.toFixed(6);
            const lng = pos.coords.longitude.toFixed(6);
            
            latInput.value = lat;
            lngInput.value = lng;
            locInput.value = '正在取得位置名稱...';
            
            const geo = await getReverseGeocode(lat, lng);
            if(locInput.value === '正在取得位置名稱...') {
                locInput.value = geo.name;
            }
            
            // Move map to this point too
            if(map) map.flyTo([lat, lng], 18);
            
            showToast("成功定位當前位置", "success");
        }, (err) => {
            console.warn("GPS failed, using Map Center:", err.message);
            // Fallback: Map Center
            if (typeof map !== 'undefined' && map) {
                const center = map.getCenter();
                const lat = center.lat.toFixed(6);
                const lng = center.lng.toFixed(6);
                latInput.value = lat;
                lngInput.value = lng;
                locInput.value = '正在取得中心點位置...';
                getReverseGeocode(lat, lng).then(geo => {
                    if(locInput.value === '正在取得中心點位置...') locInput.value = geo.name;
                });
            }
        }, { enableHighAccuracy: true, timeout: 5000 });
    }
    
    modal.style.display = 'flex';
}

function openEditModal(stampId) {
    const stamp = allStamps.find(s => s.id == stampId);
    if(!stamp) return;

    createParentModal(); 
    const pDoc = getParentDocument();
    const modal = pDoc.getElementById('parent-upload-modal');
    const form = pDoc.getElementById('parent-upload-form');
    
    // Switch to Edit Mode UI
    pDoc.querySelector('.modal-box h2').innerText = '編輯打卡資訊';
    form.querySelector('.btn-submit').innerText = '確認修改';
    
    // Hide Step 1 (Song Selection) as we don't change song in edit usually
    const step1 = pDoc.querySelector('#parent-upload-form > div:nth-child(1)');
    if(step1) step1.style.display = 'none';

    // Pre-fill data
    pDoc.getElementById('parent-input-location-name').value = stamp.location_name;
    pDoc.getElementById('parent-input-lat').value = stamp.latitude;
    pDoc.getElementById('parent-input-lng').value = stamp.longitude;
    pDoc.querySelector('textarea[name="message"]').value = stamp.message;
    pDoc.getElementById('parent-selected_song_id').value = stamp.song_id;
    
    // Add hidden inputs for Edit action
    let actionInput = pDoc.getElementById('parent-form-action');
    if(!actionInput) {
        actionInput = pDoc.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.id = 'parent-form-action';
        form.appendChild(actionInput);
    }
    actionInput.value = 'edit';

    let idInput = pDoc.getElementById('parent-form-id');
    if(!idInput) {
        idInput = pDoc.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.id = 'parent-form-id';
        form.appendChild(idInput);
    }
    idInput.value = stampId;

    // Show current image preview if exists
    const previewContainer = pDoc.getElementById('parent-image-preview-container');
    const previewImg = pDoc.getElementById('parent-image-preview');
    if(stamp.image_path) {
        previewContainer.style.display = 'block';
        previewImg.src = stamp.image_path;
    } else {
        previewContainer.style.display = 'none';
    }

    // Change subit handler temporary? No, handleParentUploadSubmit expects 'api_save_stamp.php'.
    // We should point it to 'api_manage_stamp.php' if it's an edit.
    form.onsubmit = async (e) => {
        e.preventDefault();
        const btn = form.querySelector('.btn-submit');
        btn.disabled = true; btn.innerText = '儲存中...';
        
        try {
            const formData = new FormData(form);
            const res = await fetch('api_manage_stamp.php', { method: 'POST', body: formData });
            const data = await res.json();
            if(data.status === 'success') {
                showToast('修改成功！', 'success');
                closeUploadModal();
                closeDetailModal();
                fetchStamps();
            } else {
                showToast('❌ 錯誤: ' + data.message, 'error');
            }
        } catch(err) {
            showToast('⚠️ 網路錯誤', 'error');
        } finally {
            btn.disabled = false; btn.innerText = '確認修改';
        }
    };

    modal.style.display = 'flex';
}

function closeUploadModal() {
    const pDoc = getParentDocument();
    const modal = pDoc.getElementById('parent-upload-modal');
    if(modal) modal.style.display = 'none';
}

function searchSongParent(query) {
    const pDoc = getParentDocument();
    const resultsDiv = pDoc.getElementById('parent-song-results');
    
    if (!query || query.length < 1) {
        resultsDiv.innerHTML = '';
        return;
    }
    
    // allSongsMapData is injected in anime_map.php
    if (typeof allSongsMapData === 'undefined') {
        console.error("allSongsMapData is missing!");
        resultsDiv.innerHTML = '<div style="color:red; padding:10px;">資料載入錯誤，請重新整理頁面</div>';
        return;
    }

    let matches = [];
    if (!query || query.trim() === '') {
        // Show all/top songs if query is empty
        matches = allSongsMapData.slice(0, 20);
    } else {
        matches = allSongsMapData.filter(s => 
            (s.title && s.title.toLowerCase().includes(query.toLowerCase())) || 
            (s.artist && s.artist.toLowerCase().includes(query.toLowerCase()))
        ).slice(0, 10);
    }
    
    console.log(`Searching for "${query}", found ${matches.length} matches from total ${allSongsMapData.length}`);

    if(matches.length === 0) {
        resultsDiv.innerHTML = '<div style="color:#aaa; padding:10px;">找不到相關歌曲，請確認歌名</div>';
        return;
    }

    resultsDiv.innerHTML = '';
    matches.forEach(song => {
        const div = pDoc.createElement('div');
        // Ensure high contrast and pointer events
        div.className = 'song-result-item'; // Add class for potential CSS handling
        div.style.cssText = 'display:flex; gap:10px; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1); cursor:pointer; align-items:center; background:#222; transition: background 0.2s;';
        div.onmouseover = function() { this.style.background = '#333'; };
        div.onmouseout = function() { this.style.background = '#222'; };
        
        div.innerHTML = `
            <img src="${song.cover}" onerror="this.onerror=null;this.src='img/logo_small.png';this.style.border='1px solid #333';" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #444;">
            <div>
                <div style="font-weight:600; color:#fff; font-size:14px;">${escapeHtml(song.title)}</div>
                <div style="font-size:12px; color:#aaa;">${escapeHtml(song.artist)}</div>
            </div>
        `;
        div.onclick = function() { 
            console.log("Selected song:", song.id);
            pDoc.getElementById('parent-selected_song_id').value = song.id;
            
            const preview = pDoc.getElementById('parent-selected-song-preview');
            preview.style.display = 'flex';
            preview.innerHTML = `
                <img src="${song.cover}" onerror="this.onerror=null;this.src='img/logo_small.png';" style="width:50px; height:50px; object-fit:cover; margin-right:12px; border-radius:4px; border:1px solid #1db954;">
                <div>
                    <div style="color:#1db954; font-size:12px; font-weight:bold;">✓ 已選擇音樂</div>
                    <div style="font-weight:600; color:white; font-size:15px;">${escapeHtml(song.title)}</div>
                    <div style="font-size:13px; color:#bbb;">${escapeHtml(song.artist)}</div>
                </div>
                <button type="button" onclick="document.getElementById('parent-selected-song-preview').style.display='none'; document.getElementById('parent-selected_song_id').value='';" 
                    style="margin-left:auto; background:none; border:none; color:#666; cursor:pointer; font-size:18px;">&times;</button>
            `;
            resultsDiv.innerHTML = '';
            pDoc.getElementById('parent-song-search-input').value = ''; 
        };
        resultsDiv.appendChild(div);
    });
}

async function handleParentUploadSubmit(e) {
    e.preventDefault();
    const pDoc = getParentDocument();
    const form = pDoc.getElementById('parent-upload-form');
    
    // Validation
    const songId = pDoc.getElementById('parent-selected_song_id').value;
    if(!songId) {
        alert('請先選擇一首音樂！');
        return;
    }

    const btn = form.querySelector('.btn-submit');
    if(btn) { btn.disabled = true; btn.innerText = '上傳中...'; }
    
    const formData = new FormData(form);
    
    try {
        const res = await fetch('api_save_stamp.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.status === 'success') {
            showToast('🎉 打卡成功！已新增到地圖', 'success');
            closeUploadModal();
            fetchStamps(); 
        } else {
            showToast('❌ 失敗: ' + (data.message || '未知錯誤'), 'error');
        }
    } catch(err) {
        console.log(err);
        showToast('⚠️ 上傳發生錯誤，請稍後再試', 'error');
    } finally {
        if(btn) { btn.disabled = false; btn.innerText = '確認建立'; }
    }
}

// --- Toast Notification System ---
function showToast(message, type = 'info') {
    const pDoc = getParentDocument();
    let toastContainer = pDoc.getElementById('toast-container');
    
    // Create container if not exists
    if (!toastContainer) {
        toastContainer = pDoc.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position:fixed; bottom:30px; left:50%; transform:translateX(-50%); z-index:10000; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
        pDoc.body.appendChild(toastContainer);
    }
    
    const toast = pDoc.createElement('div');
    const color = type === 'success' ? '#1db954' : (type === 'error' ? '#e91e63' : '#2196f3');
    const icon = type === 'success' ? '✔' : (type === 'error' ? '✖' : 'ℹ');
    
    toast.style.cssText = `
        background: #282828; 
        color: white; 
        padding: 12px 24px; 
        border-radius: 50px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.5); 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        font-size: 14px; 
        font-family: 'Inter', sans-serif;
        border-left: 4px solid ${color};
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    `;
    
    toast.innerHTML = `<span style="font-weight:bold; color:${color}; font-size:16px;">${icon}</span> <span>${message}</span>`;
    
    toastContainer.appendChild(toast);
    
    // Animate In
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });
    
    // Animate Out
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showConfirm(title, message) {
    return new Promise((resolve) => {
        const pDoc = getParentDocument();
        const overlay = pDoc.createElement('div');
        overlay.style.cssText = 'position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:20000; display:flex; justify-content:center; align-items:center; opacity:0; transition:opacity 0.3s;';
        
        const box = pDoc.createElement('div');
        box.style.cssText = 'background:#181818; border:1px solid rgba(255,255,255,0.1); border-radius:16px; width:360px; padding:30px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.5); transform:scale(0.8); transition:transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);';
        
        box.innerHTML = `
            <div style="font-size:20px; font-weight:700; color:white; margin-bottom:12px;">${title}</div>
            <div style="font-size:14px; color:#b3b3b3; margin-bottom:24px; line-height:1.5;">${message}</div>
            <div style="display:flex; gap:12px; justify-content:center;">
                <button id="confirm-cancel" style="flex:1; padding:12px; border-radius:30px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:white; cursor:pointer; font-weight:600;">取消</button>
                <button id="confirm-ok" style="flex:1; padding:12px; border-radius:30px; border:none; background:#1db954; color:white; cursor:pointer; font-weight:600;">確定</button>
            </div>
        `;
        
        overlay.appendChild(box);
        pDoc.body.appendChild(overlay);
        
        // Finalize styles after append to trigger transitions
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            box.style.transform = 'scale(1)';
        });
        
        pDoc.getElementById('confirm-cancel').onclick = () => {
            close();
            resolve(false);
        };
        pDoc.getElementById('confirm-ok').onclick = () => {
            close();
            resolve(true);
        };
        
        overlay.onclick = (e) => { if(e.target === overlay) { close(); resolve(false); } };
        
        function close() {
            overlay.style.opacity = '0';
            box.style.transform = 'scale(0.8)';
            setTimeout(() => overlay.remove(), 300);
        }
    });
}
// OLD searchAnime / selectAnime is no longer needed but kept for safety if referenced elsewhere
// ...

// ---------------------------


async function fetchStamps() {
    try {
        const res = await fetch('api_get_stamps.php');
        const data = await res.json();
        
        if (Array.isArray(data)) {
            allStamps = data;
            
            const countEl = document.getElementById('board-counter');
            if(countEl) countEl.innerText = allStamps.length;
            
            renderMarkers(allStamps);
            
            // Refresh Message Board as well
            renderMessageBoard();
            
            // Initially empty sidebar list or keep current if valid? 
            // For now, reset sidebar to avoid stale data
            renderSidebarList([]); 
        }
    } catch (err) {
        console.error("Failed to fetch stamps", err);
    }
}

function playSongOnMap(song) {
    if(!song || !song.file_path) return;
    
    // Use player_bridge.js if available, otherwise try direct parent call
    const playFn = (typeof playContextSong === 'function') ? playContextSong : (window.parent && window.parent.playSong);
    
    if(typeof playFn === 'function') {
        const fullFilePath = 'music/' + song.file_path;
        playFn(song.title, song.artist, fullFilePath, song.cover, song.song_real_id, 'all', 0, '音域地圖');
        showToast(`🎶 正在播放: ${song.title}`, 'info');
    } else {
        console.warn("Playback function not found in bridge or parent.");
    }
}

function renderMarkers(stamps) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    
    stamps.forEach(stamp => {
        const lat = parseFloat(stamp.latitude);
        const lng = parseFloat(stamp.longitude);
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        // Circle Marker (Dot)
        const marker = L.circleMarker([lat, lng], {
            radius: 8, // Little dot
            fillColor: "#1db954",
            color: "#ffffff",
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        });

        marker.stampId = stamp.id;
        
        marker.on('mouseover', function() {
            this.setStyle({ radius: 10, fillOpacity: 1 });
        });
        marker.on('mouseout', function() {
            if (this.currentID !== selectedId) { 
                this.setStyle({ radius: 8, fillOpacity: 0.8 });
            }
        });

        marker.on('click', (e) => {
             L.DomEvent.stopPropagation(e);
             selectStamp(stamp);
        });
        
        // --- ADDED: Permanent Tooltip for visibility from the outside ---
        marker.bindTooltip(`
            <div style="background:#1db954; color:white; padding:4px 8px; border-radius:12px; font-weight:600; font-size:11px; box-shadow:0 2px 6px rgba(0,0,0,0.3); border:none;">
                ${escapeHtml(stamp.location_name)}
            </div>
        `, { 
            permanent: false, // Show on hover by default, but let's make it permanent if user wants
            direction: 'top',
            offset: [0, -10],
            className: 'marker-tooltip-clean'
        });

        marker.addTo(map);
        markers.push(marker);
    });
}

let selectedId = null;

function selectStamp(stamp) {
    selectedId = stamp.id;
    
    // Update Sidebar
    renderSidebarList([stamp], true); 
    openSidebar();
    
    // Highlight Marker
    markers.forEach(m => {
        if(m.stampId == stamp.id) {
            m.setStyle({ color: '#fff', fillColor: '#ff4757', radius: 12, fillOpacity: 1 });
            m.bringToFront();
        } else {
            m.setStyle({ color: '#fff', fillColor: '#1db954', radius: 8, fillOpacity: 0.8 });
        }
    });
    
    map.flyTo([stamp.latitude, stamp.longitude], 16, { duration: 0.8 });
}

function renderSidebarList(stamps, isSelection = false) {
    const container = document.getElementById('map-sidebar');
    if (!container) return;
    
    let html = `
        <div class="sidebar-header">
            <div style="font-weight:700; color:white; font-size:16px;">
                ${isSelection ? '📍 地點詳情' : '地點列表'}
            </div>
            <button class="close-sidebar-btn" onclick="closeSidebar()">×</button>
        </div>
    `;
    
    html += '<div class="sidebar-list">';
    
    if (stamps.length === 0) {
        if (!isSelection) {
             html += '<div style="padding:20px; text-align:center; color:#777; font-size:14px;">點擊地圖上的圓點查看詳情</div>';
        }
    } else {
        stamps.forEach(stamp => {
            html += `
                <div class="list-item active" onclick="switchToDetail(${stamp.id})">
                    <div style="font-weight:600; color:white; margin-bottom:4px; font-size:15px;">${escapeHtml(stamp.location_name)}</div>
                    <div style="font-size:13px; color:#b3b3b3; margin-bottom:8px; line-height:1.4;">${escapeHtml(stamp.message)}</div>
                    
                    <div style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.05); padding:8px; border-radius:6px;">
                        <img src="${stamp.cover}" style="width:40px; height:40px; border-radius:4px; object-fit:cover;">
                        <div style="overflow:hidden;">
                            <div style="font-size:13px; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(stamp.title)}</div>
                            <div style="font-size:11px; color:#888;">${escapeHtml(stamp.artist)}</div>
                        </div>
                    </div>
                    
                     <div style="margin-top:10px; display:flex; justify-content:flex-end;">
                        <span style="font-size:12px; color:#1db954; font-weight:600;">了解詳情 →</span>
                    </div>
                </div>
            `;
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function openSidebar() {
    const sb = document.getElementById('map-sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    sb.classList.add('open');
    if(toggle) toggle.style.opacity = '0'; // Hide
}

function closeSidebar() {
    const sb = document.getElementById('map-sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    sb.classList.remove('open');
    if(toggle) toggle.style.opacity = '1';
    
    selectedId = null;
    markers.forEach(m => {
        m.setStyle({ color: '#fff', fillColor: '#1db954', radius: 8, fillOpacity: 0.8 });
    });
}

function toggleMapSidebar() {
    const sb = document.getElementById('map-sidebar');
    if(sb.classList.contains('open')) {
        closeSidebar();
    } else {
        openSidebar();
    }
}

function renderMessageBoard(filterTerm = '') {
    const container = document.getElementById('board-grid');
    const paginationContainer = document.getElementById('board-pagination');
    if (!container) return;
    
    // Filter stamps
    let filteredStamps = allStamps;
    if(filterTerm) {
        filteredStamps = allStamps.filter(stamp => {
            return stamp.location_name.toLowerCase().includes(filterTerm) || 
                   stamp.message.toLowerCase().includes(filterTerm) ||
                   (stamp.title && stamp.title.toLowerCase().includes(filterTerm)) ||
                   (stamp.artist && stamp.artist.toLowerCase().includes(filterTerm));
        });
    }

    // Pagination Logic
    const totalItems = filteredStamps.length;
    const totalPages = Math.ceil(totalItems / boardItemsPerPage);
    
    // Adjust current page if out of bounds
    if (boardCurrentPage > totalPages && totalPages > 0) boardCurrentPage = totalPages;
    if (boardCurrentPage < 1) boardCurrentPage = 1;

    const startIdx = (boardCurrentPage - 1) * boardItemsPerPage;
    const displayStamps = filteredStamps.slice(startIdx, startIdx + boardItemsPerPage);

    container.innerHTML = '';
    
    if (displayStamps.length === 0) {
       container.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#b3b3b3; padding:50px;">沒有找到相關留言</div>';
       if(paginationContainer) paginationContainer.innerHTML = '';
       return;
    }

    let html = '';
    displayStamps.forEach(stamp => {
        const date = new Date(stamp.created_at).toLocaleString('zh-TW', { hour12: false, month:'numeric', day:'numeric' });
        const displayImg = stamp.image_path || stamp.cover || 'img/default_location.jpg'; 
        
        html += `
            <div class="board-card" onclick="openStampDetail(${stamp.id})">
                <div class="board-img" style="background-image: url('${displayImg}');"></div>
                <div class="board-content">
                    <div class="board-title">
                        <span>${escapeHtml(stamp.location_name)}</span>
                    </div>
                    <div class="board-date">${date}</div>
                    <div class="board-msg">${escapeHtml(stamp.message)}</div>
                    <div class="board-meta">
                        <div class="board-meta-play" style="display:flex; align-items:center; gap:6px; flex:1; min-width:0; cursor:pointer; color:#1db954;" title="撥放此歌曲">
                            <img src="${stamp.cover}" style="width:24px; height:24px; border-radius:4px; flex-shrink:0;">
                            <div class="song-info">▶ ${escapeHtml(stamp.title)}</div>
                        </div>
                        <div style="font-size:12px; color:#888;">@${escapeHtml(stamp.user_name || 'U')}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;

    // Add click event for play button on cards
    container.querySelectorAll('.board-card').forEach((card, idx) => {
        const stamp = displayStamps[idx];
        const btnPlay = card.querySelector('.board-meta-play');
        if(btnPlay) {
            btnPlay.onclick = (e) => {
                e.stopPropagation();
                playSongOnMap(stamp);
            };
        }
    });

    renderBoardPagination(totalPages, filterTerm);
}

function renderBoardPagination(totalPages, filterTerm) {
    const container = document.getElementById('board-pagination');
    if(!container) return;

    if(totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    
    // First Page
    html += `<button class="page-btn" ${boardCurrentPage === 1 ? 'disabled' : ''} onclick="changeBoardPage(1, '${filterTerm}')" title="最前頁">«</button>`;

    // Prev Button
    html += `<button class="page-btn" ${boardCurrentPage === 1 ? 'disabled' : ''} onclick="changeBoardPage(${boardCurrentPage - 1}, '${filterTerm}')" title="上一頁">‹</button>`;

    // Page Numbers
    for(let i = 1; i <= totalPages; i++) {
        // Only show ±2 pages around current page for better UX if many pages
        if (totalPages > 7) {
            if (i !== 1 && i !== totalPages && (i < boardCurrentPage - 2 || i > boardCurrentPage + 2)) {
                if (i === boardCurrentPage - 3 || i === boardCurrentPage + 3) {
                    html += `<span style="color:#555; padding:0 5px;">...</span>`;
                }
                continue;
            }
        }
        
        const isActive = (i === boardCurrentPage) ? 'active' : '';
        html += `<button class="page-btn ${isActive}" onclick="changeBoardPage(${i}, '${filterTerm}')">${i}</button>`;
    }

    // Next Button
    html += `<button class="page-btn" ${boardCurrentPage === totalPages ? 'disabled' : ''} onclick="changeBoardPage(${boardCurrentPage + 1}, '${filterTerm}')" title="下一頁">›</button>`;

    // Last Page
    html += `<button class="page-btn" ${boardCurrentPage === totalPages ? 'disabled' : ''} onclick="changeBoardPage(${totalPages}, '${filterTerm}')" title="最後頁">»</button>`;

    container.innerHTML = html;
}

function changeBoardPage(newPage, filterTerm) {
    boardCurrentPage = newPage;
    renderMessageBoard(filterTerm);
    // Scroll to top of board view?
    const view = document.getElementById('message-board-view');
    if(view) view.scrollTop = 0;
}

function switchToDetail(id) {
    switchView('board');
    openStampDetail(id);
}

// Modal Logic
function createDetailModal() {
    if(document.getElementById('stamp-detail-modal')) return;
    const div = document.createElement('div');
    div.id = 'stamp-detail-modal';
    div.className = 'modal-overlay';
    div.style.display = 'none';
    div.innerHTML = `
        <div class="modal-box" style="position:relative; width:90%; max-width:900px; height:80vh; display:flex; padding:0; overflow:hidden;">
            <button onclick="closeDetailModal()" style="position:absolute; right:15px; top:15px; z-index:10; background:rgba(0,0,0,0.5); border:none; color:white; font-size:1.5rem; cursor:pointer; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;">&times;</button>
            <div id="detail-content" style="width:100%; height:100%;"></div>
        </div>
    `;
    
    // Background click to close
    div.onclick = function(e) {
        if(e.target === div) closeDetailModal();
    };
    
    document.body.appendChild(div);
}

function openStampDetail(id) {
    const stamp = allStamps.find(s => s.id == id);
    if(!stamp) return;
    
    const isMine = stamp.is_mine;
    const date = new Date(stamp.created_at).toLocaleString('zh-TW', { hour12: false });
    
    const html = `
        <div style="display:flex; height:100%; flex-wrap:wrap; background:#181818;">
            <div style="flex:1.5; min-width:300px; background:#000; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                ${stamp.image_path ? 
                    `<img src="${stamp.image_path}" 
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" 
                        style="max-width:100%; max-height:100%; object-fit:contain;">
                     <div style="display:none; width:100%; height:100%; position:relative;">
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:url('${stamp.cover}') center/cover blur(20px) opacity(0.3);"></div>
                        <img src="${stamp.cover}" style="position:relative; max-width:100%; max-height:100%; object-fit:contain; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                     </div>` : 
                    `<div style="width:100%; height:100%; position:relative; display:flex; align-items:center; justify-content:center;">
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:url('${stamp.cover}') center/cover blur(20px) opacity(0.3);"></div>
                        <img src="${stamp.cover}" style="position:relative; max-width:80%; max-height:80%; object-fit:contain; border-radius:8px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                     </div>`
                }
            </div>
            
            <div style="flex:1; min-width:300px; display:flex; flex-direction:column; border-left:1px solid rgba(255,255,255,0.1); background:#181818; text-align:left;">
                <div style="padding:24px; border-bottom:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:12px; color:#1db954; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; text-align:left;">Music Spot</div>
                    <h2 style="margin:0; color:white; font-size:24px; text-align:left;">${escapeHtml(stamp.location_name)}</h2>
                </div>
                
                <div style="flex:1; padding:24px; overflow-y:auto; display:flex; flex-direction:column; align-items:flex-start;">
                    <div style="display:flex; gap:12px; margin-bottom:24px; width:100%; align-items:flex-start;">
                        <div style="width:40px; height:40px; border-radius:50%; background:#282828; display:flex; align-items:center; justify-content:center; font-weight:600; color:white; flex-shrink:0;">${(stamp.user_name||'U').charAt(0).toUpperCase()}</div>
                        <div style="flex:1;">
                            <div style="font-size:14px; font-weight:600; color:white; margin-bottom:4px; text-align:left;">${escapeHtml(stamp.user_name || '匿名')}</div>
                            <div style="font-size:12px; color:#b3b3b3; margin-bottom:12px; text-align:left;">${date}</div>
                            <div style="color:#e0e0e0; line-height:1.6; font-size:15px; margin-bottom:20px; text-align:left;">${escapeHtml(stamp.message)}</div>
                        </div>
                    </div>
                    
                    <div onclick='playSongOnMap(${JSON.stringify(stamp).replace(/'/g, "&apos;")})' style="border:1px solid rgba(25, 185, 84, 0.4); border-radius:12px; padding:12px; display:flex; align-items:center; gap:12px; background:rgba(29, 185, 84, 0.1); margin: 0 auto 24px auto; width: 80%; cursor:pointer; transition:all 0.2s; box-sizing: border-box;" class="detail-play-card">
                        <div style="position:relative; width:48px; height:48px; flex-shrink:0;">
                            <img src="${stamp.cover}" style="width:100%; height:100%; border-radius:6px; box-shadow:0 4px 8px rgba(0,0,0,0.3); object-fit:cover;">
                            <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:white; border-radius:6px; opacity:0; transition:opacity 0.2s;" class="play-icon-overlay">▶</div>
                        </div>
                        <div style="overflow:hidden; flex:1; text-align: left;">
                            <div style="font-size:11px; color:#1db954; font-weight:700; margin-bottom:1px; text-transform: uppercase;">NOW PLAYING</div>
                            <div style="font-weight:600; color:white; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:14px;">${escapeHtml(stamp.title)}</div>
                            <div style="font-size:12px; color:#b3b3b3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(stamp.artist)}</div>
                        </div>
                    </div>
                    <style>
                        .detail-play-card:hover { background: rgba(29, 185, 84, 0.2); border-color: #1db954; }
                        .detail-play-card:hover .play-icon-overlay { opacity:1; }
                    </style>

                    ${isMine ? `
                    <div style="display:flex; gap:12px; margin-top:auto; padding-top:20px;">
                         <button onclick="openEditModal(${stamp.id})" style="flex:1; padding:12px; border:1px solid rgba(255,255,255,0.15); background:#282828; color:white; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; transition:all 0.2s;">
                            ✏️ 編輯
                         </button>
                         <button onclick="deleteStamp(${stamp.id})" style="flex:1; padding:12px; border:1px solid rgba(248,81,73,0.3); background:rgba(248,81,73,0.05); color:#ff4757; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; transition:all 0.2s;">
                            🗑️ 刪除
                         </button>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('detail-content').innerHTML = html;
    document.getElementById('stamp-detail-modal').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('stamp-detail-modal').style.display = 'none';
}

function editStamp(id, oldMsg) {
    const newMsg = prompt("修改留言內容：", oldMsg);
    if (newMsg !== null && newMsg.trim() !== "") {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('message', newMsg);
        formData.append('action', 'edit'); 
        submitStampUpdate(formData);
    }
}

async function deleteStamp(id) {
    const ok = await showConfirm("確定要刪除嗎？", "刪除後此打卡點將無法復原。");
    if(ok) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'delete');
        submitStampUpdate(formData);
    }
}

async function submitStampUpdate(formData) {
    try {
        const res = await fetch('api_manage_stamp.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            closeDetailModal();
            fetchStamps(); // Reload
        } else {
            alert("操作失敗: " + data.message);
        }
    } catch(err) {
        alert("網路錯誤");
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// --- Player Bar Observer for Layout Adjustment ---
function initPlayerBarObserver() {
    if (!window.parent || !window.parent.document) return;
    
    const player = window.parent.document.getElementById('player-bar');
    if (!player) return; 
    
    // Initial check
    checkPlayerVisibility();
    
    // Observer
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'style') {
                checkPlayerVisibility();
            }
        });
    });
    
    observer.observe(player, { attributes: true });
    
    function checkPlayerVisibility() {
        const computed = window.parent.getComputedStyle(player);
        const isVisible = computed.display !== 'none';
        adjustMapControls(isVisible);
    }
}

function adjustMapControls(isPlayerVisible) {
    const controls = document.querySelector('.map-controls-group');
    if (!controls) return;
    
    if (isPlayerVisible) {
        controls.style.transition = 'bottom 0.3s ease';
        controls.style.bottom = '120px'; 
    } else {
        controls.style.transition = 'bottom 0.3s ease';
        controls.style.bottom = '30px'; 
    }
}
