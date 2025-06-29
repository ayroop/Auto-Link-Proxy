<?php
// اسکریپت ساده تست پروکسی با آی‌پی و دامنه سرور ایران

// آی‌پی و دامنه سرور ایران
$proxy_ip = '185.235.196.22';
$proxy_domain = 'sv5.filmkhabar.space';

// فایل ریموت برای تست (در صورت نیاز تغییر دهید)
$remote_url = 'http://sv1.cinetory.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4';

// ساخت لینک‌های پروکسی با آی‌پی و دامنه
$proxy_url_ip = "http://$proxy_ip/proxy.php?url=" . urlencode($remote_url);
$proxy_url_domain = "http://$proxy_domain/proxy.php?url=" . urlencode($remote_url);

// خروجی تست به زبان فارسی
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تست پروکسی با آی‌پی و دامنه سرور ایران</title>
    <style>
        body { font-family: Tahoma, Vazirmatn, Arial, sans-serif; background: #f8f9fa; padding: 40px; }
        .box { background: #fff; border-radius: 10px; box-shadow: 0 4px 16px #0001; padding: 30px; max-width: 700px; margin: 0 auto; }
        h2 { color: #2c3e50; }
        a { color: #007bff; word-break: break-all; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .ip-test { background: #e3f2fd; }
        .domain-test { background: #f3e5f5; }
    </style>
</head>
<body>
    <div class="box">
        <h2>تست پروکسی با آی‌پی و دامنه سرور ایران</h2>
        
        <div class="test-section ip-test">
            <h3>تست با آی‌پی سرور</h3>
            <p><strong>لینک پروکسی (IP):</strong><br>
                <a href="<?= htmlspecialchars($proxy_url_ip) ?>" target="_blank"><?= htmlspecialchars($proxy_url_ip) ?></a>
            </p>
            <p>برای تست دانلود از طریق آی‌پی سرور ایران روی لینک بالا کلیک کنید.</p>
        </div>
        
        <div class="test-section domain-test">
            <h3>تست با دامنه سرور</h3>
            <p><strong>لینک پروکسی (Domain):</strong><br>
                <a href="<?= htmlspecialchars($proxy_url_domain) ?>" target="_blank"><?= htmlspecialchars($proxy_url_domain) ?></a>
            </p>
            <p>برای تست دانلود از طریق دامنه سرور ایران روی لینک بالا کلیک کنید.</p>
        </div>
        
        <div class="test-section">
            <h3>اطلاعات تست</h3>
            <p><strong>فایل ریموت:</strong> <?= htmlspecialchars($remote_url) ?></p>
            <p><strong>آی‌پی سرور:</strong> <?= htmlspecialchars($proxy_ip) ?></p>
            <p><strong>دامنه سرور:</strong> <?= htmlspecialchars($proxy_domain) ?></p>
        </div>
    </div>
</body>
</html> 