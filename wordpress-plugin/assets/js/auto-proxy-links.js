/**
 * Auto Proxy Links - Frontend JavaScript
 * Automatically converts direct links to proxy links
 */

(function($) {
    'use strict';
    
    var AutoProxyLinks = {
        
        /**
         * Initialize the plugin
         */
        init: function() {
            this.convertLinks();
            this.handleManualLinks();
        },
        
        /**
         * Convert direct links to proxy links
         */
        convertLinks: function() {
            // Get settings from localized data
            if (typeof autoProxyLinks === 'undefined') {
                return;
            }
            
            var settings = autoProxyLinks.settings;
            
            if (!settings.enabled) {
                return;
            }
            
            // Build pattern for allowed hosts
            var hostsPattern = settings.allowed_hosts.join('|');
            var extensionsPattern = settings.allowed_extensions.join('|');
            
            // Pattern to match links
            var pattern = new RegExp('\\bhttps?://(' + hostsPattern + ')([^\\s"\'<]*\\.(' + extensionsPattern + '))', 'gi');
            
            // Process all content
            $('body').each(function() {
                var $body = $(this);
                $body.html($body.html().replace(pattern, function(match, host, path) {
                    return AutoProxyLinks.generateProxyUrl(match);
                }));
            });
        },
        
        /**
         * Generate proxy URL
         */
        generateProxyUrl: function(originalUrl) {
            var settings = autoProxyLinks.settings;
            var proxyAddress = settings.use_ip ? settings.proxy_ip : settings.proxy_domain;
            return 'https://' + proxyAddress + '/proxy.php?url=' + encodeURIComponent(originalUrl);
        },
        
        /**
         * Handle manual proxy links
         */
        handleManualLinks: function() {
            // Add click handler for proxy links
            $(document).on('click', '.proxy-link', function(e) {
                var $link = $(this);
                var originalUrl = $link.data('original-url');
                
                if (originalUrl) {
                    e.preventDefault();
                    
                    // Show loading indicator
                    $link.addClass('loading').text('در حال بارگذاری...');
                    
                    // Redirect to proxy URL
                    var proxyUrl = AutoProxyLinks.generateProxyUrl(originalUrl);
                    window.open(proxyUrl, '_blank');
                    
                    // Reset link after a short delay
                    setTimeout(function() {
                        $link.removeClass('loading').text($link.data('original-text') || 'دانلود');
                    }, 2000);
                }
            });
            
            // Add hover effect for proxy links
            $(document).on('mouseenter', '.proxy-link', function() {
                $(this).addClass('hover');
            }).on('mouseleave', '.proxy-link', function() {
                $(this).removeClass('hover');
            });
        },
        
        /**
         * Test proxy connection
         */
        testConnection: function(callback) {
            if (typeof autoProxyLinks === 'undefined') {
                if (callback) callback(false, 'تنظیمات پلاگین در دسترس نیست');
                return;
            }
            
            $.ajax({
                url: autoProxyLinks.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'test_proxy_connection',
                    nonce: autoProxyLinks.nonce
                },
                success: function(response) {
                    if (callback) {
                        callback(response.success, response.data);
                    }
                },
                error: function() {
                    if (callback) {
                        callback(false, 'خطا در برقراری ارتباط');
                    }
                }
            });
        },
        
        /**
         * Convert specific URL to proxy URL
         */
        convertUrl: function(url) {
            if (typeof autoProxyLinks === 'undefined') {
                return url;
            }
            
            var settings = autoProxyLinks.settings;
            var hostsPattern = settings.allowed_hosts.join('|');
            var extensionsPattern = settings.allowed_extensions.join('|');
            
            var pattern = new RegExp('\\bhttps?://(' + hostsPattern + ')([^\\s"\'<]*\\.(' + extensionsPattern + '))', 'i');
            
            if (pattern.test(url)) {
                return this.generateProxyUrl(url);
            }
            
            return url;
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        AutoProxyLinks.init();
    });
    
    // Make AutoProxyLinks available globally
    window.AutoProxyLinks = AutoProxyLinks;
    
})(jQuery);

/**
 * CSS Styles for proxy links
 */
(function() {
    var style = document.createElement('style');
    style.textContent = `
        .proxy-link {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .proxy-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .proxy-link:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .proxy-link:hover:before {
            left: 100%;
        }
        
        .proxy-link.loading {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .proxy-link.loading:before {
            display: none;
        }
        
        .proxy-link.loading:after {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .proxy-link-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
            vertical-align: middle;
        }
        
        .proxy-link-disabled {
            background: #6c757d !important;
            cursor: not-allowed !important;
        }
        
        .proxy-link-disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }
    `;
    document.head.appendChild(style);
})(); 