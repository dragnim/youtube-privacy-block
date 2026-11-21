/**
 * Shared YouTube URL parser for the block editor and Elementor editor.
 * Mirrors YouTubePrivacyPlugin::extract_video_id() in PHP, so editor previews match the front end.
 */
(function () {
    var ID = /^[A-Za-z0-9_-]{11}$/;

    var FALLBACK_PATTERNS = [
        /(?:^(?:\/\/)?|[\s"'(<]|:\/\/)(?:(?:www|m|music)\.)?youtube(?:-nocookie)?\.com\/(?:watch\?(?:[^\s#]*&)?v=|embed\/|v\/|shorts\/|live\/)([A-Za-z0-9_-]{11})(?![A-Za-z0-9_-])/i,
        /(?:^(?:\/\/)?|[\s"'(<]|:\/\/)youtu\.be\/([A-Za-z0-9_-]{11})(?![A-Za-z0-9_-])/i
    ];

    function getVideoId(input) {
        var url = String(input || '').trim();

        // A bare 11-character video ID
        if (ID.test(url)) {
            return url;
        }

        var withScheme = /^[a-z][a-z0-9+.-]*:\/\//i.test(url) ? url : 'https://' + url;

        try {
            var parsed = new URL(withScheme);
            var host = parsed.hostname.toLowerCase().replace(/^(www\.|m\.|music\.)/, '');
            var id = '';

            if (host === 'youtu.be') {
                id = parsed.pathname.replace(/^\//, '').split('/')[0];
            } else if (host === 'youtube.com' || host === 'youtube-nocookie.com') {
                if (parsed.pathname === '/watch') {
                    id = parsed.searchParams.get('v') || '';
                } else {
                    var match = parsed.pathname.match(/^\/(embed|v|shorts|live)\/([^\/?]+)/);
                    if (match) {
                        id = match[2];
                    }
                }
            }

            if (ID.test(id)) {
                return id;
            }
        } catch (e) {
            // Not a parseable URL; try the fallback patterns.
        }

        for (var i = 0; i < FALLBACK_PATTERNS.length; i++) {
            var m = url.match(FALLBACK_PATTERNS[i]);
            if (m) {
                return m[1];
            }
        }

        return '';
    }

    window.youtubePrivacyGetVideoId = getVideoId;
})();
