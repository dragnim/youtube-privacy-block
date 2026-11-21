(function (wp) {
    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var blockEditor = wp.blockEditor;
    var components = wp.components;

    var config = window.youtubePrivacyEditor || {};

    // Shared parser (url-parser.js), identical rules to the PHP side.
    function getVideoId(url) {
        return window.youtubePrivacyGetVideoId ? window.youtubePrivacyGetVideoId(url) : '';
    }

    registerBlockType('youtube-privacy/embed', {
        apiVersion: 3,
        title: __('YouTube Privacy Embed', 'youtube-privacy'),
        icon: 'video-alt3',
        category: 'embed',
        attributes: {
            videoUrl: {
                type: 'string',
                default: ''
            },
            videoTitle: {
                type: 'string',
                default: ''
            },
            thumbnailUrl: {
                type: 'string',
                default: ''
            }
        },

        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = blockEditor.useBlockProps({ className: 'youtube-privacy-block-editor' });
            var videoId = getVideoId(attributes.videoUrl);

            function urlControl(hideLabel) {
                return el(components.TextControl, {
                    label: __('YouTube video URL', 'youtube-privacy'),
                    hideLabelFromVision: !!hideLabel,
                    value: attributes.videoUrl,
                    onChange: function (value) {
                        setAttributes({ videoUrl: value });
                    },
                    placeholder: 'https://www.youtube.com/watch?v=...',
                    help: hideLabel ? undefined : __('Supports watch, youtu.be, Shorts and live links. Start times (t=) are kept.', 'youtube-privacy'),
                    __nextHasNoMarginBottom: true
                });
            }

            var inspector = el(blockEditor.InspectorControls, {},
                el(components.PanelBody, { title: __('Video settings', 'youtube-privacy') },
                    urlControl(false),
                    el('div', { className: 'youtube-privacy-title-control' },
                        el(components.TextControl, {
                            label: __('Video title (optional)', 'youtube-privacy'),
                            value: attributes.videoTitle,
                            onChange: function (value) {
                                setAttributes({ videoTitle: value });
                            },
                            help: __('Used by screen readers, e.g. \u201cPlay \u2018Your title\u2019\u201d. Not shown on the page.', 'youtube-privacy'),
                            __nextHasNoMarginBottom: true
                        })
                    ),
                    el('div', { className: 'youtube-privacy-thumbnail-control' },
                        el('p', { className: 'youtube-privacy-thumbnail-label' }, __('Custom thumbnail (optional)', 'youtube-privacy')),
                        attributes.thumbnailUrl && el('img', {
                            src: attributes.thumbnailUrl,
                            alt: '',
                            className: 'youtube-privacy-thumbnail-preview'
                        }),
                        el(blockEditor.MediaUploadCheck, {},
                            el(blockEditor.MediaUpload, {
                                onSelect: function (media) {
                                    setAttributes({ thumbnailUrl: media.url });
                                },
                                allowedTypes: ['image'],
                                render: function (obj) {
                                    return el(components.Button, {
                                        onClick: obj.open,
                                        variant: 'secondary'
                                    }, attributes.thumbnailUrl ? __('Replace thumbnail', 'youtube-privacy') : __('Select thumbnail', 'youtube-privacy'));
                                }
                            })
                        ),
                        attributes.thumbnailUrl && el(components.Button, {
                            onClick: function () {
                                setAttributes({ thumbnailUrl: '' });
                            },
                            variant: 'link',
                            isDestructive: true,
                            className: 'youtube-privacy-remove-thumbnail'
                        }, __('Remove thumbnail', 'youtube-privacy'))
                    )
                )
            );

            var body;

            if (!videoId) {
                body = el(components.Placeholder, {
                    icon: 'video-alt3',
                    label: __('YouTube Privacy Embed', 'youtube-privacy'),
                    instructions: attributes.videoUrl
                        ? __('This doesn\u2019t look like a YouTube video link. Check the URL and try again.', 'youtube-privacy')
                        : __('Paste a YouTube video link. The video only loads from YouTube after a visitor clicks it.', 'youtube-privacy')
                }, urlControl(true));
            } else {
                // Same markup and classes as the front end, so the preview matches.
                body = el('div', { className: 'youtube-embed-container', 'aria-hidden': 'true' },
                    el('img', {
                        className: 'youtube-thumbnail',
                        src: attributes.thumbnailUrl || config.defaultThumbnail,
                        alt: ''
                    }),
                    el('span', { className: 'play-button' }),
                    el('div', { className: 'privacy-notice' },
                        (config.noticeText || '') + ' ',
                        el('span', { className: 'youtube-privacy-link' }, config.linkText || '')
                    )
                );
            }

            return el(Fragment, {}, inspector, el('div', blockProps, body));
        },

        save: function () {
            return null; // Server-side rendering
        }
    });
})(window.wp);
