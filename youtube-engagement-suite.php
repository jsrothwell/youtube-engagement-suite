<?php
/**
 * Plugin Name: YouTube Engagement Suite
 * Plugin URI: https://yoursite.com
 * Description: Comprehensive engagement and conversion tools for YouTube content integration
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: yt-engagement-suite
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YTES_VERSION', '1.0.0');
define('YTES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YTES_PLUGIN_URL', plugin_dir_url(__FILE__));

class YouTube_Engagement_Suite {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once YTES_PLUGIN_DIR . 'includes/class-ytes-database.php';
        require_once YTES_PLUGIN_DIR . 'includes/class-ytes-admin.php';
        require_once YTES_PLUGIN_DIR . 'includes/class-ytes-blocks.php';
        require_once YTES_PLUGIN_DIR . 'includes/class-ytes-ajax.php';
        require_once YTES_PLUGIN_DIR . 'includes/class-ytes-analytics.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function init() {
        YTES_Database::get_instance();
        YTES_Admin::get_instance();
        YTES_Blocks::get_instance();
        YTES_Ajax::get_instance();
        YTES_Analytics::get_instance();
    }
    
    public function activate() {
        YTES_Database::create_tables();
        
        // Set default options
        $defaults = array(
            'youtube_channel_id' => '',
            'youtube_api_key' => '',
            'enable_analytics' => true,
            'email_double_optin' => false,
            'email_notification' => get_option('admin_email'),
            'subscribe_button_theme' => 'default',
            'cta_button_text' => 'Watch on YouTube',
            'share_buttons' => array('facebook', 'twitter', 'linkedin', 'pinterest')
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option('ytes_' . $key) === false) {
                add_option('ytes_' . $key, $value);
            }
        }
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'ytes-frontend',
            YTES_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            YTES_VERSION
        );
        
        wp_enqueue_script(
            'ytes-frontend',
            YTES_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            YTES_VERSION,
            true
        );
        
        wp_localize_script('ytes-frontend', 'ytesData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ytes_nonce'),
            'channelId' => get_option('ytes_youtube_channel_id', '')
        ));
    }
}

// Initialize the plugin
function ytes_init() {
    return YouTube_Engagement_Suite::get_instance();
}

add_action('plugins_loaded', 'ytes_init');
