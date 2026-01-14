document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = '傳送中...';
            
            const fd = new FormData(form);
            fd.append('ajax', '1');
            
            fetch(form.action, {
                method: 'POST',
                body: fd
            })
            .then(res => res.text())
            .then(text => {
                if (text === 'SUCCESS') {
                    // Instant notify other tabs
                    if (window.notifyChatChange) window.notifyChatChange();
                    
                    if (window.showAlert) {
                        window.showAlert('發送成功', '感謝您的回報！我們將盡快處理。', () => {
                            window.location.href = 'my_messages.php';
                        });
                    } else {
                        alert('發送成功！感謝您的回報。');
                        window.location.href = 'my_messages.php';
                    }
                } else {
                    const errorMsg = text.startsWith('error:') ? text.replace('error:', '') : '傳送失敗，請稍後再試。';
                    if (window.showAlert) window.showAlert('錯誤', errorMsg);
                    else alert('錯誤: ' + errorMsg);
                    
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showAlert) window.showAlert('網路錯誤', '無法建立連線，請檢查網路狀態。');
                else alert('網路錯誤: 無法建立連線。');
                
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }
});
