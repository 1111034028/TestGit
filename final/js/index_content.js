// Mode 1: Content Page (Song List) Logic

function playSongInContext(title, artist, src, cover, id) {
  // Play from 'all' context starting at this song
  if (window.parent && window.parent.loadQueue) {
    window.parent.loadQueue("all", 0, id); // Load all, play specific ID
  }
}

function playAllShuffle() {
  if (window.parent && window.parent.loadQueue) {
    window.parent.loadQueue("all", 0, 0, true); // type, id, startId, shuffle
  }
}

// Playlist Modal Logic
function openPlaylistModal(songId) {
  document.getElementById("modal-song-id").value = songId;
  const select = document.getElementById("modal-playlist-select");

  // Fetch playlists
  fetch("api_get_playlists.php")
    .then((res) => res.json())
    .then((data) => {
      select.innerHTML = "";
      if (data.length === 0) {
        const opt = document.createElement("option");
        opt.text = "無歌單 - 請先建立";
        select.add(opt);
      } else {
        data.forEach((p) => {
          const opt = document.createElement("option");
          opt.value = p.id;
          opt.text = p.name;
          select.add(opt);
        });
      }
      document.getElementById("playlist-modal").style.display = "flex";
    });
}

// Close modal on outside click
document
  .getElementById("playlist-modal")
  .addEventListener("click", function (e) {
    if (e.target === this) this.style.display = "none";
  });
