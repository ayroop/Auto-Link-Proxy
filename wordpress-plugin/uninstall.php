<?php
/**
 * Uninstall script for Auto Proxy Links plugin
 * This file is executed when the plugin is deleted from WordPress
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up plugin data
 */
function auto_proxy_links_uninstall() {
    // Remove plugin settings
    delete_option('auto_proxy_links_settings');
    
    // Remove any transients
    delete_transient('auto_proxy_links_cache');
    delete_transient('auto_proxy_links_test_result');
    
    // Clear any scheduled events
    wp_clear_scheduled_hook('auto_proxy_links_cleanup');
    
    // Remove any custom database tables if created
    global $wpdb;
    
    // Example: Remove custom table if exists
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}auto_proxy_links_logs");
    
    // Clear any cached data
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Log uninstall for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Auto Proxy Links plugin uninstalled successfully');
    }
}

// Run uninstall function
auto_proxy_links_uninstall(); 