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

// تنظیمات اولیه برای فایل‌های بزرگ
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
ini_set('display_errors', 0);
ini_set('output_buffering', 'Off');
ini_set('zlib.output_compression', false);

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
            if (preg_match('#^https?://#i', $filePath)) {
                $sourceUrl = $filePath;
            } else {
                $sourceUrl = "https://{$this->sourceDomain}" . (strpos($filePath, '/') === 0 ? $filePath : "/$filePath");
            }
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
        
        return true;
    }
    
    private function proxyFile($sourceUrl, $filePath) {
        // Set the current file name for headers
        $this->currentFileName = basename($filePath) ?: 'video.mp4';
        
        // Extend execution time for large files
        set_time_limit(0);
        ignore_user_abort(false);
        
        // دریافت Range header
        $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
        $this->logger->log("Range header: $rangeHeader", 'DEBUG');
        
        // First, get headers with HEAD request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $sourceUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            // Enhanced SSL/TLS settings for Iranian ISPs
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_CIPHER_LIST => 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);
        
        // Add Range header if present
        $headers = [];
        if (!empty($rangeHeader)) {
            $headers[] = "Range: $rangeHeader";
        }
        
        // Add other important headers
        $importantHeaders = [
            'If-Range', 'If-Modified-Since', 'If-None-Match', 
            'Accept', 'Accept-Encoding', 'Referer'
        ];
        
        foreach ($importantHeaders as $header) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
            $value = $_SERVER[$serverKey] ?? '';
            if (!empty($value)) {
                $headers[] = "$header: $value";
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $headResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($headResponse === false) {
            $this->logger->log("خطای cURL در HEAD: $error", 'ERROR');
            $this->errorResponse('خطا در اتصال به سرور منبع', 502);
            return;
        }
        
        // Parse headers from HEAD response
        $this->parseHeaders($headResponse);
        
        // Send appropriate headers to client
        $this->sendSimpleHeaders($httpCode);
        
        // Store logger reference for the callback
        $logger = $this->logger;
        
        // Now stream the actual content
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $sourceUrl,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_TIMEOUT => 0, // No timeout for streaming
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_BUFFERSIZE => 8192, // 8KB buffer - smaller for better responsiveness
            CURLOPT_TCP_NODELAY => true,
            // Enhanced SSL/TLS settings for Iranian ISPs
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_CIPHER_LIST => 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_WRITEFUNCTION => function($ch, $data) use ($logger) {
                // Check if connection is still alive
                if (connection_aborted()) {
                    return -1; // Stop cURL
                }
                
                // Output the data
                echo $data;
                
                // Flush output buffers more aggressively
                while (ob_get_level()) {
                    ob_end_flush();
                }
                flush();
                
                return strlen($data);
            }
        ]);
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($result === false && !connection_aborted()) {
            $this->logger->log("خطای cURL: $error", 'ERROR');
            return;
        }
        
        $this->logger->log("فایل با موفقیت ارسال شد: $filePath (HTTP: $httpCode)");
    }
    
    private function parseHeaders($headResponse) {
        $lines = explode("\r\n", $headResponse);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $lineLower = strtolower($line);
            if (strpos($lineLower, 'content-type:') === 0) {
                $this->contentType = trim(substr($line, 13));
            } elseif (strpos($lineLower, 'content-length:') === 0) {
                $this->contentLength = trim(substr($line, 15));
            } elseif (strpos($lineLower, 'content-range:') === 0) {
                $this->contentRange = trim(substr($line, 14));
                $this->isPartial = true;
            } elseif (strpos($lineLower, 'accept-ranges:') === 0) {
                $this->acceptRanges = trim(substr($line, 14));
            } elseif (strpos($lineLower, 'last-modified:') === 0) {
                $this->lastModified = trim(substr($line, 14));
            } elseif (strpos($lineLower, 'etag:') === 0) {
                $this->etag = trim(substr($line, 6));
            }
        }
    }
    
    private function sendSimpleHeaders($httpCode) {
        // Clear any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set HTTP status code
        if ($this->isPartial || $httpCode == 206) {
            http_response_code(206);
        } else {
            http_response_code(200);
        }
        
        // Set Content-Type with proper video MIME type detection
        $contentType = $this->contentType;
        if (!$contentType) {
            $extension = strtolower(pathinfo($this->currentFileName, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'mp4':
                    $contentType = 'video/mp4';
                    break;
                case 'mkv':
                    $contentType = 'video/x-matroska';
                    break;
                case 'avi':
                    $contentType = 'video/x-msvideo';
                    break;
                case 'mov':
                    $contentType = 'video/quicktime';
                    break;
                case 'webm':
                    $contentType = 'video/webm';
                    break;
                default:
                    $contentType = 'application/octet-stream';
            }
        }
        header('Content-Type: ' . $contentType);
        
        // Set Content-Length
        if ($this->contentLength) {
            header('Content-Length: ' . $this->contentLength);
        }
        
        // Set Content-Range for partial content
        if ($this->contentRange) {
            header('Content-Range: ' . $this->contentRange);
        }
        
        // Set Accept-Ranges - crucial for video streaming
        header('Accept-Ranges: bytes');
        
        // Set Last-Modified
        if ($this->lastModified) {
            header('Last-Modified: ' . $this->lastModified);
        }
        
        // Set ETag
        if ($this->etag) {
            header('ETag: ' . $this->etag);
        }
        
        // Set Content-Disposition for inline viewing (not download)
        header('Content-Disposition: inline; filename="' . $this->currentFileName . '"');
        
        // Set security headers
        header('X-Proxy-Server: filmkhabar.space');
        header('X-Source-Domain: ' . $this->sourceDomain);
        
        // Set CORS headers for video streaming
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
        header('Access-Control-Allow-Headers: Range, If-Range, If-Modified-Since, If-None-Match');
        header('Access-Control-Expose-Headers: Content-Length, Content-Range, Accept-Ranges');
        
        // Set Cache headers optimized for video
        header('Cache-Control: public, max-age=3600, must-revalidate');
        header('Pragma: public');
        
        // Additional headers for better video streaming
        header('Connection: keep-alive');
        
        $this->logger->log("Headers sent successfully", 'DEBUG');
    }
    
    private $responseHeaders = [];
    private $contentType = null;
    private $contentLength = null;
    private $contentRange = null;
    private $acceptRanges = null;
    private $lastModified = null;
    private $etag = null;
    private $isPartial = false;
    private $currentFileName = 'video.mp4';
    
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
            <div class="url">https://sv1.netwisehub.space/path/to/video.mp4</div>
            
            <strong>لینک پروکسی:</strong><br>
            <div class="url">https://filmkhabar.space/proxy.php/path/to/video.mp4</div>
        </div>
        
        <h2>🔧 ویژگی‌ها:</h2>
        <ul>
            <li>✅ پشتیبانی از فایل‌های بزرگ (تا 10GB)</li>
            <li>✅ قابلیت Resume دانلود (HTTP Range)</li>
            <li>✅ سرعت بالا با cURL streaming</li>
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