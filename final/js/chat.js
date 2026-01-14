function setupChatPolling(config) {
    const { endpoint, chatBoxId, pollIntervalMs, onStatusChange } = config;
    const chatBox = document.getElementById(chatBoxId);
    
    if (!chatBox) return null;

    // Auto scroll to bottom on load
    chatBox.scrollTop = chatBox.scrollHeight;

    const poll = async () => {
        try {
            const response = await fetch(`${endpoint}&ajax_body=1`);
            const data = await response.json();
            
            if (data.status === 'deleted') {
                return false; 
            }

            const isAtBottom = (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < 50;
            
            if (chatBox.innerHTML !== data.html) {
                chatBox.innerHTML = data.html;
                if (isAtBottom) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }

            if (onStatusChange) {
                onStatusChange(data.status);
            }
            
            return true;
        } catch (err) {
            console.error("Chat Poll Error:", err);
            return true;
        }
    };

    // Listen for changes from other tabs via global sync helper
    if (window.onChatSync) {
        window.onChatSync(() => poll());
    }
    // Listen for local updates (e.g. after submit)
    window.addEventListener('local-chat-update', () => poll());

    // Immediate initial poll
    poll().then(success => {
        if (!success) window.location.href = config.fallbackUrl || "index.php";
    });

    const intervalId = setInterval(async () => {
        const continuePolling = await poll();
        if (!continuePolling) {
            clearInterval(intervalId);
            if (window.showAlert) {
                window.showAlert("錯誤", "此對話已被刪除或無法訪問。", () => {
                    window.location.href = config.fallbackUrl || "index.php";
                });
            } else {
                alert("此對話已被刪除或無法訪問。");
                window.location.href = config.fallbackUrl || "index.php";
            }
        }
    }, pollIntervalMs || 1000);

    return intervalId;
}

function handleChatSubmit(e, form, onDone) {
    if (e) e.preventDefault();
    const formData = new FormData(form);
    formData.append('ajax', '1');

    fetch(form.action || window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(async res => {
        if (!res.ok) throw new Error("Fetch failed with status " + res.status);
        const text = await res.text();
        if (text.startsWith('error:')) {
            if (window.showAlert) window.showAlert("傳送失敗", text.replace('error:', ''));
            else alert("傳送失敗: " + text.replace('error:', ''));
        } else {
            form.reset();
            const input = form.querySelector('.reply-input');
            if (input) input.focus();
            // Small delay to ensure DB is written
            setTimeout(() => {
                if (window.notifyChatChange) window.notifyChatChange(); 
                window.location.reload(); // Force full page refresh as requested
            }, 200);
            if (onDone) onDone();
        }
    })
    .catch(err => {
        console.error("Submit Error:", err);
        const msg = "訊息傳送可能未成功，請檢查網路連線或稍後再試。";
        if (window.showAlert) window.showAlert("網路錯誤", msg);
        else alert("網路錯誤: " + msg);
    });

    return false;
}

function setupChatInput(textareaSelector) {
    const textareas = document.querySelectorAll(textareaSelector);
    textareas.forEach(textarea => {
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (e.target.form) {
                    const submitBtn = e.target.form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.click();
                    } else if (typeof e.target.form.requestSubmit === 'function') {
                        e.target.form.requestSubmit();
                    }
                }
            }
        });
    });
}
