// Remote Control Client Logic (Desktop)

let remoteSessionToken = null;
let pollInterval = null;
const POLL_RATE = 1500; // 1.5s (adjust based on server load capacity)

function initRemoteControl() {
  const modal = document.getElementById('remote-modal');
  if (modal) modal.style.display = 'flex';

  console.log("Initializing Remote Control...");

  // Check if QRCode library is loaded
  if (typeof QRCode === "undefined") {
    const script = document.createElement("script");
    script.src =
      "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js";
    script.onload = () => {
      console.log("QRCode Lib Loaded");
      requestRemoteSession();
    };
    script.onerror = () => {
      document.getElementById("qr-stage").innerHTML =
        '<p style="color:red">Failed to load QR library</p>';
    };
    document.head.appendChild(script);
  } else {
    requestRemoteSession();
  }
}

function requestRemoteSession() {
  fetch("api_remote_create.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        remoteSessionToken = data.token;
        renderQR(remoteSessionToken);
        startPolling(remoteSessionToken);
        setupSync(remoteSessionToken); // Init Sync
      } else {
        console.error("Failed to create session:", data.message);
      }
    })
    .catch((err) => console.error("API Error:", err));
}

function renderQR(token) {
  const container = document.getElementById("qr-code-container");
  if (!container) return;

  container.innerHTML = ""; // Clear previous

  // Construct Mobile URL
  // Assumption: mobile_control.php is in the same directory
  let protocol = window.location.protocol;
  let host = window.location.host;
  
  // Force specific IP for mobile connection if on localhost
  if (host.includes('localhost') || host.includes('127.0.0.1')) {
      host = '192.168.28.12';
  }
  
  const path = window.location.pathname.substring(
    0,
    window.location.pathname.lastIndexOf("/")
  );
  const mobileUrl = `${protocol}//${host}${path}/mobile_control.php?token=${token}`;

  console.log("Mobile Control URL:", mobileUrl);

  new QRCode(container, {
    text: mobileUrl,
    width: 180,
    height: 180,
    colorDark: "#000000",
    colorLight: "#ffffff",
  });

  document.getElementById("mobile-url-hint").innerHTML =
    `請掃描 QR Code<br><span style="font-size:0.8em; color:#888; word-break:break-all;">${mobileUrl}</span>`;
}

function startPolling(token) {
  // Stop existing poll if any
  if (pollInterval) clearInterval(pollInterval);

  pollInterval = setInterval(() => {
    pollCommands(token);
  }, POLL_RATE);
}

function pollCommands(token) {
  fetch(`api_remote_check.php?token=${token}`)
    .then((res) => res.json())
    .then((commands) => {
      if (commands && commands.length > 0) {
        commands.forEach((cmd) => {
          executeCommand(cmd);
        });
      }
    })
    .catch((err) => console.error("Poll Error:", err));
}

function executeCommand(cmdObj) {
  const { command, payload } = cmdObj;
  console.log("Remote Command Received:", command, payload);

  switch (command) {
    case "play":
    case "pause":
    case "toggle_play":
      // Call index_shell.js function
      if (typeof togglePlay === "function") togglePlay();
      break;

    case "next":
      if (typeof nextSong === "function") nextSong();
      break;

    case "prev":
      if (typeof prevSong === "function") prevSong();
      break;

    case "volume":
      const vol = parseFloat(payload);
      if (!isNaN(vol) && typeof audio !== "undefined") {
        audio.volume = vol;
        if (typeof updateVolumeIcon === "function") updateVolumeIcon(vol);
        const slider = document.getElementById("volume-slider");
        if (slider) slider.value = vol;
      }
      break;

    case "mute":
      if (typeof toggleMute === "function") toggleMute();
      break;

    default:
      console.warn("Unknown command:", command);
  }
}

// Cleanup on unload
window.addEventListener("beforeunload", () => {
  if (pollInterval) clearInterval(pollInterval);
});

// --- V2: State Synchronization (Desktop -> Mobile) ---

function setupSync(token) {
    const audioEl = document.getElementById('audio-player') || window.audio;
    if (!audioEl) {
        console.warn("Audio element not found, cannot sync state.");
        return;
    }

    // 1. Listen to Audio Events
    audioEl.addEventListener('play', () => pushStateToRemote(token));
    audioEl.addEventListener('pause', () => pushStateToRemote(token));
    audioEl.addEventListener('timeupdate', () => {
        // Throttle timeupdate to avoid flooding (e.g. every 2 seconds or just relied on poll?)
        // Actually, for remote control, precise time isn't critical but periodic updates help.
        // Let's NOT push on every timeupdate to save bandwidth, unless we want live-ish progress.
        // For now, let's just rely on play/pause and metadata changes, plus maybe a slow interval.
    });
    
    // 2. Listen to Song Info Changes (using Name Mutation)
    const titleElem = document.getElementById('player-title');
    if (titleElem) {
        const observer = new MutationObserver(() => {
            setTimeout(() => pushStateToRemote(token), 300); 
        });
        observer.observe(titleElem, { childList: true, subtree: true, characterData: true });
    }

    // Initial Push
    setTimeout(() => pushStateToRemote(token), 1000);
}

function pushStateToRemote(token) {
    const audioEl = document.getElementById('audio-player') || window.audio;
    if (!audioEl) return;

    // Gather State
    const titleVal = document.getElementById('player-title')?.innerText || 'Unknown Title';
    const artistVal = document.getElementById('player-artist')?.innerText || 'Unknown Artist';
    const coverVal = document.getElementById('player-cover')?.src || ''; 
    const isPlaying = !audioEl.paused;
    
    // DEBUG LOG
    console.log("Pushing State:", titleVal, isPlaying, audioEl.currentTime);

    const statePayload = {
        title: titleVal,
        artist: artistVal,
        cover: coverVal,
        isPlaying: isPlaying,
        currentTime: audioEl.currentTime,
        duration: audioEl.duration
    };

    fetch(`api_remote_sync.php?action=push&token=${token}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(statePayload)
    }).catch(err => console.error("Sync Error:", err));
}
