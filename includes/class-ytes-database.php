<?php
/**
 * Database Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class YTES_Database {
    
    private static $instance = null;
    private $subscribers_table;
    private $analytics_table;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->subscribers_table = $wpdb->prefix . 'ytes_subscribers';
        $this->analytics_table = $wpdb->prefix . 'ytes_analytics';
    }
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $instance = self::get_instance();
        
        // Subscribers table
        $sql_subscribers = "CREATE TABLE IF NOT EXISTS {$instance->subscribers_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            name varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            source varchar(100) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            subscribed_date datetime DEFAULT CURRENT_TIMESTAMP,
            confirmed_date datetime DEFAULT NULL,
            unsubscribed_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY subscribed_date (subscribed_date)
        ) $charset_collate;";
        
        // Analytics table
        $sql_analytics = "CREATE TABLE IF NOT EXISTS {$instance->analytics_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            action_type varchar(50) NOT NULL,
            post_id bigint(20) DEFAULT NULL,
            video_id varchar(50) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            additional_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action_type (action_type),
            KEY post_id (post_id),
            KEY video_id (video_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_subscribers);
        dbDelta($sql_analytics);
    }
    
    /**
     * Add a new subscriber
     */
    public function add_subscriber($email, $data = array()) {
        global $wpdb;
        
        $defaults = array(
            'name' => '',
            'status' => 'active',
            'source' => 'website',
            'ip_address' => $this->get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : ''
        );
        
        $data = wp_parse_args($data, $defaults);
        $data['email'] = sanitize_email($email);
        
        if (get_option('ytes_email_double_optin')) {
            $data['status'] = 'pending';
        }
        
        $result = $wpdb->insert(
            $this->subscribers_table,
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Check if email exists
     */
    public function subscriber_exists($email) {
        global $wpdb;
        $email = sanitize_email($email);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->subscribers_table} WHERE email = %s",
            $email
        ));
        
        return $count > 0;
    }
    
    /**
     * Get subscriber by email
     */
    public function get_subscriber($email) {
        global $wpdb;
        $email = sanitize_email($email);
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->subscribers_table} WHERE email = %s",
            $email
        ));
    }
    
    /**
     * Update subscriber status
     */
    public function update_subscriber_status($email, $status) {
        global $wpdb;
        $email = sanitize_email($email);
        
        $update_data = array('status' => $status);
        
        if ($status === 'active') {
            $update_data['confirmed_date'] = current_time('mysql');
        } elseif ($status === 'unsubscribed') {
            $update_data['unsubscribed_date'] = current_time('mysql');
        }
        
        return $wpdb->update(
            $this->subscribers_table,
            $update_data,
            array('email' => $email),
            array('%s', '%s'),
            array('%s')
        );
    }
    
    /**
     * Get all active subscribers
     */
    public function get_active_subscribers($limit = null, $offset = 0) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->subscribers_table} WHERE status = 'active' ORDER BY subscribed_date DESC";
        
        if ($limit) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get subscriber count by status
     */
    public function get_subscriber_count($status = 'active') {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->subscribers_table} WHERE status = %s",
            $status
        ));
    }
    
    /**
     * Log analytics event
     */
    public function log_analytics($action_type, $data = array()) {
        global $wpdb;
        
        $defaults = array(
            'post_id' => get_the_ID(),
            'video_id' => null,
            'user_id' => get_current_user_id(),
            'ip_address' => $this->get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'additional_data' => null
        );
        
        $data = wp_parse_args($data, $defaults);
        
        if (is_array($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }
        
        return $wpdb->insert(
            $this->analytics_table,
            array(
                'action_type' => $action_type,
                'post_id' => $data['post_id'],
                'video_id' => $data['video_id'],
                'user_id' => $data['user_id'],
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'additional_data' => $data['additional_data']
            ),
            array('%s', '%d', '%s', '%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get analytics by type
     */
    public function get_analytics($action_type = null, $start_date = null, $end_date = null, $limit = 100) {
        global $wpdb;
        
        $where = array();
        $where_values = array();
        
        if ($action_type) {
            $where[] = "action_type = %s";
            $where_values[] = $action_type;
        }
        
        if ($start_date) {
            $where[] = "created_at >= %s";
            $where_values[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = "created_at <= %s";
            $where_values[] = $end_date;
        }
        
        $sql = "SELECT * FROM {$this->analytics_table}";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT %d";
        $where_values[] = $limit;
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        } else {
            $sql = $wpdb->prepare($sql, $limit);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get analytics count
     */
    public function get_analytics_count($action_type = null, $start_date = null, $end_date = null) {
        global $wpdb;
        
        $where = array();
        $where_values = array();
        
        if ($action_type) {
            $where[] = "action_type = %s";
            $where_values[] = $action_type;
        }
        
        if ($start_date) {
            $where[] = "created_at >= %s";
            $where_values[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = "created_at <= %s";
            $where_values[] = $end_date;
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->analytics_table}";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_var($sql);
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return 'UNKNOWN';
    }
}
