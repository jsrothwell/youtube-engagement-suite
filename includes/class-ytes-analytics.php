<?php
/**
 * Analytics Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class YTES_Analytics {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Only initialize if analytics is enabled
        if (!get_option('ytes_enable_analytics')) {
            return;
        }
        
        add_action('wp_footer', array($this, 'inject_tracking_script'));
    }
    
    /**
     * Inject tracking script into footer
     */
    public function inject_tracking_script() {
        if (!get_option('ytes_enable_analytics')) {
            return;
        }
        ?>
        <script>
        (function($) {
            'use strict';
            
            // Track subscribe button clicks
            $(document).on('click', '[data-action="subscribe_click"]', function() {
                ytesTrackEvent('subscribe_click', {
                    video_id: $(this).data('video-id') || null
                });
            });
            
            // Track email signups (handled in AJAX but we can track attempts)
            $(document).on('submit', '[data-action="email_signup"]', function() {
                ytesTrackEvent('email_signup_attempt', {
                    source: 'form'
                });
            });
            
            // Track social share clicks
            $(document).on('click', '[data-action="social_share"]', function(e) {
                var network = $(this).data('network');
                ytesTrackEvent('social_share', {
                    network: network,
                    video_id: $(this).data('video-id') || null
                });
            });
            
            // Track CTA button clicks
            $(document).on('click', '[data-action="cta_click"]', function() {
                ytesTrackEvent('cta_click', {
                    video_url: $(this).data('video-url'),
                    video_id: $(this).data('video-id') || null
                });
            });
            
            // Helper function to track events
            function ytesTrackEvent(actionType, data) {
                if (!window.ytesData || !window.ytesData.ajaxUrl) {
                    return;
                }
                
                $.ajax({
                    url: window.ytesData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'ytes_track_event',
                        nonce: window.ytesData.nonce,
                        action_type: actionType,
                        video_id: data.video_id || null,
                        video_url: data.video_url || null,
                        network: data.network || null,
                        source: data.source || null
                    }
                });
            }
            
        })(jQuery);
        </script>
        <?php
    }
}
