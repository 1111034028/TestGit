function initAdminChat(id, statusUpdated) {
    document.addEventListener('DOMContentLoaded', () => {
        if (statusUpdated) {
            if (window.notifyChatChange) window.notifyChatChange();
        }
        
        setupChatPolling({
            endpoint: 'admin_chat.php?id=' + id,
            chatBoxId: 'chat-box',
            fallbackUrl: 'admin_contact.php',
            onStatusChange: (status) => {
                const footer = document.getElementById('chat-footer-area');
                const closeBtn = document.getElementById('btn-close-ticket');
                if (status === 'closed') {
                    if (closeBtn) closeBtn.remove();
                    if (footer && !footer.querySelector('.closed-notice')) {
                        footer.innerHTML = '<div class="closed-notice" style="text-align: center; color: #777; padding: 10px;">此案件已結單。</div>';
                    }
                }
            }
        });

        // Enable Enter to submit
        setupChatInput('.reply-input');
    });

    window.confirmAction = function(action, message) {
        if (typeof openModal === 'function') {
            openModal('確認操作', message, () => {
                const fd = new FormData();
                fd.append('action', action);
                fd.append('ajax', '1');
                fetch(window.location.href, { method: 'POST', body: fd })
                .then(() => {
                    if (window.notifyChatChange) window.notifyChatChange(); // Sync all pages
                    window.location.href = (action === 'delete' ? 'admin_contact.php' : window.location.href);
                });
            }, action === 'delete');
        } else {
            if (confirm(message)) {
                const fd = new FormData();
                fd.append('action', action);
                fd.append('ajax', '1');
                fetch(window.location.href, { method: 'POST', body: fd })
                .then(() => {
                    if (window.notifyChatChange) window.notifyChatChange();
                    window.location.href = (action === 'delete' ? 'admin_contact.php' : window.location.href);
                });
            }
        }
    };
}

function initUserChat(id) {
    document.addEventListener('DOMContentLoaded', () => {
        setupChatPolling({
            endpoint: 'my_messages_view.php?id=' + id,
            chatBoxId: 'chat-box',
            fallbackUrl: 'my_messages.php',
            onStatusChange: (status) => {
                const badge = document.getElementById('status-badge-ui');
                const footer = document.getElementById('chat-footer-area');
                const statusMap = {
                    'new': { text: '未讀', bg: '#ff7675' },
                    'read': { text: '處理中', bg: '#74b9ff' },
                    'closed': { text: '已結單', bg: '#b2bec3' }
                };
                if (statusMap[status] && badge) {
                    badge.innerText = statusMap[status].text;
                    badge.style.background = statusMap[status].bg;
                }
                if (status === 'closed' && footer && !footer.querySelector('.closed-notice')) {
                    footer.innerHTML = '<div class="closed-notice" style="text-align: center; color: #777; padding: 10px;">此案件已結單，無法傳送訊息。</div>';
                }
            }
        });

        // Enable Enter to submit
        setupChatInput('.reply-input');
    });
}
