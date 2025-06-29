<?php
/**
 * Plugin Name: Auto Proxy Links
 * Plugin URI: https://filmkhabar.space
 * Description: تبدیل خودکار لینک‌های sv1.neurobuild.space به لینک‌های پروکسی از طریق سرور ایرانی
 * Version: 1.0.0
 * Author: filmkhabar.space
 * Author URI: https://filmkhabar.space
 * License: GPL v2 or later
 * Text Domain: auto-proxy-links
 * Domain Path: /languages
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// تعریف ثابت‌های پلاگین
define('APL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('APL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('APL_PLUGIN_VERSION', '1.0.0');

// تنظیمات پیش‌فرض
$default_settings = [
    'source_domain' => 'sv1.neurobuild.space',
    'proxy_domain' => 'filmkhabar.space',
    'proxy_ip' => '45.12.143.141',
    'proxy_path' => '/proxy.php',
    'enable_posts' => true,
    'enable_pages' => true,
    'enable_widgets' => true,
    'enable_comments' => false,
    'enable_shortcode' => true,
    'auto_convert' => true,
    'show_proxy_info' => true,
    'log_activity' => true
];

/**
 * کلاس اصلی پلاگین
 */
class AutoProxyLinks {
    
    private $settings;
    
    public function __construct() {
        $this->settings = get_option('auto_proxy_links_settings', $default_settings);
        $this->init_hooks();
    }
    
    /**
     * راه‌اندازی hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('wp_ajax_test_proxy_connection', [$this, 'test_proxy_connection']);
        add_action('wp_ajax_nopriv_test_proxy_connection', [$this, 'test_proxy_connection']);
        
        // فیلترهای محتوا
        if ($this->settings['enable_posts']) {
            add_filter('the_content', [$this, 'rewrite_content']);
        }
        if ($this->settings['enable_pages']) {
            add_filter('the_content', [$this, 'rewrite_content']);
        }
        if ($this->settings['enable_widgets']) {
            add_filter('widget_text', [$this, 'rewrite_content']);
        }
        if ($this->settings['enable_comments']) {
            add_filter('comment_text', [$this, 'rewrite_content']);
        }
        
        // شورت‌کد
        if ($this->settings['enable_shortcode']) {
            add_shortcode('proxy_link', [$this, 'proxy_link_shortcode']);
        }
        
        // فعال‌سازی و غیرفعال‌سازی
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * راه‌اندازی پلاگین
     */
    public function init() {
        load_plugin_textdomain('auto-proxy-links', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * فعال‌سازی پلاگین
     */
    public function activate() {
        global $default_settings;
        add_option('auto_proxy_links_settings', $default_settings);
        $this->log_activity('پلاگین فعال شد');
    }
    
    /**
     * غیرفعال‌سازی پلاگین
     */
    public function deactivate() {
        $this->log_activity('پلاگین غیرفعال شد');
    }
    
    /**
     * اضافه کردن منوی مدیریت
     */
    public function add_admin_menu() {
        add_options_page(
            'تنظیمات پروکسی لینک',
            'پروکسی لینک',
            'manage_options',
            'auto-proxy-links',
            [$this, 'admin_page']
        );
    }
    
    /**
     * اسکریپت‌های مدیریت
     */
    public function admin_scripts($hook) {
        if ($hook !== 'settings_page_auto-proxy-links') {
            return;
        }
        
        wp_enqueue_script('auto-proxy-links-admin', APL_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], APL_PLUGIN_VERSION, true);
        wp_localize_script('auto-proxy-links-admin', 'apl_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('apl_nonce')
        ]);
    }
    
    /**
     * اسکریپت‌های فرانت‌اند
     */
    public function frontend_scripts() {
        if ($this->settings['auto_convert']) {
            wp_enqueue_script('auto-proxy-links-frontend', APL_PLUGIN_URL . 'assets/js/auto-proxy-links.js', ['jquery'], APL_PLUGIN_VERSION, true);
            wp_localize_script('auto-proxy-links-frontend', 'apl_settings', [
                'source_domain' => $this->settings['source_domain'],
                'proxy_domain' => $this->settings['proxy_domain'],
                'proxy_ip' => $this->settings['proxy_ip'],
                'proxy_path' => $this->settings['proxy_path'],
                'show_info' => $this->settings['show_proxy_info']
            ]);
        }
    }
    
