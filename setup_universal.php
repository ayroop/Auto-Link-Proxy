<?php
/**
 * Universal Setup Script
 * Automatically configures the proxy for both Apache and Nginx
 */

class UniversalSetup {
    private $projectRoot;
    private $backupDir;
    
    public function __construct() {
        $this->projectRoot = __DIR__;
        $this->backupDir = $this->projectRoot . '/backups';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function setup() {
        echo "🚀 Universal Proxy Setup Starting...\n\n";
        
        // Step 1: Backup existing files
        $this->backupExistingFiles();
        
        // Step 2: Setup Apache configuration
        $this->setupApache();
        
        // Step 3: Setup Nginx configuration
        $this->setupNginx();
        
        // Step 4: Test the setup
        $this->testSetup();
        
        // Step 5: Show usage instructions
        $this->showInstructions();
        
        echo "\n✅ Universal Proxy Setup Complete!\n";
    }
    
    private function backupExistingFiles() {
        echo "📦 Creating backups...\n";
        
        $filesToBackup = [
            '.htaccess',
            'nginx-ssl-config.conf',
            'proxy.php'
        ];
        
        foreach ($filesToBackup as $file) {
            $sourcePath = $this->projectRoot . '/' . $file;
            if (file_exists($sourcePath)) {
                $backupPath = $this->backupDir . '/' . $file . '.' . date('Y-m-d_H-i-s');
                copy($sourcePath, $backupPath);
                echo "   ✓ Backed up: $file\n";
            }
        }
        echo "\n";
    }
    
    private function setupApache() {
        echo "🔧 Setting up Apache configuration...\n";
        
        // Copy universal .htaccess
        $sourceHtaccess = $this->projectRoot . '/.htaccess_universal';
        $targetHtaccess = $this->projectRoot . '/.htaccess';
        
        if (file_exists($sourceHtaccess)) {
            copy($sourceHtaccess, $targetHtaccess);
            echo "   ✓ Updated .htaccess with universal rules\n";
        } else {
            echo "   ⚠️ Warning: .htaccess_universal not found\n";
        }
        
        // Create Apache-specific configuration file
        $apacheConfig = $this->generateApacheVirtualHost();
        file_put_contents($this->projectRoot . '/apache-vhost.conf', $apacheConfig);
        echo "   ✓ Created apache-vhost.conf\n";
        
        echo "\n";
    }
    
    private function setupNginx() {
        echo "🔧 Setting up Nginx configuration...\n";
        
        // Copy universal nginx config
        $sourceNginx = $this->projectRoot . '/nginx-universal-config.conf';
        $targetNginx = $this->projectRoot . '/nginx-site.conf';
        
        if (file_exists($sourceNginx)) {
            copy($sourceNginx, $targetNginx);
            echo "   ✓ Created nginx-site.conf with universal rules\n";
        } else {
            echo "   ⚠️ Warning: nginx-universal-config.conf not found\n";
        }
        
        // Create complete Nginx site configuration
        $nginxSiteConfig = $this->generateNginxSiteConfig();
        file_put_contents($this->projectRoot . '/nginx-complete-site.conf', $nginxSiteConfig);
        echo "   ✓ Created nginx-complete-site.conf\n";
        
        echo "\n";
    }
    
    private function testSetup() {
        echo "🧪 Testing setup...\n";
        
        // Test server detection
        if (file_exists($this->projectRoot . '/server_detect.php')) {
            echo "   ✓ Server detection script available\n";
        }
        
        // Test universal proxy
        if (file_exists($this->projectRoot . '/universal_proxy.php')) {
            echo "   ✓ Universal proxy script available\n";
        }
        
        // Test original proxy compatibility
        if (file_exists($this->projectRoot . '/proxy.php')) {
            echo "   ✓ Original proxy script available\n";
        }
        
        // Test configuration files
        if (file_exists($this->projectRoot . '/config.php')) {
            echo "   ✓ Configuration file available\n";
        } else {
            echo "   ⚠️ Warning: config.php not found - you may need to create it\n";
        }
        
        echo "\n";
    }
    
    private function generateApacheVirtualHost() {
        return '# Apache Virtual Host Configuration
# Add this to your Apache configuration or create a new .conf file in sites-available

<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /path/to/your/project
    
    # Enable rewrite module
    RewriteEngine On
    
    # Include the .htaccess rules
    AllowOverride All
    
    # Directory permissions
    <Directory "/path/to/your/project">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP settings for large files
        php_admin_value memory_limit 512M
        php_admin_value max_execution_time 0
        php_admin_value max_input_time 0
        php_admin_value upload_max_filesize 10G
        php_admin_value post_max_size 10G
    </Directory>
    
    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/proxy_error.log
    CustomLog ${APACHE_LOG_DIR}/proxy_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /path/to/your/project
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    
    # Enable rewrite module
    RewriteEngine On
    
    # Include the .htaccess rules
    AllowOverride All
    
    # Directory permissions
    <Directory "/path/to/your/project">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP settings for large files
        php_admin_value memory_limit 512M
        php_admin_value max_execution_time 0
        php_admin_value max_input_time 0
        php_admin_value upload_max_filesize 10G
        php_admin_value post_max_size 10G
    </Directory>
    
    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/proxy_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/proxy_ssl_access.log combined
</VirtualHost>';
    }
    
    private function generateNginxSiteConfig() {
        return '# Complete Nginx Site Configuration
# Save this as /etc/nginx/sites-available/yourdomain.com
# Then create symlink: ln -s /etc/nginx/sites-available/yourdomain.com /etc/nginx/sites-enabled/

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /path/to/your/project;
    index index.php index.html universal_proxy.php;
    
    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    
    # Include the universal configuration
    include /path/to/your/project/nginx-universal-config.conf;
    
    # Logging
    access_log /var/log/nginx/proxy_access.log;
    error_log /var/log/nginx/proxy_error.log;
    
    # Default location
    location / {
        try_files $uri $uri/ /universal_proxy.php$is_args$args;
    }
}';
    }
    
