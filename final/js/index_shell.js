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

// Modes: 'sequence' (List Loop 🔁), 'one' (Single Loop 🔂), 'shuffle' (List Random 🔀)
let mode = 'sequence'; 

// Load Queue from API
function loadQueue(type, id, startId = 0, forceShuffle = false) {
    let url = `api_get_queue.php?type=${type}&id=${id}`;
    if (forceShuffle) url += '&shuffle=1';
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                alert("播放列表為空");
                return;
            }
            
            queue = data;
            
            if (forceShuffle) {
                mode = 'sequence';
                updateModeIcon();
            }
            
            queueInfo.innerText = `Queue: ${queue.length}`;
            
            // Find start index
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
    
    document.getElementById('player-title').innerText = song.title;
    document.getElementById('player-artist').innerText = song.artist;
    document.getElementById('player-cover').src = song.cover;
    
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
            console.warn("Like Check Failed (likely guest):", err);
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

function toggleLike() {
    if (currentIndex === -1) return;
    const song = queue[currentIndex];
    
    fetch(`api_like.php?song_id=${song.id}`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            updateLikeIcon(data.liked);
        });
}

function updateLikeIcon(isLiked) {
    if (isLiked) {
        likeBtn.innerText = '❤️';
        likeBtn.style.opacity = '1';
    } else {
        likeBtn.innerText = '♡';
        likeBtn.style.opacity = '0.7';
    }
}

// Legacy support/Single play fallback
function playSong(title, artist, src, cover, id) {
    // Check if this song is in current queue
    const idx = queue.findIndex(s => s.id == id);
    if (idx !== -1) {
        loadSong(idx);
    } else {
        // Not in queue, just add it temporarily or create single queue?
        loadQueue('all', 0, id);
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

// Next/Prev Logic
function nextSong() {
    if (queue.length === 0) return;
    
    if (mode === 'shuffle') {
        // Pick random index
        let nextIdx = Math.floor(Math.random() * queue.length);
        loadSong(nextIdx);
    } else {
        // Sequence or One
        let nextIdx = currentIndex + 1;
        if (nextIdx >= queue.length) {
            // Loop back to start
            nextIdx = 0; 
        }
        loadSong(nextIdx);
    }
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
     if (mode === 'one') {
         audio.currentTime = 0;
         audio.play();
     } else {
         nextSong();
     }
});

// Toggle Mode
function toggleMode() {
    if (mode === 'sequence') {
        mode = 'one';
    } else if (mode === 'one') {
        mode = 'shuffle';
    } else {
        mode = 'sequence';
    }
    updateModeIcon();
}

function updateModeIcon() {
    if (mode === 'sequence') {
        modeBtn.innerText = '🔁';
        modeBtn.title = "列表循環";
    } else if (mode === 'one') {
        modeBtn.innerText = '🔂';
        modeBtn.title = "單曲循環";
    } else {
        modeBtn.innerText = '🔀';
        modeBtn.title = "列表隨機";
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
const progressFill = document.getElementById('progress-fill');
const currTimeParams = document.getElementById('curr-time');
const totalTimeParams = document.getElementById('total-time');

audio.addEventListener('timeupdate', () => {
    const progress = (audio.currentTime / audio.duration) * 100;
    progressFill.style.width = `${progress}%`;
    currTimeParams.innerText = formatTime(audio.currentTime);
    const duration = isNaN(audio.duration) ? 0 : audio.duration;
    totalTimeParams.innerText = formatTime(duration);
    
    // Check Play Count (More than 60 seconds)
    if (!playCounted && audio.currentTime > 60 && currentIndex !== -1) {
        const songId = queue[currentIndex].id;
        fetch(`play_count.php?id=${songId}`);
        playCounted = true;
        console.log(`Play counted for Song ID: ${songId}`);
    }
});

function seek(e) {
    const progressBar = document.getElementById('progress-bar');
    const percent = e.offsetX / progressBar.offsetWidth;
    audio.currentTime = percent * audio.duration;
}

function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
}
