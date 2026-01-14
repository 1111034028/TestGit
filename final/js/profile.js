function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.getElementById('avatar-preview-container');
            let img = container.querySelector('img');
            if (!img) {
                container.innerHTML = '';
                img = document.createElement('img');
                container.appendChild(img);
            }
            img.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
