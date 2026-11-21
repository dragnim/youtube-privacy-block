
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.youtube-embed-container');
    
    containers.forEach(container => {
        container.addEventListener('click', function(e) {
            // Don't load video if user clicked on the privacy link
            if (e.target.tagName === 'A') {
                return;
            }
            
            const videoId = this.dataset.videoId;
            const iframe = document.createElement('iframe');
            iframe.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;border:none;';
            iframe.src = 'https://www.youtube-nocookie.com/embed/' + videoId + '?autoplay=1';
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
            iframe.allowFullscreen = true;
            
            this.innerHTML = '';
            this.appendChild(iframe);
        });
    });
});
