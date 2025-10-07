<?php
/**
 * Gutenberg Blocks
 */

if (!defined('ABSPATH')) {
    exit;
}

class YTES_Blocks {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'register_blocks'));
    }
    
    public function register_blocks() {
        // Register block assets
        wp_register_script(
            'ytes-blocks',
            YTES_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            YTES_VERSION,
            true
        );
        
        wp_register_style(
            'ytes-blocks-editor',
            YTES_PLUGIN_URL . 'assets/css/blocks-editor.css',
            array('wp-edit-blocks'),
            YTES_VERSION
        );
        
        wp_register_style(
            'ytes-blocks-frontend',
            YTES_PLUGIN_URL . 'assets/css/blocks-frontend.css',
            array(),
            YTES_VERSION
        );
        
        // YouTube Subscribe Block
        register_block_type('ytes/subscribe-button', array(
            'editor_script' => 'ytes-blocks',
            'editor_style' => 'ytes-blocks-editor',
            'style' => 'ytes-blocks-frontend',
            'render_callback' => array($this, 'render_subscribe_button'),
            'attributes' => array(
                'layout' => array(
                    'type' => 'string',
                    'default' => 'default'
                ),
                'showCount' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'alignment' => array(
                    'type' => 'string',
                    'default' => 'left'
                )
            )
        ));
        
        // Email Signup Block
        register_block_type('ytes/email-signup', array(
            'editor_script' => 'ytes-blocks',
            'editor_style' => 'ytes-blocks-editor',
            'style' => 'ytes-blocks-frontend',
            'render_callback' => array($this, 'render_email_signup'),
            'attributes' => array(
                'title' => array(
                    'type' => 'string',
                    'default' => 'Subscribe to Our Newsletter'
                ),
                'description' => array(
                    'type' => 'string',
                    'default' => 'Get notified about new videos!'
                ),
                'buttonText' => array(
                    'type' => 'string',
                    'default' => 'Subscribe'
                ),
                'showName' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'alignment' => array(
                    'type' => 'string',
                    'default' => 'left'
                )
            )
        ));
        
        // Social Share Block
        register_block_type('ytes/social-share', array(
            'editor_script' => 'ytes-blocks',
            'editor_style' => 'ytes-blocks-editor',
            'style' => 'ytes-blocks-frontend',
            'render_callback' => array($this, 'render_social_share'),
            'attributes' => array(
                'layout' => array(
                    'type' => 'string',
                    'default' => 'horizontal'
                ),
                'size' => array(
                    'type' => 'string',
                    'default' => 'medium'
                ),
                'alignment' => array(
                    'type' => 'string',
                    'default' => 'left'
                )
            )
        ));
        
        // CTA Button Block
        register_block_type('ytes/cta-button', array(
            'editor_script' => 'ytes-blocks',
            'editor_style' => 'ytes-blocks-editor',
            'style' => 'ytes-blocks-frontend',
            'render_callback' => array($this, 'render_cta_button'),
            'attributes' => array(
                'text' => array(
                    'type' => 'string',
                    'default' => 'Watch on YouTube'
                ),
                'videoUrl' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'style' => array(
                    'type' => 'string',
                    'default' => 'primary'
                ),
                'size' => array(
                    'type' => 'string',
                    'default' => 'medium'
                ),
                'alignment' => array(
                    'type' => 'string',
                    'default' => 'left'
                )
            )
        ));
    }
    
    /**
     * Render YouTube Subscribe Button
     */
    public function render_subscribe_button($attributes) {
        $channel_id = get_option('ytes_youtube_channel_id');
        
        if (empty($channel_id)) {
            return '<p>' . __('Please configure your YouTube channel ID in settings.', 'yt-engagement-suite') . '</p>';
        }
        
        $layout = isset($attributes['layout']) ? $attributes['layout'] : 'default';
        $show_count = isset($attributes['showCount']) ? $attributes['showCount'] : false;
        $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : 'left';
        
        $count_param = $show_count ? 'default' : 'hidden';
        $theme = get_option('ytes_subscribe_button_theme', 'default');
        
        ob_start();
        ?>
        <div class="ytes-subscribe-button ytes-align-<?php echo esc_attr($alignment); ?>" data-action="subscribe_click">
            <script src="https://apis.google.com/js/platform.js"></script>
            <div class="g-ytsubscribe" 
                 data-channelid="<?php echo esc_attr($channel_id); ?>" 
                 data-layout="<?php echo esc_attr($layout); ?>" 
                 data-count="<?php echo esc_attr($count_param); ?>"
                 data-theme="<?php echo esc_attr($theme); ?>">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render Email Signup Form
     */
    public function render_email_signup($attributes) {
        $title = isset($attributes['title']) ? $attributes['title'] : 'Subscribe to Our Newsletter';
        $description = isset($attributes['description']) ? $attributes['description'] : 'Get notified about new videos!';
        $button_text = isset($attributes['buttonText']) ? $attributes['buttonText'] : 'Subscribe';
        $show_name = isset($attributes['showName']) ? $attributes['showName'] : false;
        $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : 'left';
        
        ob_start();
        ?>
        <div class="ytes-email-signup ytes-align-<?php echo esc_attr($alignment); ?>">
            <div class="ytes-email-signup-inner">
                <?php if (!empty($title)): ?>
                    <h3 class="ytes-email-title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>
                
                <?php if (!empty($description)): ?>
                    <p class="ytes-email-description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
                
                <form class="ytes-email-form" data-action="email_signup">
                    <?php if ($show_name): ?>
                        <div class="ytes-form-field">
                            <input type="text" 
                                   name="ytes_name" 
                                   placeholder="<?php esc_attr_e('Your Name', 'yt-engagement-suite'); ?>" 
                                   class="ytes-input" />
                        </div>
                    <?php endif; ?>
                    
                    <div class="ytes-form-field">
                        <input type="email" 
                               name="ytes_email" 
                               placeholder="<?php esc_attr_e('Your Email', 'yt-engagement-suite'); ?>" 
                               required 
                               class="ytes-input" />
                    </div>
                    
                    <div class="ytes-form-field">
                        <button type="submit" class="ytes-button ytes-button-primary">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                    
                    <div class="ytes-form-message"></div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render Social Share Buttons
     */
    public function render_social_share($attributes) {
        $layout = isset($attributes['layout']) ? $attributes['layout'] : 'horizontal';
        $size = isset($attributes['size']) ? $attributes['size'] : 'medium';
        $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : 'left';
        
        $share_buttons = get_option('ytes_share_buttons', array('facebook', 'twitter', 'linkedin', 'pinterest'));
        
        $post_url = urlencode(get_permalink());
        $post_title = urlencode(get_the_title());
        
        $share_urls = array(
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $post_url,
            'twitter' => 'https://twitter.com/intent/tweet?url=' . $post_url . '&text=' . $post_title,
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $post_url,
            'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $post_url . '&description=' . $post_title,
            'reddit' => 'https://reddit.com/submit?url=' . $post_url . '&title=' . $post_title,
            'whatsapp' => 'https://api.whatsapp.com/send?text=' . $post_title . '%20' . $post_url
        );
        
        $button_labels = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'reddit' => 'Reddit',
            'whatsapp' => 'WhatsApp'
        );
        
        ob_start();
        ?>
        <div class="ytes-social-share ytes-layout-<?php echo esc_attr($layout); ?> ytes-size-<?php echo esc_attr($size); ?> ytes-align-<?php echo esc_attr($alignment); ?>">
            <?php foreach ($share_buttons as $network): ?>
                <?php if (isset($share_urls[$network])): ?>
                    <a href="<?php echo esc_url($share_urls[$network]); ?>" 
                       class="ytes-share-button ytes-share-<?php echo esc_attr($network); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       data-network="<?php echo esc_attr($network); ?>"
                       data-action="social_share">
                        <span class="ytes-share-icon">
                            <?php echo $this->get_social_icon($network); ?>
                        </span>
                        <span class="ytes-share-label"><?php echo esc_html($button_labels[$network]); ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render CTA Button
     */
    public function render_cta_button($attributes) {
        $text = isset($attributes['text']) ? $attributes['text'] : get_option('ytes_cta_button_text', 'Watch on YouTube');
        $video_url = isset($attributes['videoUrl']) ? $attributes['videoUrl'] : '';
        $style = isset($attributes['style']) ? $attributes['style'] : 'primary';
        $size = isset($attributes['size']) ? $attributes['size'] : 'medium';
        $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : 'left';
        
        // If no URL provided, try to get from post meta
        if (empty($video_url)) {
            $video_url = get_post_meta(get_the_ID(), 'youtube_video_url', true);
        }
        
        if (empty($video_url)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="ytes-cta-button-wrapper ytes-align-<?php echo esc_attr($alignment); ?>">
            <a href="<?php echo esc_url($video_url); ?>" 
               class="ytes-button ytes-button-<?php echo esc_attr($style); ?> ytes-button-<?php echo esc_attr($size); ?> ytes-cta-button"
               target="_blank"
               rel="noopener noreferrer"
               data-action="cta_click"
               data-video-url="<?php echo esc_attr($video_url); ?>">
                <svg class="ytes-youtube-icon" viewBox="0 0 24 24" width="20" height="20">
                    <path fill="currentColor" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
                <?php echo esc_html($text); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get social icon SVG
     */
    private function get_social_icon($network) {
        $icons = array(
            'facebook' => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'twitter' => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'linkedin' => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'pinterest' => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>',
            'reddit' => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg>',
            'whatsapp' => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>'
        );
        
        return isset($icons[$network]) ? $icons[$network] : '';
    }
}
