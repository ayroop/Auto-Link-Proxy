<?php
header('Content-Type: text/html; charset=utf-8');
/**
 * فایل دیباگ برای تست پروکسی
 * Debug file for proxy testing
 */

// بارگذاری تنظیمات
require_once 'config.php';

// تنظیمات اولیه
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 دیباگ پروکسی</h1>";

// نمایش اطلاعات درخواست
echo "<h2>📋 اطلاعات درخواست</h2>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'NULL') . "</p>";
echo "<p><strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'NULL') . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'NULL') . "</p>";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'NULL') . "</p>";

// تست تابع extractFilePath
echo "<h2>🧪 تست تابع extractFilePath</h2>";

function testExtractFilePath($requestUrl) {
    echo "<h3>تست URL: $requestUrl</h3>";
    
    // حذف query string
    $path = parse_url($requestUrl, PHP_URL_PATH);
    echo "<p><strong>مسیر استخراج شده:</strong> $path</p>";
    
    // حذف /proxy.php از ابتدای مسیر
    $path = preg_replace('#^/proxy\.php#', '', $path);
    echo "<p><strong>پس از حذف proxy.php:</strong> $path</p>";
    
    // بررسی وجود مسیر
    if (empty($path) || $path === '/') {
        echo "<p style='color: red;'><strong>نتیجه:</strong> مسیر خالی است</p>";
        return false;
    }
    
    // حذف / از ابتدای مسیر اگر وجود دارد
    $path = ltrim($path, '/');
    echo "<p><strong>مسیر نهایی:</strong> $path</p>";
    
    return $path;
}

// تست URL های مختلف
$testUrls = [
    '/proxy.php',
    '/proxy.php/',
    '/proxy.php/test.mp4',
    '/proxy.php/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4',
    '/proxy.php?url=https://sv1.neurobuild.space/test.mp4',
    $_SERVER['REQUEST_URI'] ?? '/proxy.php'
];

foreach ($testUrls as $url) {
    $result = testExtractFilePath($url);
    if ($result) {
        echo "<p style='color: green;'><strong>✅ موفق:</strong> $result</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ ناموفق:</strong> مسیر نامعتبر</p>";
    }
    echo "<hr>";
}

// تست اتصال به سرور منبع
echo "<h2>🌐 تست اتصال به سرور منبع</h2>";
$sourceDomain = SOURCE_DOMAIN;
$testUrl = "https://$sourceDomain";

echo "<p><strong>دامنه منبع:</strong> $sourceDomain</p>";
echo "<p><strong>URL تست:</strong> $testUrl</p>";

$headers = @get_headers($testUrl, 1);
if ($headers) {
    echo "<p style='color: green;'>✅ اتصال موفق</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $headers[0] . "</p>";
} else {
    echo "<p style='color: red;'>❌ اتصال ناموفق</p>";
}

// تست فایل نمونه
echo "<h2>🎬 تست فایل نمونه</h2>";
$sampleFile = "/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4";
$fullUrl = "https://$sourceDomain$sampleFile";

echo "<p><strong>فایل نمونه:</strong> $sampleFile</p>";
echo "<p><strong>URL کامل:</strong> $fullUrl</p>";

$fileHeaders = @get_headers($fullUrl, 1);
if ($fileHeaders) {
    echo "<p style='color: green;'>✅ فایل موجود است</p>";
    echo "<p><strong>کد وضعیت:</strong> " . $fileHeaders[0] . "</p>";
    
    if (isset($fileHeaders['Content-Length'])) {
        echo "<p><strong>اندازه:</strong> " . number_format($fileHeaders['Content-Length']) . " بایت</p>";
    }
    if (isset($fileHeaders['Content-Type'])) {
        echo "<p><strong>نوع:</strong> " . $fileHeaders['Content-Type'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ فایل موجود نیست</p>";
}

// لینک‌های تست
echo "<h2>🔗 لینک‌های تست</h2>";
$proxyDomain = PROXY_DOMAIN;

echo "<p><a href='https://$proxyDomain/proxy.php$sampleFile' target='_blank'>🎬 تست دانلود فایل</a></p>";
echo "<p><a href='https://$proxyDomain/proxy.php' target='_blank'>📋 صفحه اصلی پروکسی</a></p>";
echo "<p><a href='simple_ip_test.php' target='_blank'>🧪 تست IP</a></p>";

// اطلاعات سیستم
echo "<h2>💻 اطلاعات سیستم</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Client IP:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "</p>";

// زمان تست
echo "<h2>⏰ زمان تست</h2>";
echo "<p><strong>تاریخ و زمان:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 