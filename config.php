<?php
/**
 * تنظیمات مرکزی پروکسی
 * Central Proxy Configuration
 */

// تنظیمات دامنه و پروکسی
// Domain and proxy settings
define('SOURCE_DOMAIN', 'sv1.netwisehub.space'); // دامنه منبع اصلی (آلمان)
define('PROXY_DOMAIN', 'tr.modulogic.space'); // دامنه پروکسی ایرانی
define('PROXY_IP', '45.12.143.141'); // IP اختصاصی سرور ایرانی
define('EXTERNAL_HOST', 'sv1.netwisehub.space'); // سرور خارجی (آلمان)

// تنظیمات مسیر
// Path settings
define('PROXY_SCRIPT_PATH', '/proxy.php'); // مسیر اسکریپت پروکسی

// تنظیمات فایل‌های بزرگ
// Large file settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024 * 1024); // 10GB
define('CHUNK_SIZE', 1024 * 1024); // 1MB chunks
define('BUFFER_SIZE', 8192); // 8KB buffer

// تنظیمات timeout
// Timeout settings
define('REQUEST_TIMEOUT', 300); // 5 minutes
define('STREAM_TIMEOUT', 600); // 10 minutes

// تنظیمات لاگ
// Logging settings
define('LOG_ENABLED', true);
define('LOG_FILE', 'proxy_log.txt');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// تنظیمات امنیت
// Security settings
define('ALLOWED_HOSTS', [
    'sv1.netwisehub.space', // سرور آلمان - منبع اصلی
    'sv1.neurobuild.space', // سرور آلمان - منبع جایگزین
    'serfil.me',            // سرور جایگزین
    'SerFil.me',            // سرور جایگزین (حساس به حروف)
    'tr.modulogic.space',   // سرور ایران
    '45.12.143.141',        // IP سرور ایران
    'httpbin.org',          // برای تست
    'example.com',          // برای تست
    'google.com'            // برای تست
]);
define('BLOCKED_EXTENSIONS', ['php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd']);

// تنظیمات WordPress
// WordPress settings
define('WP_PLUGIN_NAME', 'Auto Proxy Links');
define('WP_PLUGIN_VERSION', '1.0.0');
define('WP_OPTION_NAME', 'auto_proxy_links_settings');

// =======================
// === تنظیمات اصلی ===
// =======================

// دامنه‌های مجاز برای پروکسی
const ALLOWED_HOSTS = [
    'sv1.netwisehub.space', // سرور آلمان - منبع اصلی
    'sv1.neurobuild.space', // سرور آلمان - منبع جایگزین
    'serfil.me',            // سرور جایگزین
    'SerFil.me',            // سرور جایگزین (حساس به حروف)
    'httpbin.org',          // برای تست
    'example.com',          // برای تست
    'google.com',           // برای تست
    // در صورت نیاز دامنه‌های دیگر را اضافه کنید
    // 'example.com',
    // 'another-domain.com',
];

// آدرس پروکسی (دامنه ایران)
const PROXY_DOMAIN = 'tr.modulogic.space';
const PROXY_IP = '45.12.143.141'; // IP اختصاصی شما
const PROXY_SCRIPT = 'proxy.php';

// انتخاب آدرس پروکسی (دامنه یا IP)
const USE_IP_INSTEAD_OF_DOMAIN = false; // false برای استفاده از دامنه، true برای IP

// =======================
// === تنظیمات لاگ‌گیری ===
// =======================

// مسیر فایل لاگ
const LOG_FILE = __DIR__ . '/proxy.log';

// حداکثر اندازه فایل لاگ (بایت)
const MAX_LOG_SIZE = 10 * 1024 * 1024; // 10 مگابایت

// =======================
// === تنظیمات امنیتی ===
// =======================

// محدودیت اندازه فایل (بایت) - 0 = بدون محدودیت
// برای فایل‌های 4K و بزرگ: 10 گیگابایت
const MAX_FILE_SIZE = 10 * 1024 * 1024 * 1024; // 10 گیگابایت
// یا برای فایل‌های معمولی: 5 گیگابایت
// const MAX_FILE_SIZE = 5 * 1024 * 1024 * 1024; // 5 گیگابایت
// یا بدون محدودیت:
// const MAX_FILE_SIZE = 0; // بدون محدودیت

// محدودیت نوع فایل‌های مجاز
const ALLOWED_EXTENSIONS = [
    // ویدیو (فایل‌های بزرگ)
    'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', 'ts', 'mts', 'm2ts',
    // صوتی
    'mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a',
    // فشرده
    'zip', 'rar', '7z', 'tar', 'gz', 'bz2',
    // اسناد
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    // تصاویر
    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg',
    // سایر
    'txt', 'csv', 'json', 'xml'
];

