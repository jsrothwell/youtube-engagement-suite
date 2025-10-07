<?php
/**
 * Admin Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class YTES_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('YouTube Engagement', 'yt-engagement-suite'),
            __('YT Engagement', 'yt-engagement-suite'),
            'manage_options',
            'ytes-settings',
            array($this, 'render_settings_page'),
            'dashicons-youtube',
            30
        );
        
        add_submenu_page(
            'ytes-settings',
            __('Settings', 'yt-engagement-suite'),
            __('Settings', 'yt-engagement-suite'),
            'manage_options',
            'ytes-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'ytes-settings',
            __('Subscribers', 'yt-engagement-suite'),
            __('Subscribers', 'yt-engagement-suite'),
            'manage_options',
            'ytes-subscribers',
            array($this, 'render_subscribers_page')
        );
        
        add_submenu_page(
            'ytes-settings',
            __('Analytics', 'yt-engagement-suite'),
            __('Analytics', 'yt-engagement-suite'),
            'manage_options',
            'ytes-analytics',
            array($this, 'render_analytics_page')
        );
    }
    
    public function register_settings() {
        // YouTube Settings
        register_setting('ytes_settings', 'ytes_youtube_channel_id');
        register_setting('ytes_settings', 'ytes_youtube_api_key');
        register_setting('ytes_settings', 'ytes_subscribe_button_theme');
        register_setting('ytes_settings', 'ytes_cta_button_text');
        
        // Email Settings
        register_setting('ytes_settings', 'ytes_email_double_optin');
        register_setting('ytes_settings', 'ytes_email_notification');
        register_setting('ytes_settings', 'ytes_success_message');
        register_setting('ytes_settings', 'ytes_error_message');
        
        // Social Share Settings
        register_setting('ytes_settings', 'ytes_share_buttons');
        
        // Analytics Settings
        register_setting('ytes_settings', 'ytes_enable_analytics');
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ytes-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'ytes-admin',
            YTES_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            YTES_VERSION
        );
        
        wp_enqueue_script(
            'ytes-admin',
            YTES_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            YTES_VERSION,
            true
        );
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['settings-updated'])) {
            add_settings_error('ytes_messages', 'ytes_message', __('Settings Saved', 'yt-engagement-suite'), 'updated');
        }
        
        settings_errors('ytes_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('ytes_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h2><?php _e('YouTube Settings', 'yt-engagement-suite'); ?></h2></th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_youtube_channel_id"><?php _e('YouTube Channel ID', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ytes_youtube_channel_id" name="ytes_youtube_channel_id" 
                                   value="<?php echo esc_attr(get_option('ytes_youtube_channel_id')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Your YouTube channel ID (e.g., UCxxxxxx)', 'yt-engagement-suite'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_youtube_api_key"><?php _e('YouTube API Key', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ytes_youtube_api_key" name="ytes_youtube_api_key" 
                                   value="<?php echo esc_attr(get_option('ytes_youtube_api_key')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Optional: For displaying subscriber count', 'yt-engagement-suite'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_subscribe_button_theme"><?php _e('Subscribe Button Theme', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <select id="ytes_subscribe_button_theme" name="ytes_subscribe_button_theme">
                                <option value="default" <?php selected(get_option('ytes_subscribe_button_theme'), 'default'); ?>><?php _e('Default', 'yt-engagement-suite'); ?></option>
                                <option value="dark" <?php selected(get_option('ytes_subscribe_button_theme'), 'dark'); ?>><?php _e('Dark', 'yt-engagement-suite'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_cta_button_text"><?php _e('CTA Button Text', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ytes_cta_button_text" name="ytes_cta_button_text" 
                                   value="<?php echo esc_attr(get_option('ytes_cta_button_text', 'Watch on YouTube')); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2><?php _e('Email Settings', 'yt-engagement-suite'); ?></h2></th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_email_double_optin"><?php _e('Double Opt-in', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="ytes_email_double_optin" name="ytes_email_double_optin" 
                                   value="1" <?php checked(get_option('ytes_email_double_optin'), 1); ?> />
                            <label for="ytes_email_double_optin"><?php _e('Require email confirmation', 'yt-engagement-suite'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_email_notification"><?php _e('Notification Email', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="ytes_email_notification" name="ytes_email_notification" 
                                   value="<?php echo esc_attr(get_option('ytes_email_notification', get_option('admin_email'))); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Receive notifications when someone subscribes', 'yt-engagement-suite'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_success_message"><?php _e('Success Message', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ytes_success_message" name="ytes_success_message" 
                                   value="<?php echo esc_attr(get_option('ytes_success_message', 'Thank you for subscribing!')); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2><?php _e('Social Share Settings', 'yt-engagement-suite'); ?></h2></th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e('Share Buttons', 'yt-engagement-suite'); ?>
                        </th>
                        <td>
                            <?php
                            $share_buttons = get_option('ytes_share_buttons', array('facebook', 'twitter', 'linkedin', 'pinterest'));
                            $available_buttons = array(
                                'facebook' => 'Facebook',
                                'twitter' => 'Twitter/X',
                                'linkedin' => 'LinkedIn',
                                'pinterest' => 'Pinterest',
                                'reddit' => 'Reddit',
                                'whatsapp' => 'WhatsApp'
                            );
                            
                            foreach ($available_buttons as $key => $label) {
                                $checked = in_array($key, (array)$share_buttons) ? 'checked' : '';
                                echo '<label><input type="checkbox" name="ytes_share_buttons[]" value="' . esc_attr($key) . '" ' . $checked . '> ' . esc_html($label) . '</label><br>';
                            }
                            ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2><?php _e('Analytics Settings', 'yt-engagement-suite'); ?></h2></th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ytes_enable_analytics"><?php _e('Enable Analytics', 'yt-engagement-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="ytes_enable_analytics" name="ytes_enable_analytics" 
                                   value="1" <?php checked(get_option('ytes_enable_analytics'), 1); ?> />
                            <label for="ytes_enable_analytics"><?php _e('Track user engagement', 'yt-engagement-suite'); ?></label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'yt-engagement-suite')); ?>
            </form>
        </div>
        <?php
    }
    
    public function render_subscribers_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $db = YTES_Database::get_instance();
        
        // Handle bulk actions
        if (isset($_POST['action']) && $_POST['action'] === 'export_subscribers') {
            $this->export_subscribers();
        }
        
        $per_page = 20;
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($page - 1) * $per_page;
        
        $subscribers = $db->get_active_subscribers($per_page, $offset);
        $total_count = $db->get_subscriber_count('active');
        $total_pages = ceil($total_count / $per_page);
        ?>
        <div class="wrap">
            <h1><?php _e('Email Subscribers', 'yt-engagement-suite'); ?></h1>
            
            <div class="ytes-stats">
                <div class="ytes-stat-box">
                    <h3><?php echo esc_html($db->get_subscriber_count('active')); ?></h3>
                    <p><?php _e('Active Subscribers', 'yt-engagement-suite'); ?></p>
                </div>
                <div class="ytes-stat-box">
                    <h3><?php echo esc_html($db->get_subscriber_count('pending')); ?></h3>
                    <p><?php _e('Pending Confirmation', 'yt-engagement-suite'); ?></p>
                </div>
                <div class="ytes-stat-box">
                    <h3><?php echo esc_html($db->get_subscriber_count('unsubscribed')); ?></h3>
                    <p><?php _e('Unsubscribed', 'yt-engagement-suite'); ?></p>
                </div>
            </div>
            
            <form method="post" style="margin-top: 20px;">
                <input type="hidden" name="action" value="export_subscribers" />
                <?php submit_button(__('Export to CSV', 'yt-engagement-suite'), 'secondary', 'submit', false); ?>
            </form>
            
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th><?php _e('Email', 'yt-engagement-suite'); ?></th>
                        <th><?php _e('Name', 'yt-engagement-suite'); ?></th>
                        <th><?php _e('Status', 'yt-engagement-suite'); ?></th>
                        <th><?php _e('Source', 'yt-engagement-suite'); ?></th>
                        <th><?php _e('Date Subscribed', 'yt-engagement-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscribers)): ?>
                        <tr>
                            <td colspan="5"><?php _e('No subscribers yet.', 'yt-engagement-suite'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?php echo esc_html($subscriber->email); ?></td>
                                <td><?php echo esc_html($subscriber->name); ?></td>
                                <td><?php echo esc_html($subscriber->status); ?></td>
                                <td><?php echo esc_html($subscriber->source); ?></td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($subscriber->subscribed_date))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $page
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function render_analytics_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $db = YTES_Database::get_instance();
        
        // Get analytics for last 30 days
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        
        $actions = array('subscribe_click', 'email_signup', 'social_share', 'cta_click');
        ?>
        <div class="wrap">
            <h1><?php _e('Analytics Dashboard', 'yt-engagement-suite'); ?></h1>
            
            <div class="ytes-stats">
                <?php foreach ($actions as $action): ?>
                    <div class="ytes-stat-box">
                        <h3><?php echo esc_html($db->get_analytics_count($action, $start_date, $end_date)); ?></h3>
                        <p><?php echo esc_html(ucwords(str_replace('_', ' ', $action))); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2><?php _e('Recent Activity (Last 30 Days)', 'yt-engagement-suite'); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Action', 'yt-engagement-suite'); ?></th>
                        <th><?php _e('Post/Video', 'yt-engagement-suite'); ?></th>
                        <th><?php _e('Date', 'yt-engagement-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_analytics = $db->get_analytics(null, $start_date, $end_date, 50);
                    if (empty($recent_analytics)):
                    ?>
                        <tr>
                            <td colspan="3"><?php _e('No analytics data yet.', 'yt-engagement-suite'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_analytics as $analytic): ?>
                            <tr>
                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $analytic->action_type))); ?></td>
                                <td>
                                    <?php
                                    if ($analytic->post_id) {
                                        echo '<a href="' . get_permalink($analytic->post_id) . '">' . get_the_title($analytic->post_id) . '</a>';
                                    } elseif ($analytic->video_id) {
                                        echo esc_html($analytic->video_id);
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($analytic->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    private function export_subscribers() {
        $db = YTES_Database::get_instance();
        $subscribers = $db->get_active_subscribers();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Email', 'Name', 'Status', 'Source', 'Date Subscribed'));
        
        foreach ($subscribers as $subscriber) {
            fputcsv($output, array(
                $subscriber->email,
                $subscriber->name,
                $subscriber->status,
                $subscriber->source,
                $subscriber->subscribed_date
            ));
        }
        
        fclose($output);
        exit;
    }
}
