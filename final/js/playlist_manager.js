/* Playlist Management Actions */

function openCreateModal() {
    const modal = document.getElementById('create-modal');
    const input = document.getElementById('create-input');
    if (modal) modal.style.display = 'flex';
    if (input) {
        input.value = '';
        input.focus();
    }
}

async function submitCreate(e) {
    if (e) e.preventDefault();
    const name = document.getElementById('create-input').value;
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', name);
    formData.append('ajax', '1');

    try {
        const response = await fetch('playlist_act.php', {
            method: 'POST',
            body: formData
        });
        const text = await response.text();
        if (text.trim() === 'SUCCESS') {
            location.reload();
        } else {
            showAlert('建立失敗', text.replace('ERROR: ', '') || '未知錯誤');
        }
    } catch (err) {
        console.error(err);
        showAlert('發生錯誤', '網路連線異常');
    }
    return false;
}

function openRenameModal(id, currentName) {
    // Close any open dropdowns
    document.querySelectorAll('.dropdown-active, .row-dropdown-active').forEach(d => {
        d.classList.remove('dropdown-active');
        d.classList.remove('row-dropdown-active');
    });
    
    const modal = document.getElementById('rename-modal');
    const input = document.getElementById('rename-input');
    const idField = document.getElementById('rename-playlist-id');
    
    if (modal) modal.style.display = 'flex';
    if (idField) idField.value = id;
    if (input) {
        input.value = currentName;
        input.focus();
    }
}

async function submitRename(e) {
    if (e) e.preventDefault();
    const id = document.getElementById('rename-playlist-id').value;
    const name = document.getElementById('rename-input').value;
    
    const formData = new FormData();
    formData.append('action', 'rename');
    formData.append('playlist_id', id);
    formData.append('name', name);
    formData.append('ajax', '1');

    try {
        const response = await fetch('playlist_act.php', {
            method: 'POST',
            body: formData
        });
        const text = await response.text();
        if (text.trim() === 'SUCCESS') {
            location.reload();
        } else {
            showAlert('更名失敗', text.replace('ERROR: ', '') || '未知錯誤');
        }
    } catch (err) {
        console.error(err);
        showAlert('發生錯誤', '網路連線異常');
    }
    return false;
}

async function togglePinPlaylist(id, currentStatus) {
    const action = currentStatus ? 'unpin_playlist' : 'pin_playlist';
    const formData = new FormData();
    formData.append('action', action);
    formData.append('playlist_id', id);
    formData.append('ajax', '1');

    try {
        const response = await fetch('playlist_act.php', { method: 'POST', body: formData });
        const text = await response.text();
        if (text.trim() === 'SUCCESS') location.reload();
        else showAlert('操作失敗', text.replace('ERROR: ', ''));
    } catch (err) { console.error(err); showAlert('錯誤', '網路異常'); }
}

function deletePlaylist(id, name) {
    document.querySelectorAll('.dropdown-active').forEach(d => d.classList.remove('dropdown-active'));
    
    if (typeof openModal === 'function') {
        openModal('刪除播放清單', `確定要刪除「${name}」嗎？此動作無法復原。`, function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'playlist_act.php';
            
            const fields = { action: 'delete', playlist_id: id };
            for(const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }, true);
    } else {
        if (confirm(`確定要刪除「${name}」嗎？`)) {
            location.href = `playlist_act.php?action=delete&playlist_id=${id}`;
        }
    }
}

async function togglePinSong(linkId, currentStatus) {
    const action = currentStatus ? 'unpin_song' : 'pin_song';
    const formData = new FormData();
    formData.append('action', action);
    formData.append('link_id', linkId);
    formData.append('ajax', '1');

    try {
        const response = await fetch('playlist_act.php', { method: 'POST', body: formData });
        const text = await response.text();
        if (text.trim() === 'SUCCESS') location.reload();
        else showAlert('操作失敗', text.replace('ERROR: ', ''));
    } catch (err) { console.error(err); showAlert('錯誤', '網路異常'); }
}

function removeSong(pid, sid) {
    openModal('移除歌曲', '確定要從清單中移除這首歌嗎？', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'playlist_act.php';
        const fields = { action: 'remove_song', playlist_id: pid, song_id: sid };
        for(const name in fields) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = fields[name];
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }, true);
}

function playPlaylist(pid, playlistName) {
    if (window.parent && window.parent.setPlaylistContext) {
         window.parent.setPlaylistContext(pid, playlistName);
    }
    if (window.parent && window.parent.loadQueue) {
        window.parent.loadQueue('playlist', pid, 0); 
    } else {
        showAlert("無法播放", "播放器未就緒，請重新載入頁面。");
    }
}