// محدودیت IP (اختیاری)
const ALLOWED_IPS = [
    // '192.168.1.1',
    // '10.0.0.1',
];

// =======================
// === تنظیمات عملکرد ===
// =======================

// اندازه بافر برای استریم (بایت) - افزایش برای فایل‌های بزرگ
const STREAM_BUFFER_SIZE = 16384; // 16KB برای عملکرد بهتر

// حداکثر زمان اجرا (ثانیه) - 0 = بدون محدودیت
const MAX_EXECUTION_TIME = 0; // بدون محدودیت برای فایل‌های بزرگ

// حداکثر حافظه مصرفی (بایت) - افزایش برای فایل‌های بزرگ
const MEMORY_LIMIT = '512M'; // 512 مگابایت برای فایل‌های 4K

// =======================
// === تنظیمات HTTP ===
// =======================

// User-Agent برای درخواست‌های cURL
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

// Timeout برای درخواست‌های cURL (ثانیه) - افزایش برای فایل‌های بزرگ
const CURL_TIMEOUT = 300; // 5 دقیقه برای فایل‌های بزرگ

// =======================
// === توابع کمکی ===
// =======================

/**
 * بررسی اینکه آیا دامنه مجاز است
 */
function isAllowedHost(string $host): bool {
    return in_array(strtolower($host), array_map('strtolower', ALLOWED_HOSTS));
}

/**
 * بررسی اینکه آیا پسوند فایل مجاز است
 */
function isAllowedExtension(string $filename): bool {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_EXTENSIONS);
}

/**
 * بررسی محدودیت اندازه فایل
 */
function checkFileSizeLimit(int $size): bool {
    if (MAX_FILE_SIZE === 0) return true;
    return $size <= MAX_FILE_SIZE;
}

/**
 * بررسی محدودیت IP
 */
function isAllowedIP(string $ip): bool {
    if (empty(ALLOWED_IPS)) return true;
    return in_array($ip, ALLOWED_IPS);
}

/**
 * تولید آدرس پروکسی کامل
 */
function getProxyUrl(string $originalUrl): string {
    $proxyAddress = USE_IP_INSTEAD_OF_DOMAIN ? PROXY_IP : PROXY_DOMAIN;
    return 'https://' . $proxyAddress . '/' . PROXY_SCRIPT . '?url=' . urlencode($originalUrl);
}

/**
 * چرخش فایل لاگ در صورت بزرگ شدن
 */
function rotateLogFile(): void {
    if (!LOG_ENABLED || !file_exists(LOG_FILE)) return;
    
    if (filesize(LOG_FILE) > MAX_LOG_SIZE) {
        $backupFile = LOG_FILE . '.' . date('Y-m-d-H-i-s');
        rename(LOG_FILE, $backupFile);
    }
}

// =======================
// === تنظیمات PHP ===
// =======================

// تنظیم محدودیت‌های PHP
if (MAX_EXECUTION_TIME > 0) {
    set_time_limit(MAX_EXECUTION_TIME);
}

// تنظیم محدودیت حافظه
ini_set('memory_limit', MEMORY_LIMIT);

// فعال‌سازی error reporting در حالت debug
if (LOG_LEVEL === 'DEBUG') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// تنظیم timezone
date_default_timezone_set('Asia/Tehran');

// =======================
// === توابع لاگ‌گیری ===
// =======================

/**
 * ثبت لاگ
 */
function logMessage(string $level, string $message): void {
    if (!LOG_ENABLED) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // چرخش فایل لاگ در صورت نیاز
    rotateLogFile();
    
    // نوشتن لاگ
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * لاگ سطح DEBUG
 */
function logDebug(string $message): void {
    if (LOG_LEVEL === 'DEBUG') {
        logMessage('DEBUG', $message);
    }
}

/**
 * لاگ سطح INFO
 */
function logInfo(string $message): void {
    if (in_array(LOG_LEVEL, ['DEBUG', 'INFO'])) {
        logMessage('INFO', $message);
    }
}

/**
 * لاگ سطح WARNING
 */
function logWarning(string $message): void {
    if (in_array(LOG_LEVEL, ['DEBUG', 'INFO', 'WARNING'])) {
        logMessage('WARNING', $message);
    }
}

/**
 * لاگ سطح ERROR
 */
function logError(string $message): void {
    logMessage('ERROR', $message);
}

// =======================
// === تنظیمات JavaScript ===
// =======================
?>
<script>
const PROXY_DOMAIN = 'tr.modulogic.space';
const SOURCE_DOMAIN = 'sv1.netwisehub.space';
const PROXY_IP = '45.12.143.141';
</script> 