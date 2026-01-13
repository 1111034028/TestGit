// Mode 1: Content Page (Song List) Logic

function playSongInContext(title, artist, src, cover, id) {
  // Play from 'all' context starting at this song
  if (window.parent && window.parent.loadQueue) {
    window.parent.loadQueue("all", 0, id); // Load all, play specific ID
  }
}



// Playlist Modal Logic
function openPlaylistModal(songId) {

  const container = document.querySelector("#playlist-modal .modal-content form");
  
  // Fetch playlists with song status
  fetch(`api_check_song_in_playlists.php?song_id=${songId}`)
    .then((res) => res.json())
    .then((data) => {
      // Clear existing content except hidden inputs
      const formContent = `
        <h3>加入播放清單</h3>
        <div id="playlist-buttons-container" style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
          ${data.length === 0 ? '<p style="color: #aaa; text-align: center;">無播放清單 - 請先建立</p>' : 
            data.map(p => `
              <button type="button" 
                      class="playlist-option-btn ${p.in_playlist ? 'in-playlist' : ''}" 
                      onclick="toggleSongInPlaylist(${songId}, ${p.id}, ${p.in_playlist})"
                      style="width: 100%; padding: 12px; margin-bottom: 8px; background: ${p.in_playlist ? '#2d3436' : '#444'}; color: white; border: 1px solid ${p.in_playlist ? '#ff4757' : '#666'}; border-radius: 4px; cursor: pointer; text-align: left; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
                <span>${p.name}</span>
                <span style="color: ${p.in_playlist ? '#ff4757' : '#2ecc71'}; font-weight: bold;">${p.in_playlist ? '✓ 移除' : '+ 加入'}</span>
              </button>
            `).join('')
          }
        </div>
        <button type="button" class="btn-create" onclick="showCreateForm(${songId})" style="width: 100%; padding: 12px; margin-bottom: 8px; background: white; color: black; border: none; border-radius: 20px; font-weight: bold; cursor: pointer;">+ 新增播放清單</button>
        <button type="button" class="btn-secondary" onclick="document.getElementById('playlist-modal').style.display='none'" style="width: 100%; background: none; border: 1px solid #666; color: #aaa; margin-top: 5px;">關閉</button>
      `;
      
      container.innerHTML = formContent;
      document.getElementById("playlist-modal").style.display = "flex";
    })
    .catch(err => {
      console.error("Failed to load playlists:", err);
      alert("載入播放清單失敗");
    });
}

function showCreateForm(songId) {
    const container = document.querySelector("#playlist-modal .modal-content form");
    container.innerHTML = `
        <h3>建立新歌單</h3>
        <input type="text" id="new-playlist-name" placeholder="輸入清單名稱" style="width: 100%; padding: 12px; margin: 20px 0; background: #333; color: white; border: 1px solid #555; border-radius: 4px; box-sizing: border-box;">
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="openPlaylistModal(${songId})" style="flex: 1; padding: 12px; background: #444; color: white; border: none; border-radius: 4px; cursor: pointer;">取消</button>
            <button type="button" onclick="createPlaylist(${songId})" style="flex: 1; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">建立</button>
        </div>
    `;
}

function createPlaylist(songId) {
    const nameInput = document.getElementById("new-playlist-name");
    const name = nameInput.value.trim();
    
    if (!name) {
        alert("請輸入清單名稱");
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', name);
    formData.append('ajax', '1');
    
    fetch('playlist_act.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        const response = text.trim();
        if (response === 'SUCCESS') {
            // Go back to list view which will reload playlists
            openPlaylistModal(songId);
        } else {
            alert(response.replace('ERROR: ', '') || "建立失敗");
        }
    })
    .catch(err => {
        console.error("Create playlist error:", err);
        alert("網路錯誤");
    });
}

// Toggle song in playlist (add or remove)
function toggleSongInPlaylist(songId, playlistId, isInPlaylist) {
  const action = isInPlaylist ? 'remove_song' : 'add_song';
  const formData = new FormData();
  formData.append('action', action);
  formData.append('song_id', songId);
  formData.append('playlist_id', playlistId);
  formData.append('ajax', '1'); // Enable AJAX mode
  
  fetch('playlist_act.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text()) // Get text response
  .then(text => {
    // Trim whitespace/BOM
    const response = text.trim();
    
    if (response === 'SUCCESS') {
        // Refresh the modal upon success
        openPlaylistModal(songId);
    } else {
        // Handle error messages (remove "ERROR: " prefix if present)
        const msg = response.replace('ERROR: ', '');
        console.error("Server returned error:", response);
        alert(msg || "操作失敗");
    }
  })
  .catch(err => {
    console.error("Network or Fetch error:", err);
    alert("網路錯誤或請求被中斷: " + err.message);
  });
}

// Close modal on outside click
document
  .getElementById("playlist-modal")
  .addEventListener("click", function (e) {
    if (e.target === this) this.style.display = "none";
  });
