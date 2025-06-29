<?php
/**
 * Admin page for Auto Proxy Links plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ذخیره تنظیمات
if (isset($_POST['submit'])) {
    $settings = [
        'enabled' => isset($_POST['enabled']),
        'proxy_domain' => sanitize_text_field($_POST['proxy_domain']),
        'source_domain' => sanitize_text_field($_POST['source_domain']),
        'allowed_hosts' => array_map('sanitize_text_field', explode(',', $_POST['allowed_hosts'])),
        'allowed_extensions' => array_map('sanitize_text_field', explode(',', $_POST['allowed_extensions'])),
        'debug_mode' => isset($_POST['debug_mode']),
        'auto_rewrite' => isset($_POST['auto_rewrite']),
        'rewrite_posts' => isset($_POST['rewrite_posts']),
        'rewrite_pages' => isset($_POST['rewrite_pages']),
        'rewrite_widgets' => isset($_POST['rewrite_widgets'])
    ];
    
    update_option('auto_proxy_links_settings', $settings);
    echo '<div class="notice notice-success"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
}

// دریافت تنظیمات فعلی
$settings = get_option('auto_proxy_links_settings', [
    'enabled' => true,
    'proxy_domain' => 'tr.modulogic.space',
    'source_domain' => 'sv1.netwisehub.space',
    'allowed_hosts' => ['sv1.netwisehub.space', 'sv1.neurobuild.space', 'serfil.me'],
    'allowed_extensions' => ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', 'zip', 'rar', '7z'],
    'debug_mode' => false,
    'auto_rewrite' => true,
    'rewrite_posts' => true,
    'rewrite_pages' => true,
    'rewrite_widgets' => true
]);
?>

<div class="wrap">
    <h1>⚙️ تنظیمات پروکسی لینک</h1>
    
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enabled">فعال‌سازی پلاگین</label>
                </th>
                <td>
                    <input type="checkbox" id="enabled" name="enabled" <?php checked($settings['enabled']); ?>>
                    <p class="description">فعال‌سازی یا غیرفعال‌سازی پلاگین</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="proxy_domain">دامنه پروکسی (ایران)</label>
                </th>
                <td>
                    <input type="text" id="proxy_domain" name="proxy_domain" value="<?php echo esc_attr($settings['proxy_domain'] ?? 'tr.modulogic.space'); ?>" class="regular-text">
                    <p class="description">دامنه سرور ایران (مثال: tr.modulogic.space)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="source_domain">دامنه منبع (آلمان)</label>
                </th>
                <td>
                    <input type="text" id="source_domain" name="source_domain" value="<?php echo esc_attr($settings['source_domain'] ?? 'sv1.netwisehub.space'); ?>" class="regular-text">
                    <p class="description">دامنه سرور آلمان (مثال: sv1.netwisehub.space)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="allowed_hosts">دامنه‌های مجاز</label>
                </th>
                <td>
                    <input type="text" id="allowed_hosts" name="allowed_hosts" value="<?php echo esc_attr(implode(',', $settings['allowed_hosts'] ?? ['sv1.netwisehub.space', 'sv1.neurobuild.space', 'serfil.me'])); ?>" class="regular-text">
                    <p class="description">دامنه‌های مجاز برای تبدیل (جدا شده با کاما)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="allowed_extensions">پسوندهای مجاز</label>
                </th>
                <td>
                    <input type="text" id="allowed_extensions" name="allowed_extensions" value="<?php echo esc_attr(implode(',', $settings['allowed_extensions'] ?? ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', 'zip', 'rar', '7z'])); ?>" class="regular-text">
                    <p class="description">پسوندهای مجاز برای تبدیل (جدا شده با کاما)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="debug_mode">حالت دیباگ</label>
                </th>
                <td>
                    <input type="checkbox" id="debug_mode" name="debug_mode" <?php checked($settings['debug_mode']); ?>>
                    <p class="description">فعال‌سازی لاگ‌گیری برای عیب‌یابی</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="auto_rewrite">تبدیل خودکار</label>
                </th>
                <td>
                    <input type="checkbox" id="auto_rewrite" name="auto_rewrite" <?php checked($settings['auto_rewrite']); ?>>
                    <p class="description">تبدیل خودکار لینک‌ها در محتوا</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">محل‌های تبدیل</th>
                <td>
                    <label>
                        <input type="checkbox" name="rewrite_posts" <?php checked($settings['rewrite_posts']); ?>>
                        پست‌ها
                    </label><br>
                    <label>
                        <input type="checkbox" name="rewrite_pages" <?php checked($settings['rewrite_pages']); ?>>
                        صفحات
                    </label><br>
                    <label>
                        <input type="checkbox" name="rewrite_widgets" <?php checked($settings['rewrite_widgets']); ?>>
                        ویجت‌ها
                    </label>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="ذخیره تنظیمات">
        </p>
    </form>
    
    <hr>
    
    <h2>🧪 تست اتصال</h2>
    <p>برای تست اتصال به سرور پروکسی، روی دکمه زیر کلیک کنید:</p>
    <button id="test-connection" class="button button-secondary">تست اتصال</button>
    <div id="test-result" style="margin-top: 10px;"></div>
    
    <script>
    document.getElementById('test-connection').addEventListener('click', function() {
        const resultDiv = document.getElementById('test-result');
        const domain = document.getElementById('proxy_domain').value;
        
        resultDiv.innerHTML = 'در حال تست اتصال...';
        
        fetch(`https://${domain}/proxy.php`)
            .then(response => {
                if (response.ok) {
                    resultDiv.innerHTML = '<div style="color: green;">✅ اتصال موفق!</div>';
                } else {
                    resultDiv.innerHTML = '<div style="color: red;">❌ خطا در اتصال</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div style="color: red;">❌ خطا در اتصال: ' + error.message + '</div>';
            });
    });
    </script>
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
    border-radius: 5px;
    margin: 10px 0;
}

.example-links h4 {
    margin-top: 0;
    color: #0073aa;
}

.example-links code {
    background: #fff;
    padding: 2px 5px;
    border-radius: 3px;
    border: 1px solid #ddd;
}
</style> 