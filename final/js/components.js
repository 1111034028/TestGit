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

/* Global Toast Logic */
window.showToast = function(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const colors = {
        success: '#2ecc71',
        error: '#ff4757',
        info: '#3498db'
    };
    
    toast.style.cssText = `
        background: rgba(0, 0, 0, 0.85);
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        border-left: 4px solid ${colors[type] || colors.info};
        backdrop-filter: blur(5px);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        pointer-events: auto;
    `;
    toast.textContent = message;

    container.appendChild(toast);

    // Fade In
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);

    // Fade Out
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

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
