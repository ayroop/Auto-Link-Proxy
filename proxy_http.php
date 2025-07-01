<?php
/**
 * HTTP-only proxy for ISPs that block HTTPS
 * Use this as fallback when HTTPS proxy fails
 */

require_once 'config.php';

// Force HTTP instead of HTTPS for source
class HttpVideoProxy {
    private $logger;
    private $sourceDomain;
    
    public function __construct() {
        $this->logger = new ProxyLogger(LOG_FILE, LOG_ENABLED);
        $this->sourceDomain = SOURCE_DOMAIN;
    }
    
    public function handleRequest() {
        $urlParam = $_GET['url'] ?? '';
        if (empty($urlParam)) {
            http_response_code(400);
            echo "Missing URL parameter";
            return;
        }
        
        // Force HTTP instead of HTTPS
        $sourceUrl = "http://{$this->sourceDomain}" . (strpos($urlParam, '/') === 0 ? $urlParam : "/$urlParam");
        $this->logger->log("HTTP URL: $sourceUrl");
        
        $this->streamFile($sourceUrl);
    }
    
    private function streamFile($sourceUrl) {
        $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $sourceUrl,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_BUFFERSIZE => 8192,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADERFUNCTION => function($ch, $header) {
                if (strpos(strtolower($header), 'content-type:') === 0) {
                    header($header);
                } elseif (strpos(strtolower($header), 'content-length:') === 0) {
                    header($header);
                } elseif (strpos(strtolower($header), 'content-range:') === 0) {
                    header($header);
                } elseif (strpos(strtolower($header), 'accept-ranges:') === 0) {
                    header($header);
                }
                return strlen($header);
            },
            CURLOPT_WRITEFUNCTION => function($ch, $data) {
                echo $data;
                flush();
                return connection_aborted() ? -1 : strlen($data);
            }
        ]);
        
        if (!empty($rangeHeader)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Range: $rangeHeader"]);
        }
        
        // Set basic headers
        header('Accept-Ranges: bytes');
        header('Access-Control-Allow-Origin: *');
        
        curl_exec($ch);
        curl_close($ch);
    }
}

$proxy = new HttpVideoProxy();
$proxy->handleRequest();
?>