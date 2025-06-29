<?php
header('Content-Type: text/html; charset=utf-8');
/**
 * تست ساده پروکسی
 * Simple proxy test
 */

// بارگذاری تنظیمات
require_once 'config.php';

echo "<h1>🧪 تست ساده پروکسی</h1>";

// تنظیمات
$source_domain = SOURCE_DOMAIN;
$proxy_domain = PROXY_DOMAIN;
$test_file = "/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4";

echo "<h2>📋 اطلاعات تست</h2>";
echo "<p><strong>دامنه منبع:</strong> $source_domain</p>";
echo "<p><strong>دامنه پروکسی:</strong> $proxy_domain</p>";
echo "<p><strong>فایل تست:</strong> $test_file</p>";

// تست اتصال به سرور منبع
echo "<h2>🌐 تست اتصال به سرور منبع</h2>";
$source_url = "https://$source_domain$test_file";
echo "<p><strong>URL منبع:</strong> $source_url</p>";

$headers = @get_headers($source_url, 1);
if ($headers) {
    echo "<p style='color: green;'>✅ فایل در سرور منبع موجود است</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $headers[0] . "</p>";
    
    if (isset($headers['Content-Length'])) {
        $size = number_format($headers['Content-Length']);
        echo "<p><strong>اندازه:</strong> $size بایت</p>";
    }
} else {
    echo "<p style='color: red;'>❌ فایل در سرور منبع موجود نیست</p>";
}

// لینک‌های تست مختلف
echo "<h2>🔗 لینک‌های تست</h2>";

// روش 1: URL path
$proxy_url1 = "https://$proxy_domain/proxy.php$test_file";
echo "<p><strong>روش 1 (URL Path):</strong></p>";
echo "<p><a href='$proxy_url1' target='_blank'>🎬 $proxy_url1</a></p>";

// روش 2: Query parameter path
$proxy_url2 = "https://$proxy_domain/proxy.php?path=$test_file";
echo "<p><strong>روش 2 (Query Path):</strong></p>";
echo "<p><a href='$proxy_url2' target='_blank'>🎬 $proxy_url2</a></p>";

// روش 3: Query parameter url
$proxy_url3 = "https://$proxy_domain/proxy.php?url=" . urlencode($source_url);
echo "<p><strong>روش 3 (Query URL):</strong></p>";
echo "<p><a href='$proxy_url3' target='_blank'>🎬 $proxy_url3</a></p>";

// تست مستقیم
echo "<h2>🎯 تست مستقیم</h2>";
echo "<p><a href='$source_url' target='_blank' style='color: red;'>🔗 لینک مستقیم (احتمالاً فیلتر شده)</a></p>";

// اطلاعات سیستم
echo "<h2>💻 اطلاعات سیستم</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Client IP:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "</p>";

// لینک‌های مفید
echo "<h2>🔧 ابزارهای مفید</h2>";
echo "<p><a href='debug_proxy.php' target='_blank'>🔍 دیباگ کامل</a></p>";
echo "<p><a href='simple_ip_test.php' target='_blank'>🌐 تست IP</a></p>";
echo "<p><a href='test_proxy.html' target='_blank'>📋 صفحه تست کامل</a></p>";

// زمان تست
echo "<h2>⏰ زمان تست</h2>";
echo "<p><strong>تاریخ و زمان:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 