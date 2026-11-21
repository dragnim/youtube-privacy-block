<?php
/**
 * Plugin Name:       YouTube Privacy Block & Elementor Widget
 * Description:       A privacy-friendly YouTube embed block for Gutenberg and Elementor widget
 * Version:           1.2.1
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            MikeM
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       youtube-privacy
 * Domain Path:       /languages
 * Update URI:        false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

define('YOUTUBE_PRIVACY_VERSION', '1.2.1');
define('YOUTUBE_PRIVACY_OPTION', 'youtube_privacy_settings');
define('YOUTUBE_PRIVACY_DEFAULT_URL', 'https://policies.google.com/privacy');

class YouTubePrivacyPlugin {

    public function __construct() {
        add_action('init', array($this, 'load_textdomain'), 1);
        add_action('init', array($this, 'register_assets'), 5);
        add_action('init', array($this, 'register_block'));

        // Elementor hooks
        add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_elementor_editor_scripts'));

        // Settings
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    public function load_textdomain() {
        load_plugin_textdomain('youtube-privacy', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /* ---------------------------------------------------------------------
     * Settings
     * ------------------------------------------------------------------ */

    public function get_default_settings() {
        return array(
            'privacy_url' => YOUTUBE_PRIVACY_DEFAULT_URL,
            'notice_text' => __('Click to load video from YouTube. This will enable YouTube tracking.', 'youtube-privacy'),
            'link_text'   => __('YouTube Privacy Information', 'youtube-privacy'),
        );
    }

    public function get_settings() {
        $defaults = $this->get_default_settings();
        $saved    = get_option(YOUTUBE_PRIVACY_OPTION);
        $settings = $defaults;

        if (is_array($saved)) {
            foreach ($defaults as $key => $default) {
                if (isset($saved[$key]) && $saved[$key] !== '') {
                    $settings[$key] = $saved[$key];
                }
            }
        }

        return $settings;
    }

    public function get_privacy_url() {
        $settings = $this->get_settings();
        return $settings['privacy_url'];
    }

    public function add_settings_page() {
        add_options_page(
            __('YouTube Privacy', 'youtube-privacy'),
            __('YouTube Privacy', 'youtube-privacy'),
            'manage_options',
            'youtube-privacy',
            array($this, 'render_settings_page')
        );
    }

    public function add_settings_link($links) {
        $url = admin_url('options-general.php?page=youtube-privacy');
        array_unshift($links, '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'youtube-privacy') . '</a>');
        return $links;
    }

    public function register_settings() {
        register_setting('youtube_privacy', YOUTUBE_PRIVACY_OPTION, array(
            'type'              => 'array',
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));

        add_settings_section('youtube_privacy_main', '', '__return_false', 'youtube-privacy');

        $defaults = $this->get_default_settings();

        add_settings_field(
            'notice_text',
            __('Notice text', 'youtube-privacy'),
            array($this, 'render_text_field'),
            'youtube-privacy',
            'youtube_privacy_main',
            array(
                'label_for'   => 'youtube_privacy_notice_text',
                'key'         => 'notice_text',
                'type'        => 'text',
                'placeholder' => $defaults['notice_text'],
                'description' => __('Shown on every embed before the video loads. Leave blank to use the default.', 'youtube-privacy'),
            )
        );

        add_settings_field(
            'link_text',
            __('Link text', 'youtube-privacy'),
            array($this, 'render_text_field'),
            'youtube-privacy',
            'youtube_privacy_main',
            array(
                'label_for'   => 'youtube_privacy_link_text',
                'key'         => 'link_text',
                'type'        => 'text',
                'placeholder' => $defaults['link_text'],
                'description' => __('Leave blank to use the default.', 'youtube-privacy'),
            )
        );

        add_settings_field(
            'privacy_url',
            __('Privacy policy link', 'youtube-privacy'),
            array($this, 'render_text_field'),
            'youtube-privacy',
            'youtube_privacy_main',
            array(
                'label_for'   => 'youtube_privacy_privacy_url',
                'key'         => 'privacy_url',
                'type'        => 'url',
                'placeholder' => YOUTUBE_PRIVACY_DEFAULT_URL,
                'description' => __('Leave blank to use the default Google privacy policy.', 'youtube-privacy'),
            )
        );
    }

    public function sanitize_settings($input) {
        $input = is_array($input) ? $input : array();

        return array(
            'privacy_url' => isset($input['privacy_url']) ? esc_url_raw(trim($input['privacy_url'])) : '',
            'notice_text' => isset($input['notice_text']) ? sanitize_text_field($input['notice_text']) : '',
            'link_text'   => isset($input['link_text']) ? sanitize_text_field($input['link_text']) : '',
        );
    }

    public function render_text_field($args) {
        $saved = get_option(YOUTUBE_PRIVACY_OPTION);
        $value = (is_array($saved) && isset($saved[$args['key']])) ? $saved[$args['key']] : '';

        printf(
            '<input type="%1$s" id="%2$s" name="%3$s[%4$s]" value="%5$s" class="regular-text" placeholder="%6$s" aria-describedby="%2$s-description"><p class="description" id="%2$s-description">%7$s</p>',
            esc_attr($args['type']),
            esc_attr($args['label_for']),
            esc_attr(YOUTUBE_PRIVACY_OPTION),
            esc_attr($args['key']),
            esc_attr($value),
            esc_attr($args['placeholder']),
            esc_html($args['description'])
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('YouTube Privacy', 'youtube-privacy'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('youtube_privacy');
                do_settings_sections('youtube-privacy');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /* ---------------------------------------------------------------------
     * Assets
     * ------------------------------------------------------------------ */

    public function register_assets() {
        $url = plugin_dir_url(__FILE__);

        wp_register_script(
            'youtube-privacy-frontend',
            $url . 'frontend.js',
            array(),
            YOUTUBE_PRIVACY_VERSION,
            true
        );

        // Legacy handle, kept as an alias for anything that depended on it.
        wp_register_script('youtube-privacy-elementor', false, array('youtube-privacy-frontend'), YOUTUBE_PRIVACY_VERSION, true);

        wp_register_style(
            'youtube-privacy-block-style',
            $url . 'style.css',
            array(),
            YOUTUBE_PRIVACY_VERSION
        );

        // Shared URL parser for the block editor and Elementor editor (mirrors extract_video_id()).
        wp_register_script(
            'youtube-privacy-url-parser',
            $url . 'url-parser.js',
            array(),
            YOUTUBE_PRIVACY_VERSION
        );

        wp_register_script(
            'youtube-privacy-block-editor',
            $url . 'block.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'youtube-privacy-url-parser'),
            YOUTUBE_PRIVACY_VERSION
        );

        wp_register_style(
            'youtube-privacy-block-editor-style',
            $url . 'editor.css',
            array(),
            YOUTUBE_PRIVACY_VERSION
        );

        $settings = $this->get_settings();
        wp_add_inline_script(
            'youtube-privacy-block-editor',
            'window.youtubePrivacyEditor = ' . wp_json_encode(array(
                'defaultThumbnail' => $url . 'default-thumbnail.jpg',
                'noticeText'       => $settings['notice_text'],
                'linkText'         => $settings['link_text'],
            )) . ';',
            'before'
        );
        wp_set_script_translations('youtube-privacy-block-editor', 'youtube-privacy', plugin_dir_path(__FILE__) . 'languages');
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_script('youtube-privacy-frontend');
        wp_enqueue_style('youtube-privacy-block-style');
    }

    /* ---------------------------------------------------------------------
     * Block editor
     * ------------------------------------------------------------------ */

    public function register_block() {
        register_block_type(__DIR__, array(
            'render_callback' => array($this, 'render_block'),
        ));
    }

    public function render_block($attributes) {
        $video_url     = isset($attributes['videoUrl']) ? $attributes['videoUrl'] : '';
        $thumbnail_url = isset($attributes['thumbnailUrl']) ? $attributes['thumbnailUrl'] : '';
        $video_title   = isset($attributes['videoTitle']) ? $attributes['videoTitle'] : '';

        return $this->render_youtube_embed($video_url, $thumbnail_url, '', true, $video_title);
    }

    /* ---------------------------------------------------------------------
     * Elementor
     * ------------------------------------------------------------------ */

    public function enqueue_elementor_editor_scripts() {
        wp_enqueue_script('youtube-privacy-url-parser');
    }

    public function register_elementor_widget($widgets_manager) {
        require_once __DIR__ . '/elementor-widget.php';
        $widgets_manager->register(new \YouTube_Privacy_Elementor_Widget());
    }

    /* ---------------------------------------------------------------------
     * Rendering (shared by block and Elementor)
     * ------------------------------------------------------------------ */

    private function editor_message($message) {
        // Only editors see setup messages; visitors see nothing.
        if (!current_user_can('edit_posts')) {
            return '';
        }
        return '<p class="youtube-privacy-editor-message">' . esc_html($message) . '</p>';
    }

    public function render_youtube_embed($video_url, $thumbnail_url = '', $additional_classes = '', $is_block = false, $video_title = '') {
        if (empty($video_url)) {
            return $this->editor_message(__('Please enter a YouTube video URL.', 'youtube-privacy'));
        }

        $video_id = $this->extract_video_id($video_url);

        if (!$video_id) {
            return $this->editor_message(__('Invalid YouTube URL. Please use a valid YouTube video link.', 'youtube-privacy'));
        }

        $this->enqueue_frontend_assets();

        if (empty($thumbnail_url)) {
            $thumbnail_url = plugin_dir_url(__FILE__) . 'default-thumbnail.jpg';
        }

        $settings = $this->get_settings();
        $start    = $this->extract_start_time($video_url);

        $video_title = trim(wp_strip_all_tags((string) $video_title));

        if ($video_title !== '') {
            /* translators: %s: video title */
            $play_label   = sprintf(__('Play “%s”', 'youtube-privacy'), $video_title);
            $iframe_title = $video_title;
        } else {
            $play_label   = __('Play YouTube video', 'youtube-privacy');
            $iframe_title = __('YouTube video player', 'youtube-privacy');
        }

        $attributes = array(
            'data-video-id'     => $video_id,
            'data-iframe-title' => $iframe_title,
        );
        if ($start > 0) {
            $attributes['data-start'] = (string) $start;
        }

        if ($is_block && function_exists('get_block_wrapper_attributes')) {
            // Adds block classes (custom class, alignment) and spacing styles to the container itself.
            $attributes['class'] = 'youtube-embed-container';
            $attribute_string = get_block_wrapper_attributes($attributes);
        } else {
            $classes = trim('youtube-embed-container ' . $additional_classes);
            $attribute_string = 'class="' . esc_attr($classes) . '"';
            foreach ($attributes as $name => $value) {
                $attribute_string .= ' ' . $name . '="' . esc_attr($value) . '"';
            }
        }

        // Random suffix keeps IDs unique even when Elementor caches widget HTML.
        $notice_id = 'youtube-privacy-notice-' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 12);

        return sprintf(
            '<div %1$s><img class="youtube-thumbnail" src="%2$s" alt="" loading="lazy" decoding="async"><button type="button" class="play-button" aria-label="%3$s" aria-describedby="%8$s"></button><div class="privacy-notice"><span id="%8$s">%4$s</span> <a href="%5$s" target="_blank" rel="noopener noreferrer">%6$s<span class="youtube-privacy-sr-only"> %7$s</span></a></div></div>',
            $attribute_string,
            esc_url($thumbnail_url),
            esc_attr($play_label),
            esc_html($settings['notice_text']),
            esc_url($settings['privacy_url']),
            esc_html($settings['link_text']),
            esc_html__('(opens in a new tab)', 'youtube-privacy'),
            esc_attr($notice_id)
        );
    }

    /* ---------------------------------------------------------------------
     * URL parsing
     * ------------------------------------------------------------------ */

    private function parse_youtube_url($url) {
        $url = trim((string) $url);

        if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = wp_parse_url($url);
        if (empty($parts['host'])) {
            return false;
        }

        $parts['host'] = strtolower(preg_replace('/^(www\.|m\.|music\.)/i', '', $parts['host']));
        $parts['path'] = isset($parts['path']) ? $parts['path'] : '';

        $query = array();
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $parts['query_args'] = $query;

        return $parts;
    }

    public function extract_video_id($url) {
        $url = trim((string) $url);

        // A bare 11-character video ID
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
            return $url;
        }

        $parts = $this->parse_youtube_url($url);

        if ($parts) {
            $id = '';

            if ($parts['host'] === 'youtu.be') {
                $id = ltrim($parts['path'], '/');
            } elseif (in_array($parts['host'], array('youtube.com', 'youtube-nocookie.com'), true)) {
                if ($parts['path'] === '/watch' && isset($parts['query_args']['v'])) {
                    $id = $parts['query_args']['v'];
                } elseif (preg_match('#^/(embed|v|shorts|live)/([^/?]+)#', $parts['path'], $m)) {
                    $id = $m[2];
                }
            }

            $id = is_string($id) ? explode('/', $id)[0] : '';
            if (preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
                return $id;
            }
        }

        // Fallback for URLs with extra text around them (accepted by earlier versions).
        // youtube.com / youtu.be must be the real host (at the start, after whitespace or after "://"),
        // and the ID must be exactly 11 characters.
        $patterns = array(
            '~(?:^(?://)?|[\s"\'(<]|://)(?:(?:www|m|music)\.)?youtube(?:-nocookie)?\.com/(?:watch\?(?:[^\s#]*&)?v=|embed/|v/|shorts/|live/)([A-Za-z0-9_-]{11})(?![A-Za-z0-9_-])~i',
            '~(?:^(?://)?|[\s"\'(<]|://)youtu\.be/([A-Za-z0-9_-]{11})(?![A-Za-z0-9_-])~i',
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return false;
    }

    public function extract_start_time($url) {
        $parts = $this->parse_youtube_url($url);
        if (!$parts) {
            return 0;
        }

        $value = '';
        if (isset($parts['query_args']['t'])) {
            $value = $parts['query_args']['t'];
        } elseif (isset($parts['query_args']['start'])) {
            $value = $parts['query_args']['start'];
        } elseif (!empty($parts['fragment']) && preg_match('/(?:^|&)t=([^&]+)/', $parts['fragment'], $m)) {
            $value = $m[1];
        }

        if (!is_string($value) || $value === '') {
            return 0;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        // Formats like 90s, 1m30s, 1h2m3s
        if (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/i', $value, $m) && $value !== '') {
            $h = isset($m[1]) ? (int) $m[1] : 0;
            $min = isset($m[2]) ? (int) $m[2] : 0;
            $s = isset($m[3]) ? (int) $m[3] : 0;
            return $h * 3600 + $min * 60 + $s;
        }

        return 0;
    }
}

// Initialize the plugin
$youtube_privacy_plugin = new YouTubePrivacyPlugin();
