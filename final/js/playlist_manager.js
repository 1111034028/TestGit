// Global Playlist Modal Functions
const modal = document.getElementById("playlist-modal");
if (modal) {
    modal.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });
}

window.openCurrentPlaylistModal = function() {
    if (window.currentIndex < 0 || !window.queue || window.currentIndex >= window.queue.length) {
        if (window.showToast) window.showToast("目前沒有播放中的歌曲", "info");
        else if(window.showAlert) showAlert("提示", "請先播放歌曲");
        else alert("請先播放歌曲");
        return;
    }
    const songId = window.queue[window.currentIndex].id;
    window.openPlaylistModal(songId);
};

window.openPlaylistModal = function(songId) {
  const modalEl = document.getElementById("playlist-modal");
  if (!modalEl) return;
  const container = modalEl.querySelector(".modal-content form");
  
  const ts = new Date().getTime();
  fetch(`api_check_song_in_playlists.php?song_id=${songId}&t=${ts}`)
    .then(res => res.json())
    .then(data => {
      const formContent = `
        <h3 style="margin-bottom: 25px; color: white;">加入播放清單</h3>
        <div id="playlist-buttons-container" style="max-height: 350px; overflow-y: auto; margin-bottom: 20px; padding-right: 5px;">
          ${data.length === 0 ? '<p style="color: #aaa; text-align: center; padding: 20px;">無播放清單 - 請先建立</p>' : 
            data.map(p => `
              <button type="button" 
                      class="playlist-option-btn ${p.in_playlist ? 'in-playlist' : ''}" 
                      onclick="toggleSongInPlaylist(${songId}, ${p.id}, ${p.in_playlist}, '${p.name.replace(/'/g, "\\'")}')"
                      style="width: 100%; padding: 10px 14px; margin-bottom: 10px; background: #2a2a2a; color: white; border: 1.5px solid ${p.in_playlist ? '#ff4757' : '#444'}; border-radius: 10px; cursor: pointer; text-align: left; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
                <span style="font-weight: 500; font-size: 0.9rem;">${p.name}</span>
                <span style="background: ${p.in_playlist ? 'rgba(255, 71, 87, 0.15)' : 'rgba(46, 204, 113, 0.15)'}; 
                             color: ${p.in_playlist ? '#ff4757' : '#2ecc71'}; 
                             border: 1px solid ${p.in_playlist ? '#ff4757' : '#2ecc71'};
                             font-weight: bold; font-size: 0.75rem; padding: 5px 12px; border-radius: 6px; min-width: 60px; text-align: center;">
                  ${p.in_playlist ? '✕ 移除' : '+ 加入'}
                </span>
              </button>
            `).join('')
          }
        </div>
        <button type="button" onclick="showCreateForm(${songId})" style="width: 100%; padding: 14px; margin-bottom: 12px; background: white; color: black; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; transition: transform 0.2s;">+ 新增播放清單</button>
        <button type="button" onclick="document.getElementById('playlist-modal').style.display='none'" style="width: 100%; background: none; border: 1px solid #555; color: #999; margin-top: 5px; border-radius: 25px; padding: 10px; cursor: pointer;">取消</button>
      `;
      
      container.innerHTML = formContent;
      modalEl.style.display = "flex";
    })
    .catch(err => {
        console.error("Playlist modal error:", err);
        if (window.showToast) showToast("無法載入清單狀態", "error");
    });
};

window.showCreateForm = function(songId) {
    const container = document.querySelector("#playlist-modal .modal-content form");
    container.innerHTML = `
        <h3 style="color: white;">建立新歌單</h3>
        <input type="text" id="new-playlist-name" placeholder="輸入清單名稱" style="width: 100%; padding: 14px; margin: 25px 0; background: #444; color: white; border: 1px solid #555; border-radius: 8px; box-sizing: border-box; font-size: 1rem;">
        <div style="display: flex; gap: 12px;">
            <button type="button" onclick="openPlaylistModal(${songId})" style="flex: 1; padding: 12px; background: #333; color: #ccc; border: 1px solid #555; border-radius: 25px; cursor: pointer;">取消</button>
            <button type="button" onclick="createPlaylist(${songId})" style="flex: 1; padding: 12px; background: #ff4757; color: white; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;">建立</button>
        </div>
    `;
};

