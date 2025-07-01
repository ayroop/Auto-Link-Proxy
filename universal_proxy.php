<?php
/**
 * Universal Proxy Handler
 * Works with both Apache and Nginx automatically
 */

require_once 'config.php';
require_once 'server_detect.php';

class UniversalProxy {
    private $detector;
    private $logger;
    private $sourceDomain;
    private $proxyDomain;
    
    public function __construct() {
        $this->detector = new ServerDetector();
        $this->logger = new ProxyLogger(LOG_FILE, LOG_ENABLED);
        $this->sourceDomain = SOURCE_DOMAIN;
        $this->proxyDomain = PROXY_DOMAIN;
        
        // Log server detection
        $serverInfo = $this->detector->getServerInfo();
        $this->logger->log("Server detected: " . json_encode($serverInfo));
    }
    
    public function handleRequest() {
        try {
            // Get the file path using universal method
            $filePath = $this->getUniversalFilePath();
            
            if (!$filePath) {
                $this->showUsage();
                return;
            }
            
            $this->logger->log("Processing file path: $filePath");
            
            // Security validation
            if (!$this->validateSecurity($filePath)) {
                $this->errorResponse('Access denied', 403);
                return;
            }
            
            // Build source URL
            $sourceUrl = $this->buildSourceUrl($filePath);
            $this->logger->log("Source URL: $sourceUrl");
            
            // Proxy the file
            $this->proxyFile($sourceUrl, $filePath);
            
        } catch (Exception $e) {
            $this->logger->log("Error: " . $e->getMessage(), 'ERROR');
            $this->errorResponse('Internal server error', 500);
        }
    }
    
    private function getUniversalFilePath() {
        $filePath = null;
        
        // Method 1: Check PATH_INFO (works with both servers when configured)
        if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
            $filePath = ltrim($_SERVER['PATH_INFO'], '/');
            $this->logger->log("Path from PATH_INFO: $filePath", 'DEBUG');
        }
        
        // Method 2: Check query parameter 'path'
        if (!$filePath && isset($_GET['path']) && !empty($_GET['path'])) {
            $filePath = ltrim($_GET['path'], '/');
            $this->logger->log("Path from query parameter: $filePath", 'DEBUG');
        }
        
        // Method 3: Check query parameter 'url'
        if (!$filePath && isset($_GET['url']) && !empty($_GET['url'])) {
            $url = $_GET['url'];
            $parsed = parse_url($url);
            $filePath = ltrim($parsed['path'] ?? '', '/');
            $this->logger->log("Path from URL parameter: $filePath", 'DEBUG');
        }
        