    private function showInstructions() {
        echo "📋 Setup Instructions:\n\n";
        
        echo "🔸 For Apache:\n";
        echo "   1. Make sure mod_rewrite is enabled\n";
        echo "   2. The .htaccess file has been updated automatically\n";
        echo "   3. Use the apache-vhost.conf as a template for your virtual host\n";
        echo "   4. Restart Apache: sudo systemctl restart apache2\n\n";
        
        echo "🔸 For Nginx:\n";
        echo "   1. Copy nginx-complete-site.conf to /etc/nginx/sites-available/\n";
        echo "   2. Update the paths in the configuration file\n";
        echo "   3. Create symlink: ln -s /etc/nginx/sites-available/yoursite /etc/nginx/sites-enabled/\n";
        echo "   4. Test config: nginx -t\n";
        echo "   5. Restart Nginx: sudo systemctl restart nginx\n\n";
        
        echo "🔸 Usage Examples:\n";
        echo "   • Query parameter: https://yourdomain.com/universal_proxy.php?path=video.mp4\n";
        echo "   • Pretty URL: https://yourdomain.com/proxy/path/to/video.mp4\n";
        echo "   • Original compatibility: https://yourdomain.com/proxy.php/path/to/video.mp4\n\n";
        
        echo "🔸 Testing:\n";
        echo "   • Visit: https://yourdomain.com/universal_proxy.php (should show usage page)\n";
        echo "   • Check server detection: https://yourdomain.com/server_detect.php (localhost only)\n\n";
        
        echo "🔸 Configuration:\n";
        echo "   • Update config.php with your source domain and other settings\n";
        echo "   • Check file permissions (755 for directories, 644 for files)\n";
        echo "   • Ensure PHP has write permissions for log files\n\n";
    }
}

// Run setup if called directly
if (basename($_SERVER['PHP_SELF']) === 'setup_universal.php') {
    $setup = new UniversalSetup();
    $setup->setup();
}
?>