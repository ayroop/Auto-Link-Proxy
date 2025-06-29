/**
 * Auto Proxy Links - Frontend JavaScript
 * تبدیل خودکار لینک‌های مستقیم به لینک‌های پروکسی
 */

(function($) {
    'use strict';

    // تنظیمات پیش‌فرض
    const defaultSettings = {
        proxy_domain: 'tr.modulogic.space',
        source_domain: 'sv1.neurobuild.space',
        allowed_hosts: ['sv1.neurobuild.space'],
        allowed_extensions: ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', 'zip', 'rar', '7z'],
        debug_mode: false
    };

    // دریافت تنظیمات از WordPress
    const settings = window.apl_settings || defaultSettings;

    /**
     * بررسی اینکه آیا URL از دامنه مجاز است
     */
    function isAllowedHost(url) {
        const urlObj = new URL(url);
        return settings.allowed_hosts.some(host => urlObj.hostname.includes(host));
    }

    /**
     * بررسی اینکه آیا فایل از نوع مجاز است
     */
    function isAllowedExtension(url) {
        const extension = url.split('.').pop().toLowerCase();
        return settings.allowed_extensions.includes(extension);
    }

    /**
     * تبدیل URL به لینک پروکسی
     */
    function convertToProxyUrl(originalUrl) {
        try {
            // بررسی اینکه آیا URL از دامنه مجاز است
            if (!isAllowedHost(originalUrl)) {
                if (settings.debug_mode) {
                    console.log('Auto Proxy Links: URL از دامنه مجاز نیست:', originalUrl);
                }
                return originalUrl;
            }

            // بررسی اینکه آیا فایل از نوع مجاز است
            if (!isAllowedExtension(originalUrl)) {
                if (settings.debug_mode) {
                    console.log('Auto Proxy Links: فایل از نوع مجاز نیست:', originalUrl);
                }
                return originalUrl;
            }

            // ساخت URL پروکسی
            const proxyUrl = `https://${settings.proxy_domain}/proxy.php?url=${encodeURIComponent(originalUrl)}`;
            
            if (settings.debug_mode) {
                console.log('Auto Proxy Links: تبدیل لینک:', originalUrl, '->', proxyUrl);
            }
            
            return proxyUrl;
        } catch (error) {
            if (settings.debug_mode) {
                console.error('Auto Proxy Links: خطا در تبدیل لینک:', error);
            }
            return originalUrl;
        }
    }

    /**
     * تبدیل لینک‌های موجود در صفحه
     */
    function convertExistingLinks() {
        $(`a[href*="${settings.source_domain}"]`).each(function() {
            const $link = $(this);
            const originalHref = $link.attr('href');
            
            // بررسی اینکه آیا لینک قبلاً تبدیل شده است
            if ($link.hasClass('proxy-converted')) {
                return;
            }
            
            const proxyHref = convertToProxyUrl(originalHref);
            
            if (proxyHref !== originalHref) {
                $link.attr('href', proxyHref);
                $link.addClass('proxy-converted');
                
                // اضافه کردن نشانگر پروکسی
                if (!$link.find('.proxy-indicator').length) {
                    $link.append('<span class="proxy-indicator" style="color: #0073aa; font-size: 0.8em; margin-right: 5px;">🔗</span>');
                }
            }
        });
    }

    /**
     * تبدیل لینک‌های جدید که به صورت پویا اضافه می‌شوند
     */
    function convertDynamicLinks() {
        // نظارت بر تغییرات DOM
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            $(node).find(`a[href*="${settings.source_domain}"]`).each(function() {
                                const $link = $(this);
                                const originalHref = $link.attr('href');
                                
                                if (!$link.hasClass('proxy-converted')) {
                                    const proxyHref = convertToProxyUrl(originalHref);
                                    
                                    if (proxyHref !== originalHref) {
                                        $link.attr('href', proxyHref);
                                        $link.addClass('proxy-converted');
                                        
                                        if (!$link.find('.proxy-indicator').length) {
                                            $link.append('<span class="proxy-indicator" style="color: #0073aa; font-size: 0.8em; margin-right: 5px;">🔗</span>');
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });

        // شروع نظارت
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * تبدیل لینک‌های در input و textarea
     */
    function convertFormLinks() {
        $('input[type="url"], textarea').on('input', function() {
            const $field = $(this);
            const value = $field.val();
            
            if (value && value.includes(settings.source_domain)) {
                const proxyValue = convertToProxyUrl(value);
                
                if (proxyValue !== value) {
                    $field.val(proxyValue);
                    
                    // نمایش پیام
                    if (!$field.next('.proxy-notice').length) {
                        $field.after('<div class="proxy-notice" style="color: #0073aa; font-size: 0.8em; margin-top: 5px;">🔗 لینک به پروکسی تبدیل شد</div>');
                    }
                }
            }
        });
    }

    /**
     * اضافه کردن دکمه تبدیل دستی
     */
    function addManualConvertButton() {
        // اضافه کردن دکمه به toolbar
        if ($('#wp-admin-bar-top-secondary').length) {
            const $button = $(`
                <li id="wp-admin-bar-proxy-convert">
                    <a href="#" style="color: #0073aa;">
                        <span class="ab-icon">🔗</span>
                        <span class="ab-label">تبدیل لینک‌ها</span>
                    </a>
                </li>
            `);
            
            $('#wp-admin-bar-top-secondary').append($button);
            
            $button.on('click', function(e) {
                e.preventDefault();
                convertExistingLinks();
                alert('لینک‌ها با موفقیت تبدیل شدند!');
            });
        }
    }

    /**
     * راه‌اندازی پلاگین
     */
    function init() {
        if (settings.debug_mode) {
            console.log('Auto Proxy Links: راه‌اندازی پلاگین با تنظیمات:', settings);
        }

        // تبدیل لینک‌های موجود
        convertExistingLinks();
        
        // تبدیل لینک‌های پویا
        convertDynamicLinks();
        
        // تبدیل لینک‌های فرم
        convertFormLinks();
        
        // اضافه کردن دکمه تبدیل دستی
        addManualConvertButton();
        
        // تبدیل مجدد در صورت تغییر محتوا
        $(document).on('contentChanged', convertExistingLinks);
    }

    // راه‌اندازی پس از آماده شدن DOM
    $(document).ready(init);

    // تبدیل مجدد در صورت تغییر محتوا (برای AJAX)
    $(window).on('load', function() {
        setTimeout(convertExistingLinks, 1000);
    });

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
