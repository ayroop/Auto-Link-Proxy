<?php
/**
 * پروکسی اسکریپت برای عبور از محدودیت‌های دانلود
 * Proxy script for bypassing download restrictions
 * 
 * این اسکریپت فایل‌های ویدیو و سریال را از سرور خارجی
 * از طریق سرور ایرانی پروکسی می‌کند
 */

// بارگذاری تنظیمات
require_once 'config.php';

// تنظیمات اولیه
ini_set('memory_limit', '2G');
ini_set('max_execution_time', 0);
ini_set('display_errors', 0);

// کلاس لاگ‌گیری
class ProxyLogger {
    private $logFile;
    private $enabled;
    
    public function __construct($logFile, $enabled = true) {
        $this->logFile = $logFile;
        $this->enabled = $enabled;
    }
    
    public function log($message, $level = 'INFO') {
        if (!$this->enabled) return;
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// کلاس اصلی پروکسی
class VideoProxy {
    private $logger;
    private $sourceDomain;
    private $proxyDomain;
    private $proxyIP;
    
    public function __construct() {
        $this->logger = new ProxyLogger(LOG_FILE, LOG_ENABLED);
        $this->sourceDomain = SOURCE_DOMAIN;
        $this->proxyDomain = PROXY_DOMAIN;
        $this->proxyIP = PROXY_IP;
    }
    
    public function handleRequest() {
        try {
            // دریافت URL درخواست
            $requestUrl = $_SERVER['REQUEST_URI'] ?? '';
            $this->logger->log("درخواست دریافت شد: $requestUrl");
            
            // بررسی وجود URL
            if (empty($requestUrl) || $requestUrl === '/') {
                $this->showUsage();
                return;
            }
            
            // استخراج مسیر فایل از URL
            $filePath = $this->extractFilePath($requestUrl);
            
            // اگر از URL path استخراج نشد، از query parameter استفاده کن
            if (!$filePath) {
                $filePath = $_GET['path'] ?? '';
                if (!empty($filePath)) {
                    $this->logger->log("مسیر از query parameter: $filePath");
                }
            }
            
            // اگر هنوز مسیر نداریم، از پارامتر url استفاده کن
            if (!$filePath) {
                $urlParam = $_GET['url'] ?? '';
                if (!empty($urlParam)) {
                    $this->logger->log("URL از query parameter: $urlParam");
                    $filePath = $this->extractPathFromUrl($urlParam);
                }
            }
            
            if (!$filePath) {
                $this->errorResponse('مسیر فایل نامعتبر است', 400);
                return;
            }
            
            // بررسی امنیت
            if (!$this->validateSecurity($filePath)) {
                $this->errorResponse('دسترسی غیرمجاز', 403);
        return;
    }
            
            // ساخت URL کامل منبع
            $sourceUrl = "https://{$this->sourceDomain}{$filePath}";
            $this->logger->log("URL منبع: $sourceUrl");
            
            // ارسال فایل
            $this->proxyFile($sourceUrl, $filePath);
            
        } catch (Exception $e) {
            $this->logger->log("خطا: " . $e->getMessage(), 'ERROR');
            $this->errorResponse('خطای داخلی سرور', 500);
        }
    }
    
    private function extractFilePath($requestUrl) {
        $this->logger->log("درخواست URL: $requestUrl", 'DEBUG');
        
        // حذف query string
        $path = parse_url($requestUrl, PHP_URL_PATH);
        $this->logger->log("مسیر استخراج شده: $path", 'DEBUG');
        
        // حذف /proxy.php از ابتدای مسیر
        $path = preg_replace('#^/proxy\.php#', '', $path);
        $this->logger->log("مسیر پس از حذف proxy.php: $path", 'DEBUG');
        
        // بررسی وجود مسیر
        if (empty($path) || $path === '/') {
            $this->logger->log("مسیر خالی است", 'WARNING');
            return false;
        }
        
        // حذف / از ابتدای مسیر اگر وجود دارد
        $path = ltrim($path, '/');
        $this->logger->log("مسیر نهایی: $path", 'DEBUG');
        
        return $path;
    }
    
    private function extractPathFromUrl($url) {
        $parsed = parse_url($url);
        return $parsed['path'] ?? '';
    }
    
    private function validateSecurity($filePath) {
        // بررسی پسوند فایل
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($extension, BLOCKED_EXTENSIONS)) {
            $this->logger->log("پسوند فایل مسدود شده: $extension", 'WARNING');
            return false;
        }
        
        // بررسی اندازه فایل (اگر در header موجود باشد)
        $contentLength = $_SERVER['HTTP_CONTENT_LENGTH'] ?? 0;
        if ($contentLength > MAX_FILE_SIZE) {
            $this->logger->log("فایل خیلی بزرگ: $contentLength bytes", 'WARNING');
            return false;
        }
        
        return true;
    }
    
    private function proxyFile($sourceUrl, $filePath) {
        // تنظیم headers
        $headers = $this->prepareHeaders();
        
        // ایجاد context برای درخواست
        $context = stream_context_create([
            'http' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'header' => $headers,
                'timeout' => REQUEST_TIMEOUT,
                'follow_location' => false,
                'max_redirects' => 0
            ]
        ]);
        
        // باز کردن stream
        $stream = @fopen($sourceUrl, 'rb', false, $context);
        if (!$stream) {
            $this->logger->log("خطا در باز کردن فایل: $sourceUrl", 'ERROR');
            $this->errorResponse('فایل یافت نشد', 404);
            return;
        }
        
        // دریافت meta data
        $metaData = stream_get_meta_data($stream);
        $responseHeaders = $metaData['wrapper_data'] ?? [];
        
        // بررسی کد پاسخ
        $statusCode = $this->extractStatusCode($responseHeaders);
        if ($statusCode !== 200 && $statusCode !== 206) {
            fclose($stream);
            $this->logger->log("خطای HTTP: $statusCode", 'ERROR');
            $this->errorResponse('خطای سرور منبع', $statusCode);
            return;
        }
        
        // ارسال headers
        $this->sendHeaders($responseHeaders, $filePath);
        
        // ارسال محتوا
        $this->streamContent($stream);
        
        fclose($stream);
        $this->logger->log("فایل با موفقیت ارسال شد: $filePath");
    }
    
