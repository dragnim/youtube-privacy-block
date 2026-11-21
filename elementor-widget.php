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
        return ['youtube-privacy-frontend'];
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
                'description' => esc_html__('Supports watch, youtu.be, Shorts and live links. Start times (t=) are kept.', 'youtube-privacy'),
                'options' => false,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'video_title',
            [
                'label' => esc_html__('Video title (optional)', 'youtube-privacy'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'description' => esc_html__('Used by screen readers, e.g. “Play ‘Your title’”. Not shown on the page.', 'youtube-privacy'),
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
        $video_title = isset($settings['video_title']) ? $settings['video_title'] : '';
        
        // Get the plugin instance to use the shared render method
        global $youtube_privacy_plugin;
        
        if ($youtube_privacy_plugin && method_exists($youtube_privacy_plugin, 'render_youtube_embed')) {
            echo $youtube_privacy_plugin->render_youtube_embed($video_url, $thumbnail_url, '', false, $video_title);
        } else {
            echo '<p>YouTube Privacy Plugin not available.</p>';
        }
    }

    protected function content_template() {
        global $youtube_privacy_plugin;

        if ($youtube_privacy_plugin && method_exists($youtube_privacy_plugin, 'get_settings')) {
            $settings = $youtube_privacy_plugin->get_settings();
        } else {
            $settings = array(
                'privacy_url' => YOUTUBE_PRIVACY_DEFAULT_URL,
                'notice_text' => __('Click to load video from YouTube. This will enable YouTube tracking.', 'youtube-privacy'),
                'link_text'   => __('YouTube Privacy Information', 'youtube-privacy'),
            );
        }

        $default_thumbnail = plugin_dir_url(__FILE__) . 'default-thumbnail.jpg';
        ?>
        <#
        var videoUrl = ( settings.video_url && settings.video_url.url ) ? settings.video_url.url : '';
        var thumbnailUrl = ( settings.custom_thumbnail && settings.custom_thumbnail.url ) ? settings.custom_thumbnail.url : '<?php echo esc_js($default_thumbnail); ?>';
        var videoTitle = ( settings.video_title || '' ).trim();
        /* translators: %s: video title */
        var playLabel = videoTitle ? '<?php echo esc_js(__('Play “%s”', 'youtube-privacy')); ?>'.replace( '%s', function () { return videoTitle; } ) : '<?php echo esc_js(__('Play YouTube video', 'youtube-privacy')); ?>';
        var isValid = ( typeof window.youtubePrivacyGetVideoId === 'function' ) ? !! window.youtubePrivacyGetVideoId( videoUrl ) : true;
        #>
        <# if ( ! videoUrl ) { #>
            <p class="youtube-privacy-editor-message"><?php echo esc_html__('Please enter a YouTube video URL.', 'youtube-privacy'); ?></p>
        <# } else if ( ! isValid ) { #>
            <p class="youtube-privacy-editor-message"><?php echo esc_html__('Invalid YouTube URL. Please use a valid YouTube video link.', 'youtube-privacy'); ?></p>
        <# } else { #>
            <div class="youtube-embed-container">
                <img class="youtube-thumbnail" src="{{ thumbnailUrl }}" alt="">
                <button type="button" class="play-button" aria-label="{{ playLabel }}" aria-describedby="youtube-privacy-notice-{{ view.getID() }}"></button>
                <div class="privacy-notice">
                    <span id="youtube-privacy-notice-{{ view.getID() }}"><?php echo esc_html($settings['notice_text']); ?></span>
                    <a href="<?php echo esc_url($settings['privacy_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($settings['link_text']); ?><span class="youtube-privacy-sr-only"> <?php echo esc_html__('(opens in a new tab)', 'youtube-privacy'); ?></span></a>
                </div>
            </div>
        <# } #>
        <?php
    }
}
