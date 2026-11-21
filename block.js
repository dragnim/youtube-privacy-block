
(function() {
    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var MediaUpload = wp.blockEditor.MediaUpload;
    var Button = wp.components.Button;

    registerBlockType('youtube-privacy/embed', {
        title: __('YouTube Privacy Embed'),
        icon: 'video-alt3',
        category: 'embed',
        attributes: {
            videoUrl: {
                type: 'string',
                default: ''
            },
            thumbnailUrl: {
                type: 'string',
                default: ''
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            function onVideoUrlChange(newUrl) {
                setAttributes({ videoUrl: newUrl });
            }

            function onThumbnailSelect(media) {
                setAttributes({ thumbnailUrl: media.url });
            }

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Video Settings' },
                        el(TextControl, {
                            label: 'YouTube Video URL',
                            value: attributes.videoUrl,
                            onChange: onVideoUrlChange,
                            placeholder: 'https://www.youtube.com/watch?v=...'
                        }),
                        el('div', { style: { marginTop: '16px' } },
                            el('label', { style: { display: 'block', marginBottom: '8px' } }, 'Custom Thumbnail (optional)'),
                            el(MediaUpload, {
                                onSelect: onThumbnailSelect,
                                allowedTypes: ['image'],
                                value: attributes.thumbnailUrl,
                                render: function(obj) {
                                    return el(Button, {
                                        onClick: obj.open,
                                        className: attributes.thumbnailUrl ? 'button' : 'button button-large'
                                    }, attributes.thumbnailUrl ? 'Change Thumbnail' : 'Select Thumbnail');
                                }
                            })
                        )
                    )
                ),
                el('div', { 
                    className: 'youtube-privacy-block-editor',
                    style: {
                        border: '2px dashed #ccc',
                        padding: '20px',
                        textAlign: 'center',
                        backgroundColor: '#f9f9f9'
                    }
                },
                    el('div', { className: 'dashicons dashicons-video-alt3', style: { fontSize: '48px', color: '#666' } }),
                    el('h3', {}, 'YouTube Privacy Embed'),
                    el('p', {}, attributes.videoUrl ? 'Video: ' + attributes.videoUrl : 'Enter a YouTube URL in the sidebar to get started'),
                    attributes.thumbnailUrl && el('p', {}, 'Custom thumbnail selected')
                )
            ];
        },

        save: function() {
            return null; // Server-side rendering
        }
    });
})();