window.createPlaylist = function(songId) {
    const nameInput = document.getElementById("new-playlist-name");
    if (!nameInput) return;
    const name = nameInput.value.trim();
    if (!name) {
        if (window.showToast) showToast("請輸入清單名稱", "error");
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', name);
    formData.append('ajax', '1');
    
    fetch('playlist_act.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(text => {
        if (text.trim().includes('SUCCESS')) {
            if (window.showToast) showToast(`已建立清單: ${name}`, "success");
            openPlaylistModal(songId);
        } else {
            if (window.showToast) showToast("建立清單失敗", "error");
        }
    })
    .catch(err => {
        console.error("Create playlist error:", err);
        if (window.showToast) showToast("網路錯誤", "error");
    });
};

window.toggleSongInPlaylist = function(songId, playlistId, isInPlaylist, playlistName) {
  const action = isInPlaylist ? 'remove_song' : 'add_song';
  const formData = new FormData();
  formData.append('action', action);
  formData.append('song_id', songId);
  formData.append('playlist_id', playlistId);
  formData.append('ajax', '1');
  
  fetch('playlist_act.php', { method: 'POST', body: formData })
  .then(res => res.text())
  .then(text => {
    const resp = text.trim();
    if (resp.includes('SUCCESS')) {
        const msg = isInPlaylist ? `已從 [${playlistName}] 移除` : `已加入 [${playlistName}]`;
        if (window.showToast) showToast(msg, "success");
        // Immediate refresh call
        openPlaylistModal(songId);
    } else {
        console.warn("Toggle failed:", resp);
        if (window.showToast) showToast("更新失敗", "error");
    }
  })
  .catch(err => {
    console.error("Toggle error:", err);
    if (window.showToast) showToast("通訊失敗", "error");
  });
};

// --- Playlist Management Page (my_playlists.php) Functions ---

// toggleDropdown and global click listener are handled by components.js

// Create Playlist Modal (Page version)
window.openCreateModal = function() {
    const modal = document.getElementById('create-modal');
    if (modal) modal.style.display = 'flex';
};

window.submitCreate = function(e) {
    e.preventDefault();
    const nameInput = document.getElementById('create-input');
    const name = nameInput.value.trim();
    if (!name) return;

    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', name);
    formData.append('ajax', '1');

    fetch('playlist_act.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(text => {
            if (text.trim().includes('SUCCESS')) {
                location.reload();
            } else {
                if(window.showAlert) showAlert("錯誤", "建立失敗: " + text);
                else alert("建立失敗: " + text);
            }
        });
};

// Rename Playlist
window.openRenameModal = function(id, name) {
    const modal = document.getElementById('rename-modal');
    document.getElementById('rename-playlist-id').value = id;
    document.getElementById('rename-input').value = name;
    if (modal) {
        modal.style.display = 'flex';
        // Focus input
        setTimeout(() => document.getElementById('rename-input').focus(), 100);
    }
};

window.submitRename = function(e) {
    e.preventDefault();
    const id = document.getElementById('rename-playlist-id').value;
    const nameInput = document.getElementById('rename-input');
    const name = nameInput.value.trim();
    if (!name) return;

    const formData = new FormData();
    formData.append('action', 'rename');
    formData.append('playlist_id', id);
    formData.append('name', name);
    formData.append('ajax', '1');

    fetch('playlist_act.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(text => {
            if (text.trim().includes('SUCCESS')) {
                location.reload();
            } else if(text.trim().includes('預設清單')) {
                if(window.showAlert) showAlert("提示", "系統預設清單無法更名");
                else alert("系統預設清單無法更名");
            } else {
                if(window.showAlert) showAlert("錯誤", "更新失敗: " + text);
                else alert("更新失敗: " + text);
            }
        });
};

// Pin Playlist
window.togglePinPlaylist = function(id, isPinned) {
    const action = isPinned ? 'unpin_playlist' : 'pin_playlist';
    const formData = new FormData();
    formData.append('action', action);
    formData.append('playlist_id', id);
    formData.append('ajax', '1');

    fetch('playlist_act.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(text => {
            if (text.trim().includes('SUCCESS')) {
                location.reload();
            } else {
                if(window.showAlert) showAlert("錯誤", "操作失敗: " + text);
                else alert("操作失敗");
            }
        });
};

// Delete Playlist
window.deletePlaylist = function(id, name) {
    const confirmAction = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'playlist_act.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'playlist_id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    };

    if (window.openModal) {
        openModal("刪除確認", `確定要刪除「${name}」嗎？此動作無法復原。`, confirmAction, true);
    } else {
        if (confirm(`確定要刪除「${name}」嗎？此動作無法復原。`)) {
            confirmAction();
        }
    }
};
