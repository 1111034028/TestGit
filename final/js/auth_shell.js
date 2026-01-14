document.addEventListener('DOMContentLoaded', () => {
    // Show top header only if not in iframe (e.g. standalone page)
    if (window.self === window.top) {
        const header = document.getElementById('page-header');
        if (header) {
            header.style.display = 'flex';
        }
    }
});
