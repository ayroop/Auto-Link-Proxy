<?php
/**
 * Admin page for Auto Proxy Links plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('auto_proxy_links_settings', array());
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('auto_proxy_links_settings'); ?>
        
        <div class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active">تنظیمات عمومی</a>
            <a href="#advanced" class="nav-tab">تنظیمات پیشرفته</a>
            <a href="#test" class="nav-tab">تست اتصال</a>
        </div>
        
        <!-- General Settings -->
        <div id="general" class="tab-content">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enabled">فعال کردن پلاگین</label>
                    </th>
                    <td>
                        <input type="checkbox" id="enabled" name="auto_proxy_links_settings[enabled]" value="1" <?php checked($settings['enabled'] ?? true); ?>>
                        <p class="description">فعال یا غیرفعال کردن تبدیل خودکار لینک‌ها</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="proxy_domain">دامنه پروکسی</label>
                    </th>
                    <td>
                        <input type="text" id="proxy_domain" name="auto_proxy_links_settings[proxy_domain]" value="<?php echo esc_attr($settings['proxy_domain'] ?? 'filmkhabar.space'); ?>" class="regular-text">
                        <p class="description">دامنه سرور ایران (مثال: filmkhabar.space)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="proxy_ip">IP اختصاصی</label>
                    </th>
                    <td>
                        <input type="text" id="proxy_ip" name="auto_proxy_links_settings[proxy_ip]" value="<?php echo esc_attr($settings['proxy_ip'] ?? '185.235.196.22'); ?>" class="regular-text">
                        <p class="description">IP اختصاصی سرور ایران</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="use_ip">استفاده از IP به جای دامنه</label>
                    </th>
                    <td>
                        <input type="checkbox" id="use_ip" name="auto_proxy_links_settings[use_ip]" value="1" <?php checked($settings['use_ip'] ?? false); ?>>
                        <p class="description">استفاده از IP به جای دامنه برای لینک‌های پروکسی</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Advanced Settings -->
        <div id="advanced" class="tab-content" style="display: none;">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="allowed_hosts">دامنه‌های مجاز</label>
                    </th>
                    <td>
                        <textarea id="allowed_hosts" name="auto_proxy_links_settings[allowed_hosts]" rows="3" class="large-text"><?php echo esc_textarea(implode("\n", $settings['allowed_hosts'] ?? array('sv1.cinetory.space'))); ?></textarea>
                        <p class="description">هر دامنه در یک خط جداگانه</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="allowed_extensions">پسوندهای مجاز</label>
                    </th>
                    <td>
                        <textarea id="allowed_extensions" name="auto_proxy_links_settings[allowed_extensions]" rows="3" class="large-text"><?php echo esc_textarea(implode("\n", $settings['allowed_extensions'] ?? array('mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', 'ts', 'mts', 'm2ts'))); ?></textarea>
                        <p class="description">هر پسوند در یک خط جداگانه (بدون نقطه)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">محتوای قابل پردازش</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="auto_proxy_links_settings[process_posts]" value="1" <?php checked($settings['process_posts'] ?? true); ?>>
                                پست‌ها
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="auto_proxy_links_settings[process_pages]" value="1" <?php checked($settings['process_pages'] ?? true); ?>>
                                صفحات
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="auto_proxy_links_settings[process_widgets]" value="1" <?php checked($settings['process_widgets'] ?? true); ?>>
                                ویجت‌ها
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="auto_proxy_links_settings[process_comments]" value="1" <?php checked($settings['process_comments'] ?? false); ?>>
                                نظرات
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="debug_mode">حالت دیباگ</label>
                    </th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="auto_proxy_links_settings[debug_mode]" value="1" <?php checked($settings['debug_mode'] ?? false); ?>>
                        <p class="description">ثبت لاگ تبدیل لینک‌ها (فقط برای توسعه)</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Test Connection -->
        <div id="test" class="tab-content" style="display: none;">
            <h3>تست اتصال پروکسی</h3>
            <p>برای تست اتصال به سرور پروکسی، روی دکمه زیر کلیک کنید:</p>
            
            <button type="button" id="test-connection" class="button button-primary">تست اتصال</button>
            
            <div id="test-result" style="margin-top: 10px; padding: 10px; border-radius: 3px; display: none;"></div>
            
            <h4>مثال لینک‌های تبدیل شده:</h4>
            <div class="example-links">
                <p><strong>لینک اصلی:</strong></p>
                <code>https://sv1.cinetory.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4</code>
                
                <p><strong>لینک پروکسی:</strong></p>
                <code id="proxy-example"><?php echo esc_html($this->generate_proxy_url('https://sv1.cinetory.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4')); ?></code>
            </div>
            
            <h4>استفاده از شورت‌کد:</h4>
            <p>برای لینک‌های دستی از شورت‌کد زیر استفاده کنید:</p>
            <code>[proxy_link url="https://sv1.cinetory.space/h2/movie/file.mp4" text="دانلود فیلم"]</code>
        </div>
        
        <?php submit_button('ذخیره تنظیمات'); ?>
    </form>
</div>

<style>
.nav-tab-wrapper {
    margin-bottom: 20px;
}

.tab-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    border-top: none;
}

.example-links {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 3px;
    margin-top: 15px;
}

.example-links code {
    display: block;
    background: #fff;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ddd;
    border-radius: 3px;
    word-break: break-all;
}

#test-result.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

#test-result.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });
    
    // Test connection
    $('#test-connection').click(function() {
        var button = $(this);
        var result = $('#test-result');
        
        button.prop('disabled', true).text('در حال تست...');
        result.hide();
        
        $.ajax({
            url: autoProxyLinks.ajaxUrl,
            type: 'POST',
            data: {
                action: 'test_proxy_connection',
                nonce: autoProxyLinks.nonce
            },
            success: function(response) {
                if (response.success) {
                    result.removeClass('error').addClass('success').html('<strong>موفق:</strong> ' + response.data).show();
                } else {
                    result.removeClass('success').addClass('error').html('<strong>خطا:</strong> ' + response.data).show();
                }
            },
            error: function() {
                result.removeClass('success').addClass('error').html('<strong>خطا:</strong> درخواست ناموفق بود').show();
            },
            complete: function() {
                button.prop('disabled', false).text('تست اتصال');
            }
        });
    });
    
    // Update proxy example when settings change
    $('#proxy_domain, #proxy_ip, #use_ip').on('change keyup', function() {
        updateProxyExample();
    });
    
    function updateProxyExample() {
        var domain = $('#proxy_domain').val();
        var ip = $('#proxy_ip').val();
        var useIp = $('#use_ip').is(':checked');
        
        var proxyAddress = useIp ? ip : domain;
        var originalUrl = 'https://sv1.cinetory.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4';
        var proxyUrl = 'https://' + proxyAddress + '/proxy.php?url=' + encodeURIComponent(originalUrl);
        
        $('#proxy-example').text(proxyUrl);
    }
});
</script> 