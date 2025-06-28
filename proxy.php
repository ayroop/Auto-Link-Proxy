<?php
/**
 * proxy.php
 *
 * یک پروکسی امن PHP برای استریم فایل از sv1.cinetory.space از طریق سرور ایران
 * ویژگی‌ها:
 *  - مدیریت URL دینامیک
 *  - پشتیبانی از Range (ادامه دانلود)
 *  - لاگ‌گیری قابل تنظیم
 *  - فیلتر بر اساس دامنه (هر مسیری مجاز است)
 * نیازمندی‌ها: PHP با افزونه cURL
 */

// بارگذاری تنظیمات مرکزی
require_once __DIR__ . '/config.php';

// =======================
// === توابع کمکی ===
// =======================

/**
 * نوشتن پیام در لاگ در صورت فعال بودن
 */
function logMessage(string $message): void {
    if (!LOG_ENABLED) {
        return;
    }
    
    // چرخش لاگ در صورت نیاز
    rotateLogFile();
    
    $time = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    file_put_contents(LOG_FILE, "[$time] [$ip] $message\n", FILE_APPEND);
}

/**
 * ارسال پاسخ خطای HTTP و خروج
 */
function sendError(int $code, string $message): void {
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    logMessage("خطای $code: $message");
    exit;
}

/**
 * بررسی محدودیت‌های امنیتی
 */
function checkSecurityLimits(string $url, int $fileSize): void {
    $parts = parse_url($url);
    $host = $parts['host'] ?? '';
    $path = $parts['path'] ?? '';
    
    // بررسی دامنه مجاز
    if (!isAllowedHost($host)) {
        sendError(403, 'دسترسی ممنوع: دامنه غیرمجاز');
    }
    
    // بررسی محدودیت IP
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!isAllowedIP($clientIP)) {
        sendError(403, 'دسترسی ممنوع: IP غیرمجاز');
    }
    
    // بررسی محدودیت اندازه فایل
    if (!checkFileSizeLimit($fileSize)) {
        sendError(413, 'فایل خیلی بزرگ است');
    }
    
    // بررسی پسوند فایل
    if (!isAllowedExtension($path)) {
        sendError(403, 'نوع فایل مجاز نیست');
    }
}

// =======================
// === اجرای اصلی ===
// =======================

try {
    logMessage('----- درخواست پروکسی جدید -----');

    // 1. اعتبارسنجی پارامتر URL
    $rawUrl = $_GET['url'] ?? '';
    if (empty($rawUrl)) {
        sendError(400, 'پارامتر url الزامی است');
    }
    $url = filter_var($rawUrl, FILTER_SANITIZE_URL);
    logMessage("URL درخواستی: $url");

    // 2. تجزیه و اعتبارسنجی اولیه
    $parts = parse_url($url);
    if (empty($parts['host'])) {
        sendError(400, 'URL نامعتبر است');
    }

    // 3. درخواست HEAD برای دریافت اطلاعات محتوا
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_TIMEOUT        => CURL_TIMEOUT,
        CURLOPT_USERAGENT      => USER_AGENT,
    ]);
    
    if (!curl_exec($ch)) {
        throw new Exception('خطا در اتصال: ' . curl_error($ch));
    }
    
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'application/octet-stream';
    $totalSize   = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);

    // 4. بررسی محدودیت‌های امنیتی
    checkSecurityLimits($url, $totalSize);

    // 5. ارسال هدرها
    header('Content-Type: ' . $contentType);
    header('Accept-Ranges: bytes');

    // 6. پردازش Range برای پشتیبانی از ادامه دانلود
    $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
    $start = 0;
    $end   = $totalSize - 1;
    $statusCode = 200;

    if ($rangeHeader && preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
        $start = (int)$matches[1];
        if ($matches[2] !== '') {
            $end = (int)$matches[2];
        }
        if ($start > $end || $start >= $totalSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes */$totalSize");
            exit;
        }
        $statusCode = 206;
        header('HTTP/1.1 206 Partial Content');
    } else {
        header('HTTP/1.1 200 OK');
    }

    $length = $end - $start + 1;
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $totalSize);
    header('Content-Length: ' . $length);

    // 7. استریم محتوا
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_BUFFERSIZE     => STREAM_BUFFER_SIZE,
        CURLOPT_TIMEOUT        => CURL_TIMEOUT,
        CURLOPT_USERAGENT      => USER_AGENT,
    ]);
    
    if ($statusCode === 206) {
        curl_setopt($ch, CURLOPT_RANGE, "$start-$end");
    }
    
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
        echo $data;
        @ob_flush();
        @flush();
        return strlen($data);
    });

    if (!curl_exec($ch)) {
        throw new Exception('خطا در انتقال داده: ' . curl_error($ch));
    }
    curl_close($ch);
    
    logMessage("استریم با موفقیت تکمیل شد - اندازه: $length بایت");

} catch (Exception $e) {
    sendError(500, 'خطای داخلی سرور: ' . $e->getMessage());
}

exit;
