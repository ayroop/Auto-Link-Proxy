<?php
header('Content-Type: text/html; charset=utf-8');
/**
 * تست ساده IP و دامنه
 * Simple IP and Domain Test
 */

// تنظیمات
$source_domain = 'sv1.neurobuild.space';
$proxy_domain = 'filmkhabar.space';
$proxy_ip = '185.235.196.22';

echo "<h1>🧪 تست IP و دامنه</h1>";
echo "<p><strong>دامنه منبع:</strong> $source_domain</p>";
echo "<p><strong>دامنه پروکسی:</strong> $proxy_domain</p>";
echo "<p><strong>IP سرور:</strong> $proxy_ip</p>";

// تست DNS
echo "<h2>🔍 تست DNS</h2>";
$source_ip = gethostbyname($source_domain);
$proxy_ip_check = gethostbyname($proxy_domain);

echo "<p><strong>IP دامنه منبع:</strong> $source_ip</p>";
echo "<p><strong>IP دامنه پروکسی:</strong> $proxy_ip_check</p>";

// تست اتصال
echo "<h2>🌐 تست اتصال</h2>";

// تست دامنه منبع
$source_url = "https://$source_domain";
$source_headers = @get_headers($source_url, 1);
if ($source_headers) {
    echo "<p style='color: green;'>✅ دامنه منبع قابل دسترس است</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $source_headers[0] . "</p>";
} else {
    echo "<p style='color: red;'>❌ دامنه منبع قابل دسترس نیست</p>";
}

// تست دامنه پروکسی
$proxy_url = "https://$proxy_domain";
$proxy_headers = @get_headers($proxy_url, 1);
if ($proxy_headers) {
    echo "<p style='color: green;'>✅ دامنه پروکسی قابل دسترس است</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $proxy_headers[0] . "</p>";
} else {
    echo "<p style='color: red;'>❌ دامنه پروکسی قابل دسترس نیست</p>";
}

// تست IP مستقیم
$ip_url = "http://$proxy_ip";
$ip_headers = @get_headers($ip_url, 1);
if ($ip_headers) {
    echo "<p style='color: green;'>✅ IP سرور قابل دسترس است</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $ip_headers[0] . "</p>";
} else {
    echo "<p style='color: red;'>❌ IP سرور قابل دسترس نیست</p>";
}

// تست پروکسی
echo "<h2>🔗 تست پروکسی</h2>";
$test_file = "/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4";
$proxy_test_url = "https://$proxy_domain/proxy.php$test_file";

echo "<p><strong>URL تست:</strong> $proxy_test_url</p>";

$proxy_test_headers = @get_headers($proxy_test_url, 1);
if ($proxy_test_headers) {
    echo "<p style='color: green;'>✅ پروکسی کار می‌کند</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $proxy_test_headers[0] . "</p>";
    
    // نمایش headers مهم
    if (isset($proxy_test_headers['Content-Type'])) {
        echo "<p><strong>نوع محتوا:</strong> " . $proxy_test_headers['Content-Type'] . "</p>";
    }
    if (isset($proxy_test_headers['Content-Length'])) {
        echo "<p><strong>اندازه:</strong> " . number_format($proxy_test_headers['Content-Length']) . " بایت</p>";
    }
    if (isset($proxy_test_headers['Accept-Ranges'])) {
        echo "<p><strong>پشتیبانی از Range:</strong> " . $proxy_test_headers['Accept-Ranges'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ پروکسی کار نمی‌کند</p>";
}

// اطلاعات سیستم
echo "<h2>💻 اطلاعات سیستم</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Client IP:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>";
echo "<p><strong>User Agent:</strong> " . $_SERVER['HTTP_USER_AGENT'] . "</p>";

// لینک‌های تست
echo "<h2>🔗 لینک‌های تست</h2>";
echo "<p><a href='$proxy_test_url' target='_blank'>🎬 تست دانلود فیلم</a></p>";
echo "<p><a href='https://$proxy_domain/proxy.php' target='_blank'>📋 صفحه اصلی پروکسی</a></p>";
echo "<p><a href='test_proxy.html' target='_blank'>🧪 صفحه تست کامل</a></p>";

// زمان تست
echo "<h2>⏰ زمان تست</h2>";
echo "<p><strong>تاریخ و زمان:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>منطقه زمانی:</strong> " . date_default_timezone_get() . "</p>";
?> 