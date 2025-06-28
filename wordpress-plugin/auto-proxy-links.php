<?php
/**
 * Plugin Name: Auto Proxy Links
 * Plugin URI: https://filmkhabar.space
 * Description: Automatically converts direct links to sv1.cinetory.space into proxy links through Iranian host for bypassing download restrictions.
 * Version: 1.0.0
 * Author: FilmKhabar
 * Author URI: https://filmkhabar.space
 * License: MIT
 * Text Domain: auto-proxy-links
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Auto Proxy Links Plugin Class
 */
class AutoProxyLinks {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * Plugin settings
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load settings
        $this->load_settings();
        
        // Add hooks
        add_action('init', array($this, 'init_hooks'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add filters for content processing
        add_filter('the_content', array($this, 'process_content'), 999);
        add_filter('widget_text', array($this, 'process_content'), 999);
        add_filter('comment_text', array($this, 'process_content'), 999);
        
        // Add shortcode
        add_shortcode('proxy_link', array($this, 'proxy_link_shortcode'));
        
        // Add activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin settings
     */
    private function load_settings() {
        $this->settings = get_option('auto_proxy_links_settings', array(
            'enabled' => true,
            'proxy_domain' => 'filmkhabar.space',
            'proxy_ip' => '185.235.196.22',
            'use_ip' => false,
            'allowed_hosts' => array('sv1.cinetory.space'),
            'allowed_extensions' => array('mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', 'ts', 'mts', 'm2ts'),
            'process_posts' => true,
            'process_pages' => true,
            'process_widgets' => true,
            'process_comments' => false,
            'debug_mode' => false
        ));
    }
    
    /**
     * Initialize hooks
     */
    public function init_hooks() {
        // Add AJAX handlers
        add_action('wp_ajax_test_proxy_connection', array($this, 'test_proxy_connection'));
        add_action('wp_ajax_nopriv_test_proxy_connection', array($this, 'test_proxy_connection'));
    }
    
    /**
     * Process content and convert links
     */
    public function process_content($content) {
        if (empty($content) || !$this->settings['enabled']) {
            return $content;
        }
        
        // Check if we should process this content type
        if (!$this->should_process_content()) {
            return $content;
        }
        
        // Process the content
        $content = $this->convert_links($content);
        
        return $content;
    }
    
    /**
     * Check if content should be processed
     */
    private function should_process_content() {
        global $post;
        
        if (is_admin()) {
            return false;
        }
        
        if (is_single() && !$this->settings['process_posts']) {
            return false;
        }
        
        if (is_page() && !$this->settings['process_pages']) {
            return false;
        }
        
        if (is_widget() && !$this->settings['process_widgets']) {
            return false;
        }
        
        if (is_comment() && !$this->settings['process_comments']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Convert direct links to proxy links
     */
    private function convert_links($content) {
        // Build pattern for allowed hosts
        $hosts_pattern = implode('|', array_map('preg_quote', $this->settings['allowed_hosts']));
        
        // Pattern to match links
        $pattern = '#\bhttps?://(' . $hosts_pattern . ')([^\s"\'<]*)#i';
        
        return preg_replace_callback($pattern, array($this, 'replace_link'), $content);
    }
    
    /**
     * Replace individual link
     */
    private function replace_link($matches) {
        $original_url = $matches[0];
        $host = $matches[1];
        $path = $matches[2];
        
        // Check if file extension is allowed
        if (!$this->is_allowed_extension($path)) {
            return $original_url;
        }
        
        // Generate proxy URL
        $proxy_url = $this->generate_proxy_url($original_url);
        
        if ($this->settings['debug_mode']) {
            error_log("Auto Proxy Links: Converted {$original_url} to {$proxy_url}");
        }
        
        return $proxy_url;
    }
    
    /**
     * Check if file extension is allowed
     */
    private function is_allowed_extension($path) {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, $this->settings['allowed_extensions']);
    }
    
    /**
     * Generate proxy URL
     */
    private function generate_proxy_url($original_url) {
        $proxy_address = $this->settings['use_ip'] ? $this->settings['proxy_ip'] : $this->settings['proxy_domain'];
        return 'https://' . $proxy_address . '/proxy.php?url=' . urlencode($original_url);
    }
    
    /**
     * Shortcode for manual proxy links
     */
    public function proxy_link_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'url' => '',
            'text' => 'دانلود',
            'class' => 'proxy-link'
        ), $atts);
        
        if (empty($atts['url'])) {
            return $content ?: $atts['text'];
        }
        
        $proxy_url = $this->generate_proxy_url($atts['url']);
        $link_text = $content ?: $atts['text'];
        
        return sprintf(
            '<a href="%s" class="%s" target="_blank">%s</a>',
            esc_url($proxy_url),
            esc_attr($atts['class']),
            esc_html($link_text)
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'Auto Proxy Links',
            'Auto Proxy Links',
            'manage_options',
            'auto-proxy-links',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('auto_proxy_links_settings', 'auto_proxy_links_settings', array($this, 'sanitize_settings'));
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? true : false;
        $sanitized['proxy_domain'] = sanitize_text_field($input['proxy_domain']);
        $sanitized['proxy_ip'] = sanitize_text_field($input['proxy_ip']);
        $sanitized['use_ip'] = isset($input['use_ip']) ? true : false;
        $sanitized['allowed_hosts'] = array_map('sanitize_text_field', $input['allowed_hosts']);
        $sanitized['allowed_extensions'] = array_map('sanitize_text_field', $input['allowed_extensions']);
        $sanitized['process_posts'] = isset($input['process_posts']) ? true : false;
        $sanitized['process_pages'] = isset($input['process_pages']) ? true : false;
        $sanitized['process_widgets'] = isset($input['process_widgets']) ? true : false;
        $sanitized['process_comments'] = isset($input['process_comments']) ? true : false;
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? true : false;
        
        return $sanitized;
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        include plugin_dir_path(__FILE__) . 'admin/admin-page.php';
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        if ($this->settings['enabled']) {
            wp_enqueue_script(
                'auto-proxy-links',
                plugin_dir_url(__FILE__) . 'assets/js/auto-proxy-links.js',
                array('jquery'),
                self::VERSION,
                true
            );
            
            wp_localize_script('auto-proxy-links', 'autoProxyLinks', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('auto_proxy_links_nonce'),
                'settings' => $this->settings
            ));
        }
    }
    
    /**
     * Test proxy connection
     */
    public function test_proxy_connection() {
        check_ajax_referer('auto_proxy_links_nonce', 'nonce');
        
        $test_url = 'https://sv1.cinetory.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4';
        $proxy_url = $this->generate_proxy_url($test_url);
        
        $response = wp_remote_head($proxy_url, array(
            'timeout' => 10,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Connection failed: ' . $response->get_error_message());
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code === 200) {
                wp_send_json_success('Connection successful! Status: ' . $status_code);
            } else {
                wp_send_json_error('Connection failed! Status: ' . $status_code);
            }
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default settings if not exists
        if (!get_option('auto_proxy_links_settings')) {
            $this->load_settings();
            update_option('auto_proxy_links_settings', $this->settings);
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize plugin
new AutoProxyLinks(); 