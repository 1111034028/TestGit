class MobileController {
    constructor(token) {
        this.token = token;
        this.currentState = { isPlaying: false, title: '', artist: '', cover: '' };
        this.playBtn = document.getElementById('play-btn');
        this.silentAudio = document.getElementById('silent-audio');
        this.volTimeout = null;

        this.init();
    }

    init() {
        // Unlock audio context on ANY interaction (iOS Requirement)
        document.body.addEventListener('click', () => {
            if (this.silentAudio.paused) {
                this.silentAudio.play().then(() => {
                    console.log("Audio Context Unlocked");
                }).catch(e => console.log("Audio unlock failed (yet):", e));
            }
        }, { once: true });

        // Volume Slider listener
        document.getElementById('vol-slider').addEventListener('input', (e) => {
            clearTimeout(this.volTimeout);
            this.volTimeout = setTimeout(() => {
                this.sendCommand('volume', e.target.value);
            }, 100);
        });

        // Initial connection
        this.sendCommand('connect');

        // Start polling
        setInterval(() => this.pollState(), 1000);
    }

    sendCommand(cmd, payload = '') {
        fetch('api_remote_send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `token=${this.token}&command=${cmd}&payload=${payload}`
        }).catch(console.error);
    }

    togglePlay() {
        // Force play silent audio to keep session active
        this.silentAudio.play().catch(e => console.log("Silent audio blocked:", e));
        this.sendCommand('toggle_play');
    }

    pollState() {
        fetch(`api_remote_sync.php?action=pull&token=${this.token}`)
            .then(res => res.json())
            .then(data => {
                this.updateUI(data);
            })
            .catch(console.error);
    }

    updateUI(data) {
        // Update Text
        if (data.title && data.title !== this.currentState.title) {
            document.getElementById('track-title').innerText = data.title;
            this.currentState.title = data.title;
        }
        if (data.artist && data.artist !== this.currentState.artist) {
            document.getElementById('track-artist').innerText = data.artist;
            this.currentState.artist = data.artist;
        }
        
        // Update Cover
        if (data.cover && data.cover !== this.currentState.cover) {
            document.getElementById('album-art').src = data.cover;
            document.getElementById('bg-art').style.backgroundImage = `url('${data.cover}')`;
            this.currentState.cover = data.cover;
        }
        
        // Always try to update Media Session Metadata (to ensure it sticks)
        if (data.title) this.updateMediaSession(data);

        // Update Play Button
        const isPlaying = data.isPlaying;
        if (isPlaying !== this.currentState.isPlaying) {
            this.playBtn.innerText = isPlaying ? '❚❚' : '▶';
            this.currentState.isPlaying = isPlaying;
            
            // Sync system audio state
            if (isPlaying) {
                this.silentAudio.play().catch(()=>{});
                if ('mediaSession' in navigator) navigator.mediaSession.playbackState = 'playing';
            } else {
                this.silentAudio.pause();
                if ('mediaSession' in navigator) navigator.mediaSession.playbackState = 'paused';
            }
        }
        
        // Update Progress Bar
        if (data.duration > 0) {
            const pct = (data.currentTime / data.duration) * 100;
            document.getElementById('progress-bar-fill').style.width = pct + '%';
        }
    }

    updateMediaSession(data) {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: data.title,
                artist: data.artist,
                artwork: [
                    { src: data.cover, sizes: '512x512', type: 'image/jpeg' }
                ]
            });

            navigator.mediaSession.setActionHandler('play', () => { this.togglePlay(); });
            navigator.mediaSession.setActionHandler('pause', () => { this.togglePlay(); });
            navigator.mediaSession.setActionHandler('previoustrack', () => { this.sendCommand('prev'); });
            navigator.mediaSession.setActionHandler('nexttrack', () => { this.sendCommand('next'); });
        }
    }
}

// Helper global functions for inline onclick in HTML
window.mobileController = null;
function initMobileControl(token) {
    window.mobileController = new MobileController(token);
}
function sendCommand(cmd, payload) {
    if (window.mobileController) window.mobileController.sendCommand(cmd, payload);
}
function togglePlay() {
    if (window.mobileController) window.mobileController.togglePlay();
}