    /**
     * بازنویسی محتوا
     */
    public function rewrite_content($content) {
        if (empty($content)) {
            return $content;
        }
        
        $source_domain = $this->settings['source_domain'];
        $proxy_domain = $this->settings['proxy_domain'];
        $proxy_path = $this->settings['proxy_path'];
        
        // الگوی regex برای پیدا کردن لینک‌ها
        $pattern = '/https?:\/\/' . preg_quote($source_domain, '/') . '([^"\s\'<>]+)/i';
        
        // جایگزینی لینک‌ها
        $content = preg_replace_callback($pattern, function($matches) use ($proxy_domain, $proxy_path) {
            $original_url = $matches[0];
            $file_path = $matches[1];
            
            // ساخت لینک پروکسی
            $proxy_url = "https://{$proxy_domain}{$proxy_path}{$file_path}";
            
            $this->log_activity("لینک تبدیل شد: {$original_url} -> {$proxy_url}");
            
            return $proxy_url;
        }, $content);
        
        return $content;
    }
    
    /**
     * شورت‌کد لینک پروکسی
     */
    public function proxy_link_shortcode($atts) {
        $atts = shortcode_atts([
            'url' => '',
            'text' => 'دانلود',
            'class' => 'proxy-link',
            'target' => '_blank'
        ], $atts);
        
        if (empty($atts['url'])) {
            return '<span style="color: red;">خطا: URL مشخص نشده است</span>';
        }
        
        $source_domain = $this->settings['source_domain'];
        $proxy_domain = $this->settings['proxy_domain'];
        $proxy_path = $this->settings['proxy_path'];
        
        // بررسی اینکه آیا URL از دامنه منبع است
        if (strpos($atts['url'], $source_domain) === false) {
            return '<span style="color: orange;">هشدار: URL از دامنه مجاز نیست</span>';
        }
        
        // استخراج مسیر فایل
        $file_path = parse_url($atts['url'], PHP_URL_PATH);
        if (empty($file_path)) {
            return '<span style="color: red;">خطا: مسیر فایل نامعتبر است</span>';
        }
        
        // ساخت لینک پروکسی
        $proxy_url = "https://{$proxy_domain}{$proxy_path}{$file_path}";
        
        $this->log_activity("شورت‌کد لینک: {$atts['url']} -> {$proxy_url}");
        
        return sprintf(
            '<a href="%s" class="%s" target="%s" data-original-url="%s">%s</a>',
            esc_url($proxy_url),
            esc_attr($atts['class']),
            esc_attr($atts['target']),
            esc_attr($atts['url']),
            esc_html($atts['text'])
        );
    }
    
    /**
     * تست اتصال پروکسی
     */
    public function test_proxy_connection() {
        check_ajax_referer('apl_nonce', 'nonce');
        
        $proxy_domain = $this->settings['proxy_domain'];
        $test_url = "https://{$proxy_domain}/proxy.php";
        
        $response = wp_remote_get($test_url, [
            'timeout' => 10,
            'user-agent' => 'WordPress/' . get_bloginfo('version')
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error([
                'message' => 'خطا در اتصال: ' . $response->get_error_message()
            ]);
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            wp_send_json_success([
                'message' => 'اتصال موفق! سرور پروکسی در دسترس است.',
                'status_code' => $status_code
            ]);
        } else {
            wp_send_json_error([
                'message' => "خطا در اتصال. کد وضعیت: {$status_code}",
                'status_code' => $status_code
            ]);
        }
    }
    
    /**
     * صفحه مدیریت
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $this->settings = get_option('auto_proxy_links_settings', $default_settings);
        include APL_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    /**
     * ذخیره تنظیمات
     */
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('apl_settings');
        
        $settings = [
            'source_domain' => sanitize_text_field($_POST['source_domain']),
            'proxy_domain' => sanitize_text_field($_POST['proxy_domain']),
            'proxy_ip' => sanitize_text_field($_POST['proxy_ip']),
            'proxy_path' => sanitize_text_field($_POST['proxy_path']),
            'enable_posts' => isset($_POST['enable_posts']),
            'enable_pages' => isset($_POST['enable_pages']),
            'enable_widgets' => isset($_POST['enable_widgets']),
            'enable_comments' => isset($_POST['enable_comments']),
            'enable_shortcode' => isset($_POST['enable_shortcode']),
            'auto_convert' => isset($_POST['auto_convert']),
            'show_proxy_info' => isset($_POST['show_proxy_info']),
            'log_activity' => isset($_POST['log_activity'])
        ];
        
        update_option('auto_proxy_links_settings', $settings);
        $this->settings = $settings;
        
        $this->log_activity('تنظیمات به‌روزرسانی شد');
        
        echo '<div class="notice notice-success"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
    }
    
    /**
     * ثبت فعالیت
     */
    private function log_activity($message) {
        if (!$this->settings['log_activity']) {
            return;
        }
        
        $log_file = WP_CONTENT_DIR . '/proxy-links-log.txt';
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// راه‌اندازی پلاگین
new AutoProxyLinks(); 