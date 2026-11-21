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
?>