    private function prepareHeaders() {
        $headers = [];
        
        // کپی headers مهم
        $importantHeaders = [
            'Range', 'If-Range', 'If-Modified-Since', 
            'If-None-Match', 'Accept', 'Accept-Encoding',
            'User-Agent', 'Referer'
        ];
        
        foreach ($importantHeaders as $header) {
            $value = $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $header))] ?? '';
            if (!empty($value)) {
                $headers[] = "$header: $value";
            }
        }
        
        // اضافه کردن User-Agent پیش‌فرض
        if (empty(array_filter($headers, function($h) { return strpos($h, 'User-Agent:') === 0; }))) {
            $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        }
        
        return implode("\r\n", $headers);
    }
    
    private function extractStatusCode($headers) {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                return (int)$matches[1];
            }
        }
        return 200;
    }
    
    private function sendHeaders($responseHeaders, $filePath) {
        // حذف headers غیرضروری
        $skipHeaders = [
            'transfer-encoding', 'connection', 'keep-alive',
            'proxy-authenticate', 'proxy-authorization'
        ];
        
        foreach ($responseHeaders as $header) {
            $headerLower = strtolower($header);
            $shouldSkip = false;
            
            foreach ($skipHeaders as $skip) {
                if (strpos($headerLower, $skip) === 0) {
                    $shouldSkip = true;
                    break;
                }
            }
            
            if (!$shouldSkip) {
                header($header);
            }
        }
        
        // اضافه کردن headers امنیتی
        header('X-Proxy-Server: filmkhabar.space');
        header('X-Source-Domain: ' . $this->sourceDomain);
        
        // تنظیم CORS برای درخواست‌های AJAX
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
        header('Access-Control-Allow-Headers: Range, If-Range, If-Modified-Since, If-None-Match');

        // --- اضافه کردن Content-Disposition با نام فایل اصلی ---
        $filename = basename($filePath);
        if (!$filename) {
            $filename = 'video.mp4';
        }
        // اگر کاربر می‌خواهد دانلود کند (یا برای دانلود منیجرها)
        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(MSIE|Trident|Edge|IDM|Download|wget|curl|Safari|Chrome|Firefox)/i', $_SERVER['HTTP_USER_AGENT'])) {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            // حالت پیش‌فرض: نمایش در مرورگر
            header('Content-Disposition: inline; filename="' . $filename . '"');
        }
    }
    
    private function streamContent($stream) {
        $bufferSize = BUFFER_SIZE;
        $totalSent = 0;
        
        while (!feof($stream)) {
            $chunk = fread($stream, $bufferSize);
            if ($chunk === false) {
                break;
            }
            
            echo $chunk;
            $totalSent += strlen($chunk);
            
            // flush output
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
            
            // بررسی timeout
            if (connection_aborted()) {
                $this->logger->log("اتصال توسط کاربر قطع شد", 'INFO');
                break;
            }
        }
        
        $this->logger->log("تعداد بایت ارسال شده: $totalSent");
    }
    
    private function showUsage() {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پروکسی ویدیو - filmkhabar.space</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .example { background: #ecf0f1; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .url { background: #34495e; color: #ecf0f1; padding: 10px; border-radius: 3px; font-family: monospace; word-break: break-all; }
        .info { background: #3498db; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .warning { background: #e74c3c; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 پروکسی ویدیو - filmkhabar.space</h1>
        
        <div class="info">
            <strong>✅ فعال:</strong> این سرور پروکسی برای عبور از محدودیت‌های دانلود فعال است.
        </div>
        
        <h2>📋 نحوه استفاده:</h2>
        <div class="example">
            <strong>لینک اصلی:</strong><br>
            <div class="url">https://sv1.neurobuild.space/path/to/video.mp4</div>
            
            <strong>لینک پروکسی:</strong><br>
            <div class="url">https://filmkhabar.space/proxy.php/path/to/video.mp4</div>
        </div>
        
        <h2>🔧 ویژگی‌ها:</h2>
        <ul>
            <li>✅ پشتیبانی از فایل‌های بزرگ (تا 10GB)</li>
            <li>✅ قابلیت Resume دانلود</li>
            <li>✅ سرعت بالا با بافر بهینه</li>
            <li>✅ لاگ‌گیری کامل</li>
            <li>✅ امنیت بالا</li>
        </ul>
        
        <div class="warning">
            <strong>⚠️ توجه:</strong> این سرویس فقط برای فایل‌های ویدیو و سریال طراحی شده است.
        </div>
        
        <h2>📞 پشتیبانی:</h2>
        <p>برای گزارش مشکلات یا درخواست ویژگی‌های جدید، با تیم فنی تماس بگیرید.</p>
    </div>
</body>
</html>';
    }
    
    private function errorResponse($message, $code = 500) {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        
        echo '<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>خطا - پروکسی ویدیو</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .error { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .error-code { font-size: 72px; color: #e74c3c; margin: 0; }
        .error-message { color: #2c3e50; font-size: 18px; margin: 20px 0; }
        .back-link { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error">
        <h1 class="error-code">' . $code . '</h1>
        <p class="error-message">' . htmlspecialchars($message) . '</p>
        <a href="/" class="back-link">← بازگشت به صفحه اصلی</a>
    </div>
</body>
</html>';
    }
}

// اجرای پروکسی
$proxy = new VideoProxy();
$proxy->handleRequest();
?> 