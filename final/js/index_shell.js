// Mode 2: App Shell Logic

const audio = document.getElementById('audio-player');
const playerBar = document.getElementById('player-bar');
const playBtn = document.getElementById('main-play-btn');
const modeBtn = document.getElementById('mode-btn');
const likeBtn = document.getElementById('like-btn'); 
const queueInfo = document.getElementById('queue-info');

// --- Navigation Logic ---
function navigate(url) {
    document.getElementById('content-frame').src = url;
    
    // Update Active State
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    if (url.includes('index.php')) document.getElementById('nav-home').classList.add('active');
    else if (url.includes('search')) document.getElementById('nav-search').classList.add('active');
    else if (url.includes('playlist')) document.getElementById('nav-library').classList.add('active');
    else if (url.includes('creator')) document.getElementById('nav-creator').classList.add('active');
    else if (url.includes('profile')) document.getElementById('nav-profile').classList.add('active');
}

function highlightNav(id) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
}

function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    if (menu) {
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
        } else {
            menu.style.display = 'none';
        }
    }
}

// Close menus on click outside
window.addEventListener('click', function(e) {
    const menu = document.getElementById('user-menu');
    const avatar = document.querySelector('[onclick="toggleUserMenu()"]');
    if (menu && avatar && !menu.contains(e.target) && !avatar.contains(e.target)) {
        menu.style.display = 'none';
    }
});
// ------------------------

// State
let queue = [];
let currentIndex = -1;
let isPlaying = false;
let playCounted = false; // Flag to track if play count has been sent for this session

// Modes: shuffle (bool), loop (sequence 🔁 / one 🔂)
let isShuffle = false;
let loopMode = 'sequence'; // 'sequence' or 'one'
let queueType = 'all'; // Track what we loaded
let queueTypeId = 0;

// Load Queue from API
function loadQueue(type, id, startId = 0, forceShuffle = false) {
    queueType = type;
    queueTypeId = id;
    if (forceShuffle !== undefined) isShuffle = forceShuffle;
    
    // 如果是開啟隨機，後端回傳亂序清單；否則回傳正序
    // 傳遞 current_id 讓後端將目前歌曲固定在第一首
    let url = `api_get_queue.php?type=${type}&id=${id}&shuffle=${isShuffle ? 1 : 0}`;
    if (startId > 0) url += `&current_id=${startId}`;
    else if (currentIndex >= 0 && queue[currentIndex]) url += `&current_id=${queue[currentIndex].id}`;
    
    console.log("Loading Queue:", url);

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                alert("播放列表為空");
                return;
            }
            
            
            // Silent Update Check: Is currently playing song in the new queue?
            const currentPlayingId = (currentIndex >= 0 && queue[currentIndex]) ? queue[currentIndex].id : 0;
            
            queue = data; // Update Queue Data
            
            updateShuffleBtn();
            updateQueueInfo();
            renderQueueList();
            
            // Silent Update Check: Is currently playing song in the new queue?
            // Only strictly preserve playback if we are NOT trying to switch to a new song (startId == current)
            if (currentPlayingId > 0 && (startId == 0 || startId == currentPlayingId)) {
                const newIndex = queue.findIndex(s => s.id == currentPlayingId);
                if (newIndex !== -1) {
                    currentIndex = newIndex;
                    scrollToActiveQueueItem();
                    // Don't call loadSong, just let it play
                    console.log("Silent Update: Playing continues at index", newIndex);
                    return;
                }
            }
            
            // Find start index (Normal Load)
            if (startId > 0) {
                currentIndex = queue.findIndex(s => s.id == startId);
                if (currentIndex === -1) currentIndex = 0;
            } else {
                currentIndex = 0;
            }
            
            loadSong(currentIndex);
        })
        .catch(err => console.error("Queue Load Error:", err));
}

