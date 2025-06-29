/**
 * Auto Proxy Links - Frontend JavaScript
 * تبدیل خودکار لینک‌های sv1.neurobuild.space به لینک‌های پروکسی
 */

(function($) {
    'use strict';
    
    // تنظیمات از PHP
    const settings = window.apl_settings || {
        source_domain: 'sv1.neurobuild.space',
        proxy_domain: 'filmkhabar.space',
        proxy_ip: '185.235.196.22',
        proxy_path: '/proxy.php',
        show_info: true
    };
    
    /**
     * تبدیل URL مستقیم به URL پروکسی
     */
    function convertToProxyUrl(originalUrl) {
        // بررسی اینکه آیا URL از دامنه منبع است
        if (!originalUrl.includes(settings.source_domain)) {
            return originalUrl;
        }
        
        // استخراج مسیر فایل
        const urlObj = new URL(originalUrl);
        const filePath = urlObj.pathname;
        
        if (!filePath) {
            return originalUrl;
        }
        
        // ساخت URL پروکسی
        const proxyUrl = `https://${settings.proxy_domain}${settings.proxy_path}${filePath}`;
        
        console.log(`🔗 تبدیل لینک: ${originalUrl} -> ${proxyUrl}`);
        return proxyUrl;
    }
    
    /**
     * تبدیل همه لینک‌های موجود در صفحه
     */
    function convertExistingLinks() {
        const links = document.querySelectorAll('a[href*="' + settings.source_domain + '"]');
        
        links.forEach(function(link) {
            const originalUrl = link.href;
            const proxyUrl = convertToProxyUrl(originalUrl);
            
            if (proxyUrl !== originalUrl) {
                link.href = proxyUrl;
                link.setAttribute('data-original-url', originalUrl);
                link.classList.add('proxy-converted');
                
                // اضافه کردن اطلاعات پروکسی
                if (settings.show_info) {
                    addProxyInfo(link);
                }
            }
        });
        
        console.log(`✅ ${links.length} لینک تبدیل شد`);
    }
    
    /**
     * اضافه کردن اطلاعات پروکسی به لینک
     */
    function addProxyInfo(link) {
        // ایجاد tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'proxy-tooltip';
        tooltip.innerHTML = `
            <div class="proxy-info">
                <strong>🔗 لینک پروکسی</strong><br>
                <small>سرور ایرانی: ${settings.proxy_domain}</small><br>
                <small>IP: ${settings.proxy_ip}</small>
            </div>
        `;
        tooltip.style.cssText = `
            position: absolute;
            background: #2c3e50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            max-width: 200px;
        `;
        
        document.body.appendChild(tooltip);
        
        // نمایش tooltip در hover
        link.addEventListener('mouseenter', function(e) {
            tooltip.style.display = 'block';
            tooltip.style.left = e.pageX + 10 + 'px';
            tooltip.style.top = e.pageY - 10 + 'px';
        });
        
        link.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    }
    
    /**
     * نظارت بر تغییرات DOM برای لینک‌های جدید
     */
    function observeNewLinks() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            const newLinks = node.querySelectorAll ? 
                                node.querySelectorAll('a[href*="' + settings.source_domain + '"]') : [];
                            
                            if (node.matches && node.matches('a[href*="' + settings.source_domain + '"]')) {
                                newLinks.push(node);
                            }
                            
                            newLinks.forEach(function(link) {
                                if (!link.classList.contains('proxy-converted')) {
                                    const originalUrl = link.href;
                                    const proxyUrl = convertToProxyUrl(originalUrl);
                                    
                                    if (proxyUrl !== originalUrl) {
                                        link.href = proxyUrl;
                                        link.setAttribute('data-original-url', originalUrl);
                                        link.classList.add('proxy-converted');
                                        
                                        if (settings.show_info) {
                                            addProxyInfo(link);
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * اضافه کردن استایل‌های CSS
     */
    function addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .proxy-converted {
                position: relative;
                transition: all 0.3s ease;
            }
            
            .proxy-converted:hover {
                background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
                color: white !important;
                text-decoration: none;
                padding: 5px 10px;
                border-radius: 5px;
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            }
            
            .proxy-tooltip {
                pointer-events: none;
            }
            
            .proxy-info {
                line-height: 1.4;
            }
            
            .proxy-info strong {
                color: #3498db;
            }
            
            .proxy-info small {
                opacity: 0.8;
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * تست اتصال پروکسی
     */
    function testProxyConnection() {
        const testUrl = `https://${settings.proxy_domain}${settings.proxy_path}`;
        
        fetch(testUrl, { 
            method: 'HEAD',
            mode: 'no-cors'
        })
        .then(function(response) {
            console.log('✅ اتصال پروکسی موفق');
            return true;
        })
        .catch(function(error) {
            console.warn('⚠️ خطا در اتصال پروکسی:', error);
            return false;
        });
    }
    
    /**
     * راه‌اندازی
     */
    function init() {
        console.log('🚀 راه‌اندازی Auto Proxy Links...');
        console.log('🌐 دامنه منبع:', settings.source_domain);
        console.log('🔗 دامنه پروکسی:', settings.proxy_domain);
        
        // اضافه کردن استایل‌ها
        addStyles();
        
        // تبدیل لینک‌های موجود
        convertExistingLinks();
        
        // نظارت بر لینک‌های جدید
        observeNewLinks();
        
        // تست اتصال
        testProxyConnection();
        
        console.log('✅ Auto Proxy Links آماده است');
    }
    
    // راه‌اندازی پس از بارگذاری DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // در دسترس قرار دادن توابع برای استفاده خارجی
    window.AutoProxyLinks = {
        convertToProxyUrl: convertToProxyUrl,
        convertExistingLinks: convertExistingLinks,
        testProxyConnection: testProxyConnection
    };
    
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