        // Method 4: Parse REQUEST_URI (Apache-style)
        if (!$filePath && $this->detector->isApache()) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            
            // Remove script name from request URI
            if (strpos($requestUri, $scriptName) === 0) {
                $pathInfo = substr($requestUri, strlen($scriptName));
                $pathInfo = strtok($pathInfo, '?'); // Remove query string
                $filePath = ltrim($pathInfo, '/');
                $this->logger->log("Path from Apache REQUEST_URI parsing: $filePath", 'DEBUG');
            }
        }
        
        // Method 5: Parse REQUEST_URI (Nginx-style)
        if (!$filePath && $this->detector->isNginx()) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            
            // For Nginx, check if the URI contains the proxy script
            if (preg_match('#/proxy\.php/(.+)#', $requestUri, $matches)) {
                $filePath = $matches[1];
                $this->logger->log("Path from Nginx REQUEST_URI parsing: $filePath", 'DEBUG');
            }
        }
        
        // Method 6: Fallback - try to extract from any available source
        if (!$filePath) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            
            // Try to extract path after proxy.php
            if (preg_match('#proxy\.php[/\\\\](.+)#', $requestUri, $matches)) {
                $filePath = $matches[1];
                $filePath = strtok($filePath, '?'); // Remove query string
                $this->logger->log("Path from fallback parsing: $filePath", 'DEBUG');
            }
        }
        
        return $filePath;
    }
    
    private function buildSourceUrl($filePath) {
        // If it's already a full URL, return as is
        if (preg_match('#^https?://#i', $filePath)) {
            return $filePath;
        }
        
        // Build URL with source domain
        $path = '/' . ltrim($filePath, '/');
        return "https://{$this->sourceDomain}{$path}";
    }
    
    private function validateSecurity($filePath) {
        // Check file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($extension, BLOCKED_EXTENSIONS)) {
            $this->logger->log("Blocked extension: $extension", 'WARNING');
            return false;
        }
        
        // Check for directory traversal
        if (strpos($filePath, '..') !== false) {
            $this->logger->log("Directory traversal attempt: $filePath", 'WARNING');
            return false;
        }
        
        return true;
    }
    
    private function proxyFile($sourceUrl, $filePath) {
        // Set execution time and memory limits
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        // Get range header
        $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
        
        // Prepare headers for the request
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
        
        // First, get headers with HEAD request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $sourceUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $headResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($headResponse === false) {
            $this->errorResponse('Failed to connect to source server', 502);
            return;
        }
        
        // Parse and send headers
        $this->parseAndSendHeaders($headResponse, $httpCode, basename($filePath));
        
        // Stream the content
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $sourceUrl,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_BUFFERSIZE => 8192,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_WRITEFUNCTION => function($ch, $data) {
                if (connection_aborted()) {
                    return -1;
                }
                echo $data;
                while (ob_get_level()) {
                    ob_end_flush();
                }
                flush();
                return strlen($data);
            }
        ]);
        
        curl_exec($ch);
        curl_close($ch);
        
        $this->logger->log("File streamed successfully: $filePath");
    }
    
    private function parseAndSendHeaders($headResponse, $httpCode, $filename) {
        // Clear output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set status code
        http_response_code($httpCode == 206 ? 206 : 200);
        
        // Parse headers from response
        $lines = explode("\r\n", $headResponse);
        $contentType = null;
        $contentLength = null;
        $contentRange = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $lineLower = strtolower($line);
            if (strpos($lineLower, 'content-type:') === 0) {
                $contentType = trim(substr($line, 13));
            } elseif (strpos($lineLower, 'content-length:') === 0) {
                $contentLength = trim(substr($line, 15));
            } elseif (strpos($lineLower, 'content-range:') === 0) {
                $contentRange = trim(substr($line, 14));
            }
        }
        
        // Set appropriate headers
        if ($contentType) {
            header('Content-Type: ' . $contentType);
        } else {
            // Detect content type from filename
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeTypes = [
                'mp4' => 'video/mp4',
                'mkv' => 'video/x-matroska',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'webm' => 'video/webm'
            ];
            $mime = $mimeTypes[$extension] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
        }
        
        if ($contentLength) {
            header('Content-Length: ' . $contentLength);
        }
        
        if ($contentRange) {
            header('Content-Range: ' . $contentRange);
        }
        
        // Essential headers for video streaming
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        
        // CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
        header('Access-Control-Allow-Headers: Range, If-Range, If-Modified-Since, If-None-Match');
        
        // Cache headers
        header('Cache-Control: public, max-age=3600');
    }
    
    private function showUsage() {
        $serverInfo = $this->detector->getServerInfo();
        $config = $this->detector->generateRewriteConfig();
        
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universal PHP Proxy</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .server-info { background: #3498db; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .example { background: #ecf0f1; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .url { background: #34495e; color: #ecf0f1; padding: 10px; border-radius: 3px; font-family: monospace; word-break: break-all; }
        .config { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
        .success { background: #27ae60; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 Universal PHP Proxy</h1>
        
        <div class="server-info">
            <strong>🖥️ Server Detected:</strong> ' . ucfirst($serverInfo['type']) . ' ' . $serverInfo['version'] . '<br>
            <strong>📋 Full String:</strong> ' . htmlspecialchars($serverInfo['full_string']) . '
        </div>
        
        <div class="success">
            <strong>✅ Status:</strong> Universal proxy is active and compatible with both Apache and Nginx!
        </div>
        
        <h2>📋 Usage Examples:</h2>
        <div class="example">
            <strong>Method 1 - Path Parameter:</strong><br>
            <div class="url">https://yourdomain.com/universal_proxy.php?path=path/to/video.mp4</div>
            
            <strong>Method 2 - URL Parameter:</strong><br>
            <div class="url">https://yourdomain.com/universal_proxy.php?url=https://source.com/path/to/video.mp4</div>
            
            <strong>Method 3 - Pretty URLs (with rewrite rules):</strong><br>
            <div class="url">https://yourdomain.com/universal_proxy.php/path/to/video.mp4</div>
        </div>
        
        <h2>⚙️ Recommended Configuration:</h2>
        <div class="config">' . htmlspecialchars($config['config']) . '</div>
        
        <h2>🔧 Features:</h2>
        <ul>
            <li>✅ Universal compatibility (Apache + Nginx)</li>
            <li>✅ Automatic server detection</li>
            <li>✅ Large file support (up to 10GB)</li>
            <li>✅ Resume download support (HTTP Range)</li>
            <li>✅ High-speed streaming with cURL</li>
            <li>✅ Complete logging system</li>
            <li>✅ Enhanced security</li>
            <li>✅ CORS support for video streaming</li>
        </ul>
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
    <title>Error - Universal Proxy</title>
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
        <a href="/" class="back-link">← Back to Home</a>
    </div>
</body>
</html>';
    }
}

// Logger class (simplified version)
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

// Execute the proxy
$proxy = new UniversalProxy();
$proxy->handleRequest();
?>