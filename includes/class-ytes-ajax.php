<?php
/**
 * AJAX Handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class YTES_Ajax {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_ytes_email_signup', array($this, 'handle_email_signup'));
        add_action('wp_ajax_nopriv_ytes_email_signup', array($this, 'handle_email_signup'));
        
        add_action('wp_ajax_ytes_track_event', array($this, 'handle_track_event'));
        add_action('wp_ajax_nopriv_ytes_track_event', array($this, 'handle_track_event'));
        
        add_action('wp_ajax_ytes_confirm_subscription', array($this, 'handle_confirm_subscription'));
        add_action('wp_ajax_nopriv_ytes_confirm_subscription', array($this, 'handle_confirm_subscription'));
        
        add_action('wp_ajax_ytes_unsubscribe', array($this, 'handle_unsubscribe'));
        add_action('wp_ajax_nopriv_ytes_unsubscribe', array($this, 'handle_unsubscribe'));
    }
    
    /**
     * Handle email signup
     */
    public function handle_email_signup() {
        check_ajax_referer('ytes_nonce', 'nonce');
        
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array(
                'message' => __('Please provide a valid email address.', 'yt-engagement-suite')
            ));
        }
        
        $db = YTES_Database::get_instance();
        
        // Check if email already exists
        if ($db->subscriber_exists($email)) {
            $subscriber = $db->get_subscriber($email);
            
            if ($subscriber->status === 'active') {
                wp_send_json_error(array(
                    'message' => __('This email is already subscribed.', 'yt-engagement-suite')
                ));
            } elseif ($subscriber->status === 'unsubscribed') {
                // Reactivate subscription
                $db->update_subscriber_status($email, 'active');
                wp_send_json_success(array(
                    'message' => __('Welcome back! Your subscription has been reactivated.', 'yt-engagement-suite')
                ));
            } elseif ($subscriber->status === 'pending') {
                wp_send_json_error(array(
                    'message' => __('Please check your email to confirm your subscription.', 'yt-engagement-suite')
                ));
            }
        }
        
        // Add new subscriber
        $data = array(
            'name' => $name,
            'source' => isset($_POST['source']) ? sanitize_text_field($_POST['source']) : 'website'
        );
        
        $subscriber_id = $db->add_subscriber($email, $data);
        
        if ($subscriber_id) {
            // Log analytics
            if (get_option('ytes_enable_analytics')) {
                $db->log_analytics('email_signup', array(
                    'additional_data' => array(
                        'subscriber_id' => $subscriber_id,
                        'source' => $data['source']
                    )
                ));
            }
            
            // Send confirmation email if double opt-in is enabled
            if (get_option('ytes_email_double_optin')) {
                $this->send_confirmation_email($email, $subscriber_id);
                $message = __('Thank you! Please check your email to confirm your subscription.', 'yt-engagement-suite');
            } else {
                $message = get_option('ytes_success_message', __('Thank you for subscribing!', 'yt-engagement-suite'));
            }
            
            // Send notification to admin
            $this->send_admin_notification($email, $name);
            
            wp_send_json_success(array(
                'message' => $message
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('An error occurred. Please try again.', 'yt-engagement-suite')
            ));
        }
    }
    
    /**
     * Handle analytics event tracking
     */
    public function handle_track_event() {
        check_ajax_referer('ytes_nonce', 'nonce');
        
        if (!get_option('ytes_enable_analytics')) {
            wp_send_json_success();
            return;
        }
        
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        $video_id = isset($_POST['video_id']) ? sanitize_text_field($_POST['video_id']) : null;
        $video_url = isset($_POST['video_url']) ? esc_url_raw($_POST['video_url']) : null;
        $network = isset($_POST['network']) ? sanitize_text_field($_POST['network']) : null;
        
        $db = YTES_Database::get_instance();
        
        $additional_data = array();
        if ($video_url) {
            $additional_data['video_url'] = $video_url;
        }
        if ($network) {
            $additional_data['network'] = $network;
        }
        
        $db->log_analytics($action_type, array(
            'video_id' => $video_id,
            'additional_data' => $additional_data
        ));
        
        wp_send_json_success();
    }
    
    /**
     * Handle subscription confirmation
     */
    public function handle_confirm_subscription() {
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
        
        if (empty($token) || empty($email)) {
            wp_die(__('Invalid confirmation link.', 'yt-engagement-suite'));
        }
        
        // Verify token
        $expected_token = $this->generate_confirmation_token($email);
        if ($token !== $expected_token) {
            wp_die(__('Invalid confirmation link.', 'yt-engagement-suite'));
        }
        
        $db = YTES_Database::get_instance();
        $subscriber = $db->get_subscriber($email);
        
        if (!$subscriber) {
            wp_die(__('Subscriber not found.', 'yt-engagement-suite'));
        }
        
        if ($subscriber->status === 'active') {
            wp_die(__('Your subscription is already confirmed.', 'yt-engagement-suite'));
        }
        
        $db->update_subscriber_status($email, 'active');
        
        // Redirect to home with success message
        wp_redirect(add_query_arg('ytes_confirmed', '1', home_url()));
        exit;
    }
    
    /**
     * Handle unsubscribe
     */
    public function handle_unsubscribe() {
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
        
        if (empty($token) || empty($email)) {
            wp_die(__('Invalid unsubscribe link.', 'yt-engagement-suite'));
        }
        
        // Verify token
        $expected_token = $this->generate_confirmation_token($email);
        if ($token !== $expected_token) {
            wp_die(__('Invalid unsubscribe link.', 'yt-engagement-suite'));
        }
        
        $db = YTES_Database::get_instance();
        $db->update_subscriber_status($email, 'unsubscribed');
        
        // Redirect to home with success message
        wp_redirect(add_query_arg('ytes_unsubscribed', '1', home_url()));
        exit;
    }
    
    /**
     * Send confirmation email
     */
    private function send_confirmation_email($email, $subscriber_id) {
        $token = $this->generate_confirmation_token($email);
        $confirm_url = add_query_arg(array(
            'action' => 'ytes_confirm_subscription',
            'token' => $token,
            'email' => urlencode($email)
        ), admin_url('admin-ajax.php'));
        
        $subject = sprintf(__('Please confirm your subscription to %s', 'yt-engagement-suite'), get_bloginfo('name'));
        
        $message = sprintf(
            __("Hi!\n\nThank you for subscribing to %s.\n\nPlease confirm your subscription by clicking the link below:\n\n%s\n\nIf you did not request this subscription, please ignore this email.\n\nThanks,\n%s", 'yt-engagement-suite'),
            get_bloginfo('name'),
            $confirm_url,
            get_bloginfo('name')
        );
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Send admin notification
     */
    private function send_admin_notification($email, $name) {
        $admin_email = get_option('ytes_email_notification');
        
        if (empty($admin_email)) {
            return;
        }
        
        $subject = sprintf(__('New subscriber: %s', 'yt-engagement-suite'), $email);
        
        $message = sprintf(
            __("You have a new subscriber!\n\nEmail: %s\nName: %s\nTime: %s\n\nView all subscribers: %s", 'yt-engagement-suite'),
            $email,
            $name ? $name : '(not provided)',
            current_time('mysql'),
            admin_url('admin.php?page=ytes-subscribers')
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Generate confirmation token
     */
    private function generate_confirmation_token($email) {
        return hash_hmac('sha256', $email, wp_salt('auth'));
    }
}
