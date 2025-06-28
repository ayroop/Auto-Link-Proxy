# Auto Link Proxy

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![GitHub release](https://img.shields.io/github/release/ayroop/Auto-Link-Proxy.svg)](https://github.com/ayroop/Auto-Link-Proxy/releases)
[![GitHub issues](https://img.shields.io/github/issues/ayroop/Auto-Link-Proxy.svg)](https://github.com/ayroop/Auto-Link-Proxy/issues)
[![GitHub stars](https://img.shields.io/github/stars/ayroop/Auto-Link-Proxy.svg)](https://github.com/ayroop/Auto-Link-Proxy/stargazers)

A powerful PHP proxy solution designed to bypass download restrictions for Iranian users by proxying files from external servers through Iranian servers. Includes a WordPress plugin for automatic link conversion.

## 🌟 Features

### Core Proxy Functionality
- ✅ **Secure File Proxying**: Proxy files through Iranian servers
- ✅ **Large File Support**: Handle files up to 10GB (4K videos)
- ✅ **Resume Downloads**: Support for range requests
- ✅ **Multiple Formats**: Support for mp4, avi, mkv, mov, wmv, flv, webm, m4v, ts, mts, m2ts
- ✅ **Memory Optimization**: Efficient handling of large video files
- ✅ **SSL/TLS Support**: Full HTTPS support

### Security Features
- 🔒 **Domain Whitelisting**: Only allow specific domains
- 🔒 **File Extension Filtering**: Restrict file types
- 🔒 **Input Validation**: Sanitize all inputs
- 🔒 **Rate Limiting**: Prevent abuse
- 🔒 **Error Handling**: Secure error messages

### WordPress Integration
- 🚀 **Automatic Link Conversion**: Convert direct links automatically
- 🚀 **Shortcode Support**: Manual link conversion
- 🚀 **Admin Panel**: Easy configuration interface
- 🚀 **Persian UI**: Full Persian language support
- 🚀 **Connection Testing**: Built-in proxy testing
- 🚀 **Debug Mode**: Development assistance

## 📋 Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [WordPress Plugin](#wordpress-plugin)
- [Usage Examples](#usage-examples)
- [API Reference](#api-reference)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [Support](#support)

## 🚀 Installation

### Prerequisites

- PHP 7.4 or higher
- Apache/Nginx web server
- cURL extension enabled
- SSL certificate (recommended)

### Quick Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ayroop/Auto-Link-Proxy.git
   cd Auto-Link-Proxy
   ```

2. **Configure your server:**
   ```bash
   # Copy configuration file
   cp config.php config.local.php
   # Edit with your settings
   nano config.local.php
   ```

3. **Set up web server:**
   ```bash
   # For Apache, copy .htaccess
   cp .htaccess /var/www/html/
   
   # For Nginx, use the provided configuration
   sudo cp nginx.conf /etc/nginx/sites-available/auto-link-proxy
   ```

4. **Test the installation:**
   ```bash
   # Open in browser
   http://your-domain.com/test_proxy.html
   ```

## ⚡ Quick Start

### Basic Usage

1. **Direct Proxy Access:**
   ```
   https://your-domain.com/proxy.php?url=https://sv1.cinetory.space/file.mp4
   ```

2. **WordPress Integration:**
   - Install the WordPress plugin from `wordpress-plugin/`
   - Activate the plugin
   - Configure settings in WordPress admin
   - Links are automatically converted

3. **Manual Link Conversion:**
   ```php
   // Using link_rewriter.php
   $proxy_url = convertToProxyUrl('https://sv1.cinetory.space/file.mp4');
   ```

### Example Configuration

```php
// config.php
$config = [
    'proxy_domain' => 'filmkhabar.space',
    'proxy_ip' => '185.235.196.22',
    'use_ip' => false,
    'allowed_hosts' => ['sv1.cinetory.space'],
    'allowed_extensions' => ['mp4', 'avi', 'mkv', 'mov'],
    'max_file_size' => '10G',
    'memory_limit' => '512M',
    'timeout' => 300,
    'buffer_size' => 1048576,
    'enable_logging' => true,
    'log_file' => 'proxy.log'
];
```

## 🔧 Configuration

### Server Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `proxy_domain` | `filmkhabar.space` | Your Iranian server domain |
| `proxy_ip` | `185.235.196.22` | Your Iranian server IP |
| `use_ip` | `false` | Use IP instead of domain |
| `max_file_size` | `10G` | Maximum file size to proxy |
| `memory_limit` | `512M` | PHP memory limit |
| `timeout` | `300` | cURL timeout in seconds |
| `buffer_size` | `1048576` | Buffer size for streaming |

### Security Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `allowed_hosts` | `['sv1.cinetory.space']` | Whitelisted domains |
| `allowed_extensions` | `['mp4', 'avi', 'mkv']` | Allowed file extensions |
| `enable_logging` | `true` | Enable access logging |
| `rate_limit` | `100` | Requests per minute |

## 🎯 WordPress Plugin

### Installation

1. Copy `wordpress-plugin/` to `wp-content/plugins/auto-proxy-links/`
2. Activate the plugin in WordPress admin
3. Go to "Settings > Auto Proxy Links"
4. Configure your proxy settings

### Features

- **Automatic Conversion**: Converts direct links automatically
- **Shortcode Support**: `[proxy_link url="..." text="..."]`
- **Admin Interface**: Persian UI with tabs
- **Connection Testing**: Built-in proxy testing
- **Debug Mode**: Development assistance

### Shortcode Usage

```php
// Basic usage
[proxy_link url="https://sv1.cinetory.space/file.mp4" text="دانلود"]

// With custom class
[proxy_link url="..." text="..." class="custom-button"]
```

## 📖 Usage Examples

### Basic Proxy Request

```php
// Direct proxy access
$url = 'https://filmkhabar.space/proxy.php?url=' . urlencode('https://sv1.cinetory.space/file.mp4');
```

### WordPress Integration

```php
// In your theme or plugin
$proxy_url = AutoProxyLinks::convertUrl($original_url);

// Test connection
AutoProxyLinks::testConnection(function($success, $message) {
    if ($success) {
        echo "Connection successful: " . $message;
    } else {
        echo "Connection failed: " . $message;
    }
});
```

### Custom Implementation

```php
// Using link_rewriter.php
require_once 'link_rewriter.php';

$content = 'Download from https://sv1.cinetory.space/file.mp4';
$converted_content = convertLinksInContent($content);
```

## 🔌 API Reference

### Core Functions

#### `proxyFile($url, $config)`
Proxies a file from the given URL.

**Parameters:**
- `$url` (string): The URL to proxy
- `$config` (array): Configuration array

**Returns:** void (outputs file directly)

#### `convertToProxyUrl($url, $config)`
Converts a direct URL to a proxy URL.

**Parameters:**
- `$url` (string): Original URL
- `$config` (array): Configuration array

**Returns:** string - Proxy URL

#### `validateUrl($url, $config)`
Validates if a URL can be proxied.

**Parameters:**
- `$url` (string): URL to validate
- `$config` (array): Configuration array

**Returns:** bool - True if valid

### WordPress Plugin API

#### `AutoProxyLinks::convertUrl($url)`
Converts a URL to proxy format.

#### `AutoProxyLinks::testConnection($callback)`
Tests proxy connection.

#### `AutoProxyLinks::getSettings()`
Gets plugin settings.

## 🔒 Security

### Best Practices

1. **Use HTTPS**: Always use HTTPS for your proxy server
2. **Domain Whitelisting**: Only allow trusted domains
3. **File Extension Filtering**: Restrict file types
4. **Rate Limiting**: Prevent abuse
5. **Input Validation**: Sanitize all inputs
6. **Error Handling**: Don't expose sensitive information

### Security Features

- Domain whitelisting
- File extension filtering
- Input validation and sanitization
- Rate limiting capabilities
- Secure error handling
- SSL/TLS support

### Configuration Security

```apache
# .htaccess security settings
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

# Rate limiting
<IfModule mod_ratelimit.c>
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 400
</IfModule>
```

## 🛠️ Troubleshooting

### Common Issues

#### 1. "File not found" Error
**Cause**: File doesn't exist or server is down
**Solution**: Check the original URL and server status

#### 2. "Memory limit exceeded" Error
**Cause**: File is too large for current memory limit
**Solution**: Increase `memory_limit` in config.php

#### 3. "Connection timeout" Error
**Cause**: Slow connection or server issues
**Solution**: Increase `timeout` value in config.php

#### 4. WordPress Plugin Not Working
**Cause**: Plugin not properly configured
**Solution**: Check settings in WordPress admin

### Debug Mode

Enable debug mode in configuration:

```php
$config['debug_mode'] = true;
$config['log_file'] = 'debug.log';
```

### Log Analysis

```bash
# Check proxy logs
tail -f proxy.log

# Check PHP error logs
tail -f /var/log/php_errors.log

# Check web server logs
tail -f /var/log/apache2/access.log
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable names
- Add comments for complex logic
- Keep functions small and focused

## 📞 Support

### Contact Information

- **Email**: support@filmkhabar.space
- **Website**: https://filmkhabar.space
- **GitHub Issues**: [Create an issue](https://github.com/ayroop/Auto-Link-Proxy/issues)

### Documentation

- [Setup Guide](SETUP_GUIDE.md) - Detailed installation instructions
- [Quick Config](QUICK_CONFIG.md) - Quick configuration reference
- [WordPress Plugin Guide](wordpress-plugin/install-guide.md) - Plugin installation
- [API Documentation](API.md) - Complete API reference

### Community

- [GitHub Discussions](https://github.com/ayroop/Auto-Link-Proxy/discussions)
- [Issues](https://github.com/ayroop/Auto-Link-Proxy/issues)
- [Releases](https://github.com/ayroop/Auto-Link-Proxy/releases)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Thanks to all contributors
- Inspired by the need for accessible content in Iran
- Built with security and performance in mind

## 📊 Project Status

- **Version**: 1.0.0
- **Status**: Production Ready
- **PHP Support**: 7.4+
- **WordPress Support**: 5.0+
- **Last Updated**: January 2024

---

**Made with ❤️ for the Iranian community**

[![GitHub stars](https://img.shields.io/github/stars/ayroop/Auto-Link-Proxy.svg?style=social&label=Star)](https://github.com/ayroop/Auto-Link-Proxy)
[![GitHub forks](https://img.shields.io/github/forks/ayroop/Auto-Link-Proxy.svg?style=social&label=Fork)](https://github.com/ayroop/Auto-Link-Proxy/fork)
[![GitHub watchers](https://img.shields.io/github/watchers/ayroop/Auto-Link-Proxy.svg?style=social&label=Watch)](https://github.com/ayroop/Auto-Link-Proxy)
