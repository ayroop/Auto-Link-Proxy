<?php
/**
 * Server Detection and Auto-Configuration Script
 * Automatically detects Apache or Nginx and configures the proxy accordingly
 */

class ServerDetector {
    private $serverType = null;
    private $serverVersion = null;
    
    public function __construct() {
        $this->detectServer();
    }
    
    private function detectServer() {
        // Method 1: Check SERVER_SOFTWARE
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE']);
            
            if (strpos($serverSoftware, 'apache') !== false) {
                $this->serverType = 'apache';
                $this->serverVersion = $this->extractVersion($serverSoftware, 'apache');
            } elseif (strpos($serverSoftware, 'nginx') !== false) {
                $this->serverType = 'nginx';
                $this->serverVersion = $this->extractVersion($serverSoftware, 'nginx');
            }
        }
        
        // Method 2: Check for Apache-specific variables
        if (!$this->serverType) {
            if (function_exists('apache_get_version') || 
                isset($_SERVER['APACHE_PID_FILE']) ||
                isset($_SERVER['SERVER_ADMIN'])) {
                $this->serverType = 'apache';
            }
        }
        
        // Method 3: Check for Nginx-specific headers
        if (!$this->serverType) {
            $headers = getallheaders();
            foreach ($headers as $name => $value) {
                if (stripos($name, 'nginx') !== false || stripos($value, 'nginx') !== false) {
                    $this->serverType = 'nginx';
                    break;
                }
            }
        }
        
        // Method 4: Check environment variables
        if (!$this->serverType) {
            if (getenv('NGINX_VERSION') || isset($_ENV['NGINX_VERSION'])) {
                $this->serverType = 'nginx';
            } elseif (getenv('APACHE_VERSION') || isset($_ENV['APACHE_VERSION'])) {
                $this->serverType = 'apache';
            }
        }
        
        // Default fallback
        if (!$this->serverType) {
            $this->serverType = 'unknown';
        }
    }
    
    private function extractVersion($serverString, $serverName) {
        $pattern = '/' . $serverName . '\/([0-9\.]+)/i';
        if (preg_match($pattern, $serverString, $matches)) {
            return $matches[1];
        }
        return 'unknown';
    }
    
    public function getServerType() {
        return $this->serverType;
    }
    
    public function getServerVersion() {
        return $this->serverVersion;
    }
    
    public function isApache() {
        return $this->serverType === 'apache';
    }
    
    public function isNginx() {
        return $this->serverType === 'nginx';
    }
    
    public function getServerInfo() {
        return [
            'type' => $this->serverType,
            'version' => $this->serverVersion,
            'full_string' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ];
    }
    
    /**
     * Generate appropriate rewrite rules based on server type
     */
    public function generateRewriteConfig() {
        if ($this->isApache()) {
            return $this->generateApacheConfig();
        } elseif ($this->isNginx()) {
            return $this->generateNginxConfig();
        } else {
            return $this->generateGenericConfig();
        }
    }
    
    private function generateApacheConfig() {
        return [
            'type' => 'apache',
            'config' => '
# Apache Configuration for PHP Proxy
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Proxy URL rewriting
    RewriteRule ^proxy\.php/(.*)$ proxy.php?path=$1 [L,QSA]
    
    # Handle direct proxy calls
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^proxy\.php$ proxy.php [L]
</IfModule>
            '
        ];
    }
    
    private function generateNginxConfig() {
        return [
            'type' => 'nginx',
            'config' => '
# Nginx Configuration for PHP Proxy
location ~ ^/proxy\.php/(.*)$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root/proxy.php;
    fastcgi_param PATH_INFO $1;
    fastcgi_param QUERY_STRING path=$1&$query_string;
    include fastcgi_params;
    
    # Large file settings
    fastcgi_buffering off;
    fastcgi_request_buffering off;
    fastcgi_read_timeout 300;
    fastcgi_send_timeout 300;
}

location = /proxy.php {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
            '
        ];
    }
    
    private function generateGenericConfig() {
        return [
            'type' => 'generic',
            'config' => 'Server type could not be determined. Please configure manually.'
        ];
    }
}

// Usage example
if (basename($_SERVER['PHP_SELF']) === 'server_detect.php') {
    $detector = new ServerDetector();
    $info = $detector->getServerInfo();
    $config = $detector->generateRewriteConfig();
    
    header('Content-Type: application/json');
    echo json_encode([
        'server_info' => $info,
        'recommended_config' => $config
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>