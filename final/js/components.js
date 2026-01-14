/* Global Modal Logic */
(function() {
    if (window.openModalInitialized) return;
    window.openModalInitialized = true;

    // Cross-tab Synchronization
    const chatChannel = window.BroadcastChannel ? new BroadcastChannel('chat_sync') : null;
    window.notifyChatChange = function() {
        try {
            if (chatChannel) chatChannel.postMessage('refresh');
            localStorage.setItem('chat_sync_timestamp', Date.now());
        } catch(e) { console.error("Sync Error:", e); }
    };
    window.onChatSync = function(callback) {
        try {
            if (chatChannel) {
                chatChannel.addEventListener('message', (e) => { if (e.data === 'refresh') callback(); });
            }
            window.addEventListener('storage', (e) => {
                if (e.key === 'chat_sync_timestamp') callback();
            });
        } catch(e) { console.error("Sync Listener Error:", e); }
    };

    let globalConfirmCallback = null;

    window.openModal = function(title, message, callback, isDanger = false) {
        const modal = document.getElementById('globalConfirmModal');
        const box = document.getElementById('globalModalBox');
        const titleEl = document.getElementById('globalModalTitle');
        const textEl = document.getElementById('globalModalText');
        const btn = document.getElementById('globalModalConfirmBtn');

        if (!modal || !box) {
            // Fallback if elements not present
            if (confirm(message)) callback();
            return;
        }

        titleEl.textContent = title;
        textEl.textContent = message;
        globalConfirmCallback = callback;

        if (isDanger) {
            btn.classList.add('danger');
        } else {
            btn.classList.remove('danger');
        }

        modal.style.display = 'flex';
        void modal.offsetWidth; 
        setTimeout(() => box.classList.add('active'), 10);
    };
    
    window.showAlert = function(title, message, callback) {
        const modal = document.getElementById('globalConfirmModal');
        const box = document.getElementById('globalModalBox');
        const titleEl = document.getElementById('globalModalTitle');
        const textEl = document.getElementById('globalModalText');
        const confirmBtn = document.getElementById('globalModalConfirmBtn');
        const cancelBtn = modal ? modal.querySelector('.modal-btn.cancel') : null;

        if (!modal || !box) {
            alert(message);
            if (callback) callback();
            return;
        }

        titleEl.textContent = title;
        textEl.textContent = message;
        globalConfirmCallback = callback || null;

        if (cancelBtn) cancelBtn.style.display = 'none';
        confirmBtn.classList.remove('danger');
        confirmBtn.textContent = '確定';

        modal.style.display = 'flex';
        void modal.offsetWidth; 
        setTimeout(() => box.classList.add('active'), 10);
    };

    window.closeGlobalModal = function() {
        const modal = document.getElementById('globalConfirmModal');
        const box = document.getElementById('globalModalBox');
        if (!modal || !box) return;
        
        box.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            globalConfirmCallback = null;
            const cancelBtn = modal.querySelector('.modal-btn.cancel');
            if (cancelBtn) cancelBtn.style.display = ''; // Restore
        }, 200);
    };

    window.confirmLink = function(e, url, title, message, isDanger = false) {
        if (e) e.preventDefault();
        window.openModal(title, message, () => {
            window.location.href = url;
        }, isDanger);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('globalModalConfirmBtn');
        if (btn) {
            btn.onclick = () => {
                if (typeof globalConfirmCallback === 'function') {
                    globalConfirmCallback();
                }
                window.closeGlobalModal();
            };
        }

        const modal = document.getElementById('globalConfirmModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    window.closeGlobalModal();
                }
            });
        }
    });
})();

/* Global Dropdown Logic */
function toggleDropdown(e, btn) {
    if (e) e.stopPropagation();
    const container = btn.parentElement;
    document.querySelectorAll('.settings-dropdown, .row-settings-dropdown').forEach(d => {
        if (d !== container) {
            d.classList.remove('dropdown-active');
            d.classList.remove('row-dropdown-active');
        }
    });
    // Toggle the targeted class
    if (container.classList.contains('settings-dropdown')) {
        container.classList.toggle('dropdown-active');
    } else {
        container.classList.toggle('row-dropdown-active');
    }
}

window.addEventListener('click', function(e) {
    if (!e.target.closest('.settings-dropdown') && !e.target.closest('.row-settings-dropdown')) {
        document.querySelectorAll('.settings-dropdown').forEach(d => d.classList.remove('dropdown-active'));
        document.querySelectorAll('.row-settings-dropdown').forEach(d => d.classList.remove('row-dropdown-active'));
    }
});
