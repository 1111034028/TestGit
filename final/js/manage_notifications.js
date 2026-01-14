document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('success')) {
        const action = params.get('success');
        let msg = '操作成功！';
        if (action === 'update') msg = '歌曲更新成功！';
        if (action === 'delete') msg = '歌曲已成功刪除！';
        if (action === 'profile_update') {
            msg = '個人資料更新成功！';
            // Trigger parent avatar refresh if new_pic is present
            if (params.has('new_pic') && window.parent && window.parent.refreshUserAvatar) {
                const newPic = params.get('new_pic');
                if (newPic) window.parent.refreshUserAvatar(newPic);
            }
        }
        
        if (typeof showAlert === 'function') {
            showAlert('成功', msg, () => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    } else if (params.has('error')) {
        if (typeof showAlert === 'function') {
            showAlert('錯誤', '操作失敗，請檢查權限或稍後再試。', () => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    }
});
