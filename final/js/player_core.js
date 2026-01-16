// Audio Player Core Logic

window.audio = document.getElementById('audio-player');
window.playerBar = document.getElementById('player-bar');
window.playBtn = document.getElementById('main-play-btn');
window.likeBtn = document.getElementById('like-btn'); 
window.queueInfo = document.getElementById('queue-info');

window.queue = [];
window.currentIndex = -1;
window.isPlaying = false;
window.playCounted = false; 
window.isShuffle = false;
window.loopMode = 'sequence'; 
window.queueType = 'all'; 
window.queueTypeId = 0;

function loadQueue(type, id, startId = 0, forceShuffle = false) {
    queueType = type;
    queueTypeId = id;
    if (forceShuffle !== undefined) isShuffle = forceShuffle;
    
    let url = `api_get_queue.php?type=${type}&id=${id}&shuffle=${window.isShuffle ? 1 : 0}`;
    if (startId > 0) url += `&current_id=${startId}`;
    else if (window.currentIndex >= 0 && window.queue[window.currentIndex]) url += `&current_id=${window.queue[window.currentIndex].id}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) return;
            
            const currentPlayingId = (window.currentIndex >= 0 && window.queue[window.currentIndex]) ? window.queue[window.currentIndex].id : 0;
            window.queue = data; 
            
            if (window.updateShuffleBtn) updateShuffleBtn();
            if (window.updateQueueInfo) updateQueueInfo();
            if (window.renderQueueList) renderQueueList();
            
            if (currentPlayingId > 0 && (startId == 0 || startId == currentPlayingId)) {
                const newIndex = window.queue.findIndex(s => s.id == currentPlayingId);
                if (newIndex !== -1) {
                    window.currentIndex = newIndex;
                    if (window.scrollToActiveQueueItem) window.scrollToActiveQueueItem();
                    return;
                }
            }
            
            window.currentIndex = startId > 0 ? window.queue.findIndex(s => s.id == startId) : 0;
            if (window.currentIndex === -1) window.currentIndex = 0;
            loadSong(window.currentIndex);
        })
        .catch(err => console.error("Queue Load Error:", err));
}

function loadSong(index) {
    if (index < 0 || index >= window.queue.length) return;
    
    const song = window.queue[index];
    window.currentIndex = index;
    window.playCounted = false; 
    
    document.getElementById('player-title').innerText = song.title;
    document.getElementById('player-artist').innerText = song.artist;
    document.getElementById('player-cover').src = song.cover;
    
    const bigCover = document.getElementById('np-big-cover');
    if (bigCover) bigCover.src = song.cover;
    
    if (window.renderQueueList) renderQueueList();
    if (window.scrollToActiveQueueItem) scrollToActiveQueueItem();

    window.audio.src = song.file_path;
    window.playerBar.style.display = 'flex';
    
    fetch(`api_like.php?action=check&song_id=${song.id}`)
        .then(res => res.json())
        .then(data => updateLikeIcon(data.liked))
        .catch(() => updateLikeIcon(false));
    
    window.audio.play().then(() => {
        window.isPlaying = true;
        window.playBtn.innerText = '❚❚';
    }).catch(e => console.error("Auto-play blocked:", e));
}

function toggleLike() {
    if (window.currentIndex < 0 || window.currentIndex >= window.queue.length) return;
    const songId = window.queue[window.currentIndex].id;
    const formData = new FormData();
    formData.append('song_id', songId);
    
    fetch('api_like.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.hasOwnProperty('liked')) {
                updateLikeIcon(data.liked);
                if (window.showToast) window.showToast(data.liked ? "已加入最愛" : "已從最愛移除", "success");
            }
        });
}

function updateLikeIcon(liked) {
    if (liked) {
        window.likeBtn.classList.add('liked');
        window.likeBtn.style.color = '#ff4757';
    } else {
        window.likeBtn.classList.remove('liked');
        window.likeBtn.style.color = '#999';
    }
}

function togglePlay() {
    if (window.audio.paused) {
        window.audio.play();
        window.playBtn.innerText = '❚❚';
        window.isPlaying = true;
    } else {
        window.audio.pause();
        window.playBtn.innerText = '▶';
        window.isPlaying = false;
    }
}

function nextSong() {
    if (window.queue.length === 0) return;
    loadSong((window.currentIndex + 1) % window.queue.length);
}

function prevSong() {
    if (window.queue.length === 0) return;
    loadSong(window.currentIndex <= 0 ? window.queue.length - 1 : window.currentIndex - 1);
}

window.audio.addEventListener('ended', () => {
     if (window.loopMode === 'one') {
         window.audio.currentTime = 0;
         window.audio.play();
     } else {
         nextSong();
     }
});

function toggleShuffle() {
    window.isShuffle = !window.isShuffle;
    if (window.updateShuffleBtn) window.updateShuffleBtn();
    const currentSongId = (window.currentIndex >= 0 && window.queue[window.currentIndex]) ? window.queue[window.currentIndex].id : 0;
    loadQueue(window.queueType, window.queueTypeId, currentSongId, window.isShuffle);
}

function toggleLoop() {
    window.loopMode = (window.loopMode === 'sequence') ? 'one' : 'sequence';
    if (window.updateLoopBtn) window.updateLoopBtn();
}

// Global Interfaces
window.playSong = function(title, artist, src, cover, id, type, typeId, typeName) {
    playCounted = false;
    const targetType = type || queueType || 'all';
    const targetTypeId = typeId !== undefined ? typeId : queueTypeId;

    if (typeName) window.currentPlaylistName = typeName;
    else if (targetType === 'all') window.currentPlaylistName = '所有歌曲';

    if (isShuffle) {
        loadQueue(targetType, targetTypeId, id, true);
    } else {
        const idx = queue.findIndex(s => s.id == id);
        if (idx !== -1 && targetType === queueType && targetTypeId === queueTypeId) {
            loadSong(idx);
        } else {
            loadQueue(targetType, targetTypeId, id, false);
        }
    }
};

window.setPlaylistContext = function(playlistId, playlistName) {
    if (queueType === 'playlist' && queueTypeId == playlistId && queue.length > 0 && isPlaying) return;
    queueType = 'playlist';
    queueTypeId = playlistId;
    window.currentPlaylistName = playlistName;
    if (window.updateQueueInfo) updateQueueInfo();
    if (window.updatePlaylistNameDisplay) updatePlaylistNameDisplay();
};

function formatTime(seconds) {
    if (!seconds || isNaN(seconds)) return "0:00";
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
}
