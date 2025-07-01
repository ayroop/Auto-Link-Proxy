# Universal PHP Proxy - Apache & Nginx Compatible

This project provides a universal PHP proxy solution that works seamlessly with both Apache and Nginx web servers. It automatically detects the server type and configures itself accordingly.

## 🌟 Features

- ✅ **Universal Compatibility**: Works with both Apache and Nginx
- ✅ **Automatic Server Detection**: Detects server type and configures accordingly
- ✅ **Large File Support**: Handles files up to 10GB
- ✅ **Resume Downloads**: HTTP Range support for interrupted downloads
- ✅ **High Performance**: Optimized streaming with cURL
- ✅ **Security**: Built-in security measures and access controls
- ✅ **CORS Support**: Proper headers for video streaming
- ✅ **Logging**: Comprehensive logging system
- ✅ **Multiple URL Formats**: Supports various URL patterns

## 📁 File Structure

```
├── proxy.php                    # Original proxy (maintained for compatibility)
├── universal_proxy.php          # New universal proxy handler
├── server_detect.php           # Server detection utility
├── setup_universal.php         # Automatic setup script
├── config.php                  # Configuration file
├── .htaccess                   # Apache configuration
├── .htaccess_universal         # Universal Apache rules
├── nginx-ssl-config.conf       # Original Nginx config
├── nginx-universal-config.conf # Universal Nginx rules
├── nginx-complete-site.conf    # Complete Nginx site config
└── README_UNIVERSAL.md         # This file
```

## 🚀 Quick Setup

### Automatic Setup (Recommended)

1. Run the setup script:
```bash
php setup_universal.php
```

2. Update your `config.php` with your settings:
```php
define('SOURCE_DOMAIN', 'your-source-domain.com');
define('PROXY_DOMAIN', 'your-proxy-domain.com');
// ... other settings
```

3. Configure your web server (see detailed instructions below)

### Manual Setup

1. Copy the universal files to your web root
2. Configure your web server (Apache or Nginx)
3. Update `config.php` with your settings
4. Test the setup

## ⚙️ Server Configuration

### Apache Configuration

The system automatically updates your `.htaccess` file. For virtual hosts, use this template:

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/your/project
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Enable rewrite and allow overrides
    RewriteEngine On
    AllowOverride All
    
    <Directory "/path/to/your/project">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP settings for large files
        php_admin_value memory_limit 512M
        php_admin_value max_execution_time 0
        php_admin_value upload_max_filesize 10G
        php_admin_value post_max_size 10G
    </Directory>
</VirtualHost>
```

### Nginx Configuration

Add this to your Nginx server block:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/your/project;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Include universal proxy rules
    include /path/to/your/project/nginx-universal-config.conf;
    
    # Default handler
    location / {
        try_files $uri $uri/ /universal_proxy.php$is_args$args;
    }
}
```

## 📋 Usage Examples

The universal proxy supports multiple URL formats:

### Method 1: Query Parameter
```
https://yourdomain.com/universal_proxy.php?path=path/to/video.mp4
https://yourdomain.com/universal_proxy.php?url=https://source.com/video.mp4
```

### Method 2: Pretty URLs (with rewrite rules)
```
https://yourdomain.com/proxy/path/to/video.mp4
https://yourdomain.com/universal_proxy.php/path/to/video.mp4
```

### Method 3: Original Compatibility
```
https://yourdomain.com/proxy.php/path/to/video.mp4
```

## 🔧 Configuration Options

Update `config.php` with your settings:

```php
<?php
// Source domain (where files are hosted)
define('SOURCE_DOMAIN', 'source-server.com');

// Proxy domain (your domain)
define('PROXY_DOMAIN', 'your-domain.com');

// Proxy IP (optional)
define('PROXY_IP', 'your-server-ip');

// Logging
define('LOG_ENABLED', true);
define('LOG_FILE', __DIR__ . '/proxy.log');

// Security - blocked file extensions
define('BLOCKED_EXTENSIONS', ['php', 'exe', 'bat', 'sh']);
?>
```

## 🧪 Testing

### Test Server Detection
```bash
curl http://localhost/server_detect.php
```

### Test Proxy Functionality
```bash
# Test with query parameter
curl -I "https://yourdomain.com/universal_proxy.php?path=test/video.mp4"

# Test with pretty URL
curl -I "https://yourdomain.com/proxy/test/video.mp4"
```

### Test Range Requests
```bash
curl -H "Range: bytes=0-1023" "https://yourdomain.com/proxy/test/video.mp4"
```

## 🔍 Troubleshooting

### Common Issues

1. **404 Errors with Pretty URLs**
   - Apache: Ensure mod_rewrite is enabled
   - Nginx: Check that rewrite rules are properly included

2. **Large File Timeouts**
   - Increase PHP execution time limits
   - Check web server timeout settings

3. **Permission Errors**
   - Ensure proper file permissions (755 for directories, 644 for files)
   - Check that PHP can write to log files

4. **CORS Issues**
   - Verify CORS headers are being sent
   - Check browser developer tools for blocked requests

### Debug Mode

Enable debug logging in `config.php`:
```php
define('LOG_ENABLED', true);
define('DEBUG_MODE', true);
```

Check the log file for detailed information about requests and errors.

## 🔒 Security Considerations

- File extension filtering prevents execution of dangerous files
- Directory traversal protection
- Access control for sensitive files
- Rate limiting support (optional)
- CORS headers properly configured

## 📊 Performance Optimization

- Streaming with small buffer sizes for better responsiveness
- Proper caching headers for static content
- Compression enabled for text content
- Connection keep-alive for better performance

## 🆙 Migration from Original Proxy

The universal proxy maintains backward compatibility:

1. Keep your existing `proxy.php` file
2. Add the new universal files
3. Update your web server configuration
4. Test both old and new URL formats
5. Gradually migrate to the new system

## 📞 Support

For issues or questions:

1. Check the log files for error messages
2. Verify your server configuration
3. Test with the server detection script
4. Review the troubleshooting section

## 📄 License

This project maintains the same license as the original proxy system.

---

**Note**: This universal system is designed to work alongside your existing setup. You can gradually migrate from the original proxy to the universal system without breaking existing functionality.