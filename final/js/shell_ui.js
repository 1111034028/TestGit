// Mode 2: App Shell UI & Navigation Logic

// User Menu Toggle
window.toggleUserMenu = function() {
    const menu = document.getElementById('user-dropdown');
    if (!menu) return;
    
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
        document.removeEventListener('click', closeUserMenuOnClick);
    } else {
        menu.style.display = 'block';
        setTimeout(() => {
            document.addEventListener('click', closeUserMenuOnClick);
        }, 0);
    }
};

function closeUserMenuOnClick(e) {
    const menu = document.getElementById('user-dropdown');
    const btn = document.getElementById('user-menu-btn');
    if (menu && btn && !menu.contains(e.target) && !btn.contains(e.target)) {
        menu.style.display = 'none';
        document.removeEventListener('click', closeUserMenuOnClick);
    }
}

// Expose internal state saving for child pages
window.savePageState = function(fullUrl) {
    try {
        let relativeUrl = '';
        if (fullUrl.includes('?')) {
            const parts = fullUrl.split('/');
            relativeUrl = parts.pop(); 
        } else {
            relativeUrl = fullUrl.split('/').pop();
        }
        
        if (!relativeUrl || relativeUrl === 'about:blank' || relativeUrl.includes('logout.php') || relativeUrl.includes('login.php')) return;
        
        console.log("Saving State:", relativeUrl);
        sessionStorage.setItem('lastPage_v2', relativeUrl);
             
         // Update Active State
         document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
         const navHome = document.getElementById('nav-home');
         const navSearch = document.getElementById('nav-search');
         const navLibrary = document.getElementById('nav-library');
         const navCreator = document.getElementById('nav-creator');
         const navProfile = document.getElementById('nav-profile');
         
         if (relativeUrl.includes('index.php') && navHome) navHome.classList.add('active');
         else if (relativeUrl.includes('search') && navSearch) navSearch.classList.add('active');
         else if (relativeUrl.includes('playlist') && navLibrary) navLibrary.classList.add('active');
         else if (relativeUrl.includes('creator') && navCreator) navCreator.classList.add('active');
         else if (relativeUrl.includes('profile') && navProfile) navProfile.classList.add('active');
         else if (relativeUrl.includes('my_messages') && navProfile) navProfile.classList.add('active'); 
    } catch (e) {
        console.warn("State Save Error:", e);
    }
};

function navigate(url) {
    document.getElementById('content-frame').src = url;
    if (window.savePageState) window.savePageState(url);
    else sessionStorage.setItem('lastPage_v2', url);
    
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    if (url.includes('index.php')) document.getElementById('nav-home').classList.add('active');
    else if (url.includes('search')) document.getElementById('nav-search').classList.add('active');
    else if (url.includes('playlist')) document.getElementById('nav-library').classList.add('active');
    else if (url.includes('creator')) document.getElementById('nav-creator').classList.add('active');
    else if (url.includes('profile')) document.getElementById('nav-profile').classList.add('active');
}

function highlightNav(id) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('collapsed');
}

// Monitor Iframe Navigation
const contentFrame = document.getElementById('content-frame');
if (contentFrame) {
    contentFrame.addEventListener('load', function() {
        try {
            const currentUrl = contentFrame.contentWindow.location.href;
            if (currentUrl !== 'about:blank') {
                window.savePageState(currentUrl);
            }
        } catch (e) {}
    });
}

// Restore Last Page on Refresh
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        try {
            const lastPage = sessionStorage.getItem('lastPage_v2');
            if (lastPage && lastPage !== 'about:blank' && !lastPage.includes('logout.php') && !lastPage.includes('login.php')) {
                const currentFrameSrc = document.getElementById('content-frame').contentWindow.location.href;
                if (!currentFrameSrc.includes(lastPage)) {
                     navigate(lastPage);
                }
            } else {
                navigate('index.php?inner=1');
            }
        } catch(e) {
            navigate('index.php?inner=1');
        }
    }, 150);
});

window.refreshUserAvatar = function(newSrc) {
    const avatar = document.getElementById('user-avatar-img');
    if (newSrc && !newSrc.includes('/')) newSrc = 'img/avatars/' + newSrc;
    if (avatar && newSrc) {
        avatar.src = newSrc + '?t=' + new Date().getTime();
    }
};