// Core Play Function
function loadSong(index) {
    if (index < 0 || index >= queue.length) return;
    
    const song = queue[index];
    currentIndex = index;
    playCounted = false; // Reset flag for new song
    
    // Update Player Bar Info
    document.getElementById('player-title').innerText = song.title;
    document.getElementById('player-artist').innerText = song.artist;
    document.getElementById('player-cover').src = song.cover;
    
    // Update Now Playing Overlay Info
    const bigCover = document.getElementById('np-big-cover');
    if (bigCover) bigCover.src = song.cover;
    
    // Highlight in Queue List
    renderQueueList();
    scrollToActiveQueueItem();

    audio.src = song.file_path;
    playerBar.style.display = 'flex';
    
    // Check Like Status (Non-blocking)
    fetch(`api_like.php?action=check&song_id=${song.id}`)
        .then(res => {
            if (!res.ok) throw new Error('API Error');
            return res.json();
        })
        .then(data => {
            updateLikeIcon(data.liked);
        })
        .catch(err => {
            updateLikeIcon(false);
        });
    
    // Auto Play
    const playPromise = audio.play();
    if (playPromise !== undefined) {
        playPromise.then(() => {
            isPlaying = true;
            playBtn.innerText = '❚❚';
        }).catch(e => {
            console.error("Auto-play prevented:", e);
        });
    }
}

// Like Button Functions
function toggleLike() {
    if (currentIndex < 0 || currentIndex >= queue.length) return;
    
    const songId = queue[currentIndex].id;
    const isLiked = likeBtn.classList.contains('liked');
    
    // Use POST method as API expects
    const formData = new FormData();
    formData.append('song_id', songId);
    
    fetch('api_like.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.hasOwnProperty('liked')) {
                updateLikeIcon(data.liked);
            }
        })
        .catch(err => console.error('Like toggle error:', err));
}

function updateLikeIcon(liked) {
    if (liked) {
        likeBtn.classList.add('liked');
        likeBtn.style.color = '#ff4757';
    } else {
        likeBtn.classList.remove('liked');
        likeBtn.style.color = '#999';
    }
}

function togglePlay() {
    if (audio.paused) {
        audio.play();
        playBtn.innerText = '❚❚';
        isPlaying = true;
    } else {
        audio.pause();
        playBtn.innerText = '▶';
        isPlaying = false;
    }
}

// Next/Prev Logic (Linear, because specific shuffle handled by Queue structure)
function nextSong() {
    if (queue.length === 0) return;
    
    let nextIdx = currentIndex + 1;
    if (nextIdx >= queue.length) {
        // Loop back to start (default behavior for list loop)
        nextIdx = 0; 
    }
    loadSong(nextIdx);
}

function prevSong() {
    if (queue.length === 0) return;
    
    let prevIdx = currentIndex - 1;
    if (prevIdx < 0) {
        prevIdx = queue.length - 1;
    }
    loadSong(prevIdx);
}

// Auto Next on Ended
audio.addEventListener('ended', () => {
     if (loopMode === 'one') {
         audio.currentTime = 0;
         audio.play();
     } else {
         nextSong();
     }
});

// --- New Control Logic ---

function toggleShuffle() {
    isShuffle = !isShuffle;
    updateShuffleBtn();
    
    // Reload Queue with new shuffle state
    // Try to keep current song playing
    const currentSongId = (currentIndex >= 0 && queue[currentIndex]) ? queue[currentIndex].id : 0;
    loadQueue(queueType, queueTypeId, currentSongId, isShuffle);
}

function updateShuffleBtn() {
    const btn = document.getElementById('shuffle-btn');
    if (isShuffle) {
        btn.style.opacity = '1';
        btn.style.color = '#ff4757'; // Active Color
    } else {
        btn.style.opacity = '0.5';
        btn.style.color = '#e1e1e1';
    }
}

function toggleLoop() {
    if (loopMode === 'sequence') {
        loopMode = 'one';
    } else {
        loopMode = 'sequence';
    }
    updateLoopBtn();
}

function updateLoopBtn() {
    const btn = document.getElementById('loop-btn');
    if (loopMode === 'sequence') {
        btn.innerText = '🔁';
        btn.title = "列表循環";
        btn.style.color = '#ff4757'; // Active for List Loop means 'Looping' (default is usually Loop All)
    } else {
        btn.innerText = '🔂';
        btn.title = "單曲循環";
        btn.style.color = '#ff4757'; 
    }
}

function updateQueueInfo() {
    let displayText = `Q: ${queue.length}`;
    
    // Add context information
    if (queueType === 'playlist' && queueTypeId > 0) {
        // Try to get playlist name from the first song's metadata or just show "播放清單"
        displayText = `🎵 清單 (${queue.length})`;
    } else if (queueType === 'all') {
        displayText = `🌐 所有歌曲 (${queue.length})`;
    }
    
    queueInfo.innerText = displayText;
    queueInfo.title = queueType === 'playlist' ? 
        '當前播放清單內循環' : 
        '所有歌曲';
    
    // Also update Now Playing overlay display
    updatePlaylistNameDisplay();
}

function updatePlaylistNameDisplay() {
    const playlistNameEl = document.getElementById('np-playlist-name');
    if (!playlistNameEl) return;
    
    if (queueType === 'playlist' && window.currentPlaylistName) {
        playlistNameEl.innerHTML = `📀 播放清單：<strong>${window.currentPlaylistName}</strong>`;
        playlistNameEl.style.display = 'block';
    } else if (queueType === 'all') {
        playlistNameEl.innerHTML = `🌐 播放範圍：<strong>所有歌曲</strong>`;
        playlistNameEl.style.display = 'block';
    } else {
        playlistNameEl.style.display = 'none';
    }
}

// --- Now Playing Overlay Logic ---

function toggleNowPlaying() {
    const overlay = document.getElementById('now-playing-overlay');
    if (overlay.style.display === 'none') {
        overlay.style.display = 'flex';
        renderQueueList();
    } else {
        overlay.style.display = 'none';
    }
}

function renderQueueList() {
    const listContainer = document.getElementById('np-queue-list');
    const countSpan = document.getElementById('np-queue-count');
    if (!listContainer) return;
    
    if (countSpan) countSpan.innerText = `(${queue.length} 首歌曲)`;
    
    let html = '';
    queue.forEach((song, idx) => {
        const isActive = (idx === currentIndex) ? 'active' : '';
        html += `
            <div class="np-queue-item ${isActive}" onclick="loadSong(${idx})">
                <img src="${song.cover}" class="np-q-img" loading="lazy">
                <div class="np-q-info">
                    <div class="np-q-title">${song.title}</div>
                    <div class="np-q-artist">${song.artist}</div>
                </div>
                ${isActive ? '<span style="color: #ff4757;">▶</span>' : ''}
            </div>
        `;
    });
    listContainer.innerHTML = html;
}

function scrollToActiveQueueItem() {
    // Optional: Scroll to active item in overlay list
    const activeItem = document.querySelector('.np-queue-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Volume & Mute Logic
const volumeSlider = document.getElementById('volume-slider');
const volumeBtn = document.getElementById('volume-btn');
const volumeValue = document.getElementById('volume-value');
let lastVolume = 1;

volumeSlider.addEventListener('input', (e) => {
    const val = e.target.value;
    audio.volume = val;
    updateVolumeIcon(val);
});

function toggleMute() {
    if (audio.muted || audio.volume === 0) {
        audio.muted = false;
        audio.volume = lastVolume || 0.5;
        volumeSlider.value = audio.volume;
    } else {
        lastVolume = audio.volume;
        audio.muted = true;
        volumeSlider.value = 0;
    }
    updateVolumeIcon(audio.muted ? 0 : audio.volume);
}

function updateVolumeIcon(vol) {
    if (vol == 0) {
        volumeBtn.innerText = '🔇';
    } else if (vol < 0.5) {
        volumeBtn.innerText = '🔉';
    } else {
        volumeBtn.innerText = '🔊';
    }
    
    // Update Percentage Text
    if (volumeValue) {
        // Toggle display block if using style.display logic, but we used CSS opacity/width.
        // Just update text.
        volumeValue.innerText = Math.round(vol * 100) + '%';
        volumeValue.style.display = 'block'; // Make sure it's visible in DOM flow if originally hidden
    }
}

// Progress Bar
const progressSlider = document.getElementById('progress-slider');
const progressFill = document.getElementById('progress-fill');
const currTimeDisplay = document.getElementById('curr-time');
const totalTimeDisplay = document.getElementById('total-time');

let isDragging = false;

// Update Slider Range when metadata loaded
audio.addEventListener('loadedmetadata', () => {
    if (isFinite(audio.duration)) {
        progressSlider.max = audio.duration;
        totalTimeDisplay.innerText = formatTime(audio.duration);
    }
});

// Update Slider as song plays
audio.addEventListener('timeupdate', () => {
    if (!isDragging) {
        progressSlider.value = audio.currentTime;
        currTimeDisplay.innerText = formatTime(audio.currentTime);
        updateSliderVisuals();
    }
    
    // Check Play Count (More than 60 seconds)
    if (typeof playCounted !== 'undefined' && !playCounted && audio.currentTime > 60 && currentIndex !== -1) {
        const songId = queue[currentIndex].id;
        fetch(`play_count.php?id=${songId}`);
        playCounted = true;
        console.log(`Play counted for Song ID: ${songId}`);
    }
});

// Slider Interactions
progressSlider.addEventListener('mousedown', () => isDragging = true);
progressSlider.addEventListener('touchstart', () => isDragging = true);

progressSlider.addEventListener('input', function() {
    isDragging = true;
    currTimeDisplay.innerText = formatTime(this.value);
    updateSliderVisuals();
});

progressSlider.addEventListener('change', function() {
    isDragging = false;
    audio.currentTime = this.value;
    updateSliderVisuals();
});

progressSlider.addEventListener('mouseup', () => {
    if (isDragging) {
        isDragging = false;
        audio.currentTime = progressSlider.value;
        updateSliderVisuals();
    }
});

function updateSliderVisuals() {
    const min = parseFloat(progressSlider.min) || 0;
    const max = parseFloat(progressSlider.max) || 100;
    const val = parseFloat(progressSlider.value) || 0;
    
    let percentage = 0;
    if (max > 0) {
        percentage = ((val - min) / (max - min)) * 100;
    }

    if (progressFill) {
        progressFill.style.width = `${percentage}%`;
    }
}

// Init
updateSliderVisuals();

function formatTime(seconds) {
    if (!seconds || isNaN(seconds)) return "0:00";
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
}

// Global Interaction Fix (MouseUp outside slider)
window.addEventListener('mouseup', () => {
    if (typeof isDragging !== 'undefined' && isDragging) {
        isDragging = false;
        audio.currentTime = progressSlider.value;
        updateSliderVisuals();
    }
});
window.addEventListener('touchend', () => {
    if (typeof isDragging !== 'undefined' && isDragging) {
        isDragging = false;
        audio.currentTime = progressSlider.value;
        updateSliderVisuals();
    }
});

// External Play Interface (for iframes)
window.playSong = function(title, artist, src, cover, id, type, typeId, typeName) {
    console.log("PlaySong Request:", title, id, "Shuffle:", isShuffle, "Type:", type, "TypeId:", typeId, "TypeName:", typeName);
    
    // Reset Play Count tracking for new song interaction
    playCounted = false;

    // If type and typeId are provided, use them; otherwise use current context
    const targetType = type || queueType || 'all';
    const targetTypeId = typeId !== undefined ? typeId : queueTypeId;

    // Set Playlist Name if provided
    if (typeName) {
        window.currentPlaylistName = typeName;
    } else if (targetType === 'all') {
        window.currentPlaylistName = '所有歌曲';
    }

    if (isShuffle) {
        console.log("Shuffle Play: Re-anchoring queue to", id, "with type", targetType, targetTypeId);
        loadQueue(targetType, targetTypeId, id, true);
    } else {
        const idx = queue.findIndex(s => s.id == id);
        if (idx !== -1 && targetType === queueType && targetTypeId === queueTypeId) {
            // Already in current queue context, just play
            loadSong(idx);
        } else {
            console.log("Song not in current queue or context changed, reloading with type", targetType, targetTypeId);
            loadQueue(targetType, targetTypeId, id, false);
        }
    }
};

// Function to set current viewing context (called by child pages like playlist_view.php)
window.setPlaylistContext = function(playlistId, playlistName) {
    // Don't change if currently playing from this playlist
    if (queueType === 'playlist' && queueTypeId == playlistId && queue.length > 0 && isPlaying) {
        console.log("Already playing from this playlist, keeping current queue");
        return;
    }
    
    // Set context for future playback
    queueType = 'playlist';
    queueTypeId = playlistId;
    window.currentPlaylistName = playlistName; // Store playlist name globally
    
    console.log(`Playlist context set: ${playlistName} (ID: ${playlistId})`);
    console.log("Next song clicked from this page will play from this playlist");
    
    // Update display if overlay is open
    updateQueueInfo();
    updatePlaylistNameDisplay();
};
