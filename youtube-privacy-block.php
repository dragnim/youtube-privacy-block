<?php
/**
 * Plugin Name: YouTube Privacy Block & Elementor Widget
 * Description: A privacy-friendly YouTube embed block for Gutenberg and Elementor widget
 * Version: 1.1.0
 * Author: MikeM
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class YouTubePrivacyPlugin {

    public function __construct() {
        add_action('init', array($this, 'register_block'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));

        // Elementor hooks
        add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
        add_action('elementor/frontend/after_register_scripts', array($this, 'register_elementor_scripts'));
    }

    // Existing Gutenberg block functionality
    public function register_block() {
        // Register the block
        register_block_type('youtube-privacy/embed', array(
            'editor_script' => 'youtube-privacy-block-editor',
            'editor_style' => 'youtube-privacy-block-editor-style',
            'style' => 'youtube-privacy-block-style',
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'videoUrl' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'thumbnailUrl' => array(
                    'type' => 'string',
                    'default' => ''
                )
            )
        ));

        // Enqueue editor scripts
        wp_register_script(
            'youtube-privacy-block-editor',
            plugin_dir_url(__FILE__) . 'block.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            '1.0.0'
        );

        // Enqueue editor styles
        wp_register_style(
            'youtube-privacy-block-editor-style',
            plugin_dir_url(__FILE__) . 'editor.css',
            array('wp-edit-blocks'),
            '1.0.0'
        );

        // Enqueue frontend styles
        wp_register_style(
            'youtube-privacy-block-style',
            plugin_dir_url(__FILE__) . 'style.css',
            array(),
            '1.0.0'
        );
    }

    public function enqueue_frontend_scripts() {
        wp_enqueue_script(
            'youtube-privacy-frontend',
            plugin_dir_url(__FILE__) . 'frontend.js',
            array(),
            '1.0.0',
            true
        );
    }

    // Elementor functionality
    public function register_elementor_widget($widgets_manager) {
        require_once(__DIR__ . '/elementor-widget.php');
        $widgets_manager->register(new \YouTube_Privacy_Elementor_Widget());
    }

    public function register_elementor_scripts() {
        wp_register_script(
            'youtube-privacy-elementor',
            plugin_dir_url(__FILE__) . 'frontend.js',
            array(),
            '1.0.0',
            true
        );
    }

    // Shared render method used by both Gutenberg and Elementor
    public function render_youtube_embed($video_url, $thumbnail_url = '', $additional_classes = '') {
        if (empty($video_url)) {
            return '<p>Please enter a YouTube video URL.</p>';
        }

        // Extract video ID from URL
        $video_id = $this->extract_video_id($video_url);

        if (!$video_id) {
            return '<p>Invalid YouTube URL. Please use a valid YouTube video link.</p>';
        }

        // Use default thumbnail if none provided
        if (empty($thumbnail_url)) {
            $thumbnail_url = plugin_dir_url(__FILE__) . 'default-thumbnail.jpg';
        }

        return sprintf(
            '<div class="youtube-embed-container %s" data-video-id="%s">
                <img class="youtube-thumbnail" src="%s" alt="Video thumbnail">
                <div class="play-button"></div>
                <div class="privacy-notice">
                    Click to load video from YouTube. This will enable YouTube tracking. 
                    <a href="https://support.google.com/youtube/answer/10364219" target="_blank">YouTube Privacy Information</a>
                </div>
            </div>',
            esc_attr($additional_classes),
            esc_attr($video_id),
            esc_url($thumbnail_url)
        );
    }

    // Gutenberg block render callback
    public function render_block($attributes) {
        $video_url = isset($attributes['videoUrl']) ? $attributes['videoUrl'] : '';
        $thumbnail_url = isset($attributes['thumbnailUrl']) ? $attributes['thumbnailUrl'] : '';
        $custom_class = isset($attributes['className']) ? $attributes['className'] : '';

        return $this->render_youtube_embed($video_url, $thumbnail_url, $custom_class);
    }

    public function extract_video_id($url) {
        // Handle various YouTube URL formats
        $patterns = array(
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/'
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return false;
    }
}

// Initialize the plugin
$youtube_privacy_plugin = new YouTubePrivacyPlugin();

// Create block.js file content
function create_block_js_file() {
    $js_content = <<<'EOD'
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

            var editorClassName = 'youtube-privacy-block-editor' + (attributes.className ? ' ' + attributes.className : '');

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
                    className: editorClassName,
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
EOD;

    file_put_contents(plugin_dir_path(__FILE__) . 'block.js', $js_content);
}

// Create elementor-widget.php file
function create_elementor_widget_file() {
    $widget_content = <<<'EOD'
<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class YouTube_Privacy_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'youtube_privacy_embed';
    }

    public function get_title() {
        return esc_html__('YouTube Privacy Embed', 'youtube-privacy');
    }

    public function get_icon() {
        return 'eicon-youtube';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_keywords() {
        return ['youtube', 'video', 'privacy', 'embed'];
    }

    public function get_script_depends() {
        return ['youtube-privacy-elementor'];
    }

    public function get_style_depends() {
        return ['youtube-privacy-block-style'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Video Content', 'youtube-privacy'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'video_url',
            [
                'label' => esc_html__('YouTube Video URL', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://www.youtube.com/watch?v=...', 'youtube-privacy'),
                'description' => esc_html__('Enter the full YouTube video URL', 'youtube-privacy'),
            ]
        );

        $this->add_control(
            'custom_thumbnail',
            [
                'label' => esc_html__('Custom Thumbnail', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'description' => esc_html__('Upload a custom thumbnail image (optional)', 'youtube-privacy'),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'youtube-privacy'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => esc_html__('Border Radius', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .youtube-embed-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'label' => esc_html__('Box Shadow', 'youtube-privacy'),
                'selector' => '{{WRAPPER}} .youtube-embed-container',
            ]
        );

        $this->add_control(
            'play_button_color',
            [
                'label' => esc_html__('Play Button Color', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .play-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'privacy_notice_bg',
            [
                'label' => esc_html__('Privacy Notice Background', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .privacy-notice' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'privacy_notice_color',
            [
                'label' => esc_html__('Privacy Notice Text Color', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .privacy-notice' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $video_url = isset($settings['video_url']['url']) ? $settings['video_url']['url'] : '';
        $thumbnail_url = isset($settings['custom_thumbnail']['url']) ? $settings['custom_thumbnail']['url'] : '';

        // Get the plugin instance to use the shared render method
        global $youtube_privacy_plugin;

        if ($youtube_privacy_plugin && method_exists($youtube_privacy_plugin, 'render_youtube_embed')) {
            echo $youtube_privacy_plugin->render_youtube_embed($video_url, $thumbnail_url);
        } else {
            echo '<p>YouTube Privacy Plugin not available.</p>';
        }
    }

    protected function content_template() {
        ?>
        <#
        var videoUrl = settings.video_url.url || '';
        var thumbnailUrl = settings.custom_thumbnail.url || '';
        #>
        <div class="youtube-embed-container" style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0; background: #000; border-radius: 5px;">
            <# if (thumbnailUrl) { #>
                <img src="{{{ thumbnailUrl }}}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" alt="Video thumbnail">
            <# } else { #>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #666; font-size: 18px;">
                    <i class="eicon-youtube" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                    <# if (videoUrl) { #>
                        YouTube Video Preview
                    <# } else { #>
                        Enter YouTube URL
                    <# } #>
                </div>
            <# } #>
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80px; height: 56px; background: rgba(255, 0, 0, 0.9); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <div style="width: 0; height: 0; border-left: 16px solid white; border-top: 10px solid transparent; border-bottom: 10px solid transparent; margin-left: 4px;"></div>
            </div>
            <div style="position: absolute; bottom: 10px; left: 10px; right: 10px; background: rgba(0, 0, 0, 0.8); color: white; padding: 8px 12px; border-radius: 4px; font-size: 14px;">
                Click to load video from YouTube. This will enable YouTube tracking. 
                <a href="https://support.google.com/youtube/answer/10364219" target="_blank" style="color: #ccc; text-decoration: underline;">YouTube Privacy Information</a>
            </div>
        </div>
        <?php
    }
}
EOD;

    file_put_contents(plugin_dir_path(__FILE__) . 'elementor-widget.php', $widget_content);
}

// Create CSS files
function create_css_files() {
    $style_css = <<<'EOD'
.youtube-embed-container {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%;
    height: 0;
    background: #000;
    cursor: pointer;
    overflow: hidden;
    border-radius: 5px;
}

.youtube-thumbnail {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

.youtube-thumbnail:hover {
    opacity: 0.8;
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80px;
    height: 56px;
    background: rgba(255, 0, 0, 0.9);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.play-button:hover {
    background: #ff0000;
    transform: translate(-50%, -50%) scale(1.1);
}

.play-button::after {
    content: '';
    width: 0;
    height: 0;
    border-left: 16px solid white;
    border-top: 10px solid transparent;
    border-bottom: 10px solid transparent;
    margin-left: 4px;
}

.privacy-notice {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-family: sans-serif;
    opacity: 1;
}

.privacy-notice a {
    color: #ccc;
    text-decoration: underline;
}
EOD;

    $editor_css = <<<'EOD'
.youtube-privacy-block-editor {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    background-color: #f9f9f9;
}
EOD;

    file_put_contents(plugin_dir_path(__FILE__) . 'style.css', $style_css);
    file_put_contents(plugin_dir_path(__FILE__) . 'editor.css', $editor_css);
}

// Create frontend JavaScript
function create_frontend_js() {
    $js_content = <<<'EOD'
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
EOD;

    file_put_contents(plugin_dir_path(__FILE__) . 'frontend.js', $js_content);
}

// Hook to create files when plugin is activated
register_activation_hook(__FILE__, function() {
    create_block_js_file();
    create_elementor_widget_file();
    create_css_files();
    create_frontend_js();
});