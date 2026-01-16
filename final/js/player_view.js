// Player UI & View Updates

function updateShuffleBtn() {
    const btn = document.getElementById('shuffle-btn');
    if (!btn) return;
    if (window.isShuffle) {
        btn.style.opacity = '1';
        btn.style.color = '#ff4757';
    } else {
        btn.style.opacity = '0.5';
        btn.style.color = '#e1e1e1';
    }
}

function updateLoopBtn() {
    const btn = document.getElementById('loop-btn');
    if (!btn) return;
    if (window.loopMode === 'sequence') {
        btn.innerText = '🔁';
        btn.title = "列表循環";
        btn.style.color = '#ff4757';
    } else {
        btn.innerText = '🔂';
        btn.title = "單曲循環";
        btn.style.color = '#ff4757'; 
    }
}

function updateQueueInfo() {
    let displayText = `Q: ${window.queue.length}`;
    if (window.queueType === 'playlist' && window.queueTypeId > 0) displayText = `🎵 清單 (${window.queue.length})`;
    else if (window.queueType === 'all') displayText = `🌐 所有歌曲 (${window.queue.length})`;
    
    if (window.queueInfo) {
        window.queueInfo.innerText = displayText;
        window.queueInfo.title = window.queueType === 'playlist' ? '當前播放清單內循環' : '所有歌曲';
    }
    updatePlaylistNameDisplay();
}

function updatePlaylistNameDisplay() {
    const playlistNameEl = document.getElementById('np-playlist-name');
    if (!playlistNameEl) return;
    
    if (window.queueType === 'playlist' && window.currentPlaylistName) {
        playlistNameEl.innerHTML = `📀 播放清單：<strong>${window.currentPlaylistName}</strong>`;
        playlistNameEl.style.display = 'block';
    } else if (window.queueType === 'all') {
        playlistNameEl.innerHTML = `🌐 播放範圍：<strong>所有歌曲</strong>`;
        playlistNameEl.style.display = 'block';
    } else {
        playlistNameEl.style.display = 'none';
    }
}

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
    
    if (countSpan) countSpan.innerText = `(${window.queue.length} 首歌曲)`;
    
    let html = '';
    window.queue.forEach((song, idx) => {
        const isActive = (idx === window.currentIndex) ? 'active' : '';
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
    const activeItem = document.querySelector('.np-queue-item.active');
    if (activeItem) activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Volume & Progress Bar Logic
const volumeSlider = document.getElementById('volume-slider');
const volumeBtn = document.getElementById('volume-btn');
const volumeValue = document.getElementById('volume-value');
let lastVolume = 1;

if (volumeSlider) {
    volumeSlider.addEventListener('input', (e) => {
        const val = e.target.value;
        window.audio.volume = val;
        updateVolumeIcon(val);
    });
}

function toggleMute() {
    if (window.audio.muted || window.audio.volume === 0) {
        window.audio.muted = false;
        window.audio.volume = lastVolume || 0.5;
        volumeSlider.value = window.audio.volume;
    } else {
        lastVolume = window.audio.volume;
        window.audio.muted = true;
        volumeSlider.value = 0;
    }
    updateVolumeIcon(window.audio.muted ? 0 : window.audio.volume);
}

function updateVolumeIcon(vol) {
    if (vol == 0) volumeBtn.innerText = '🔇';
    else if (vol < 0.5) volumeBtn.innerText = '🔉';
    else volumeBtn.innerText = '🔊';
    
    if (volumeValue) {
        volumeValue.innerText = Math.round(vol * 100) + '%';
        volumeValue.style.display = 'block';
    }
}

const progressSlider = document.getElementById('progress-slider');
const progressFill = document.getElementById('progress-fill');
const currTimeDisplay = document.getElementById('curr-time');
const totalTimeDisplay = document.getElementById('total-time');
let isDragging = false;

if (window.audio) {
    window.audio.addEventListener('loadedmetadata', () => {
        if (isFinite(window.audio.duration)) {
            progressSlider.max = window.audio.duration;
            totalTimeDisplay.innerText = formatTime(window.audio.duration);
        }
    });

    window.audio.addEventListener('timeupdate', () => {
        if (!isDragging) {
            progressSlider.value = window.audio.currentTime;
            currTimeDisplay.innerText = formatTime(window.audio.currentTime);
            updateSliderVisuals();
        }
        if (!window.playCounted && window.audio.currentTime > 60 && window.currentIndex !== -1) {
            fetch(`play_count.php?id=${window.queue[window.currentIndex].id}`);
            window.playCounted = true;
        }
    });
}

if (progressSlider) {
    progressSlider.addEventListener('mousedown', () => isDragging = true);
    progressSlider.addEventListener('touchstart', () => isDragging = true);
    progressSlider.addEventListener('input', function() {
        isDragging = true;
        currTimeDisplay.innerText = formatTime(this.value);
        updateSliderVisuals();
    });
    progressSlider.addEventListener('change', function() {
        isDragging = false;
        window.audio.currentTime = this.value;
        updateSliderVisuals();
    });
    progressSlider.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            window.audio.currentTime = progressSlider.value;
            updateSliderVisuals();
        }
    });
}

function updateSliderVisuals() {
    const min = parseFloat(progressSlider.min) || 0;
    const max = parseFloat(progressSlider.max) || 100;
    const val = parseFloat(progressSlider.value) || 0;
    let percentage = max > 0 ? ((val - min) / (max - min)) * 100 : 0;
    if (progressFill) progressFill.style.width = `${percentage}%`;
}

window.addEventListener('mouseup', () => {
    if (typeof isDragging !== 'undefined' && isDragging) {
        isDragging = false;
        window.audio.currentTime = progressSlider.value;
        updateSliderVisuals();
    }
});
window.addEventListener('touchend', () => {
    if (typeof isDragging !== 'undefined' && isDragging) {
        isDragging = false;
        window.audio.currentTime = progressSlider.value;
        updateSliderVisuals();
    }
});

// Init
updateSliderVisuals();
updateShuffleBtn();
updateLoopBtn();
