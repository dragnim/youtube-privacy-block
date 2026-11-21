(function () {
    // Guard against the script being included more than once.
    if (window.youtubePrivacyInitialised) {
        return;
    }
    window.youtubePrivacyInitialised = true;

    // Delegated, so embeds added later (popups, tabs, AJAX) work too.
    document.addEventListener('click', function (e) {
        var container = e.target.closest ? e.target.closest('.youtube-embed-container') : null;

        if (!container || !container.dataset.videoId || container.classList.contains('is-loaded')) {
            return;
        }

        // Clicks on the privacy notice (including its link) never load the video.
        if (e.target.closest('.privacy-notice, a')) {
            return;
        }

        var videoId = container.dataset.videoId;
        if (!/^[A-Za-z0-9_-]+$/.test(videoId)) {
            return;
        }

        var params = 'autoplay=1&playsinline=1';
        var start = parseInt(container.dataset.start, 10);
        if (start > 0) {
            params += '&start=' + start;
        }

        var iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;border:none;';
        iframe.src = 'https://www.youtube-nocookie.com/embed/' + videoId + '?' + params;
        iframe.title = container.dataset.iframeTitle || 'YouTube video player';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        iframe.allowFullscreen = true;

        container.classList.add('is-loaded');
        container.innerHTML = '';
        container.appendChild(iframe);
        iframe.focus();
    });
})();
