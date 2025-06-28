# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- WordPress plugin for automatic link conversion
- Persian language support
- Advanced configuration options
- Connection testing functionality

### Changed
- Improved error handling
- Enhanced security features
- Better documentation

### Fixed
- Various bug fixes and improvements

## [1.0.0] - 2024-01-01

### Added
- Initial release of Auto Link Proxy
- PHP proxy script with Persian error messages
- Support for large file downloads (up to 10GB)
- Range request support for resume downloads
- Host-based filtering for security
- Configurable logging system
- WordPress plugin integration
- Link rewriter functionality
- Comprehensive documentation in Persian
- Server configuration files (.htaccess, php_settings.ini)
- Test files and examples

### Features
- **Core Proxy Functionality**
  - Secure file proxying through Iranian servers
  - Support for multiple file formats (mp4, avi, mkv, etc.)
  - Resume download support
  - Large file handling (up to 10GB)
  - Memory optimization for 4K videos

- **Security Features**
  - Domain whitelisting
  - File extension filtering
  - Input validation and sanitization
  - Rate limiting capabilities
  - SSL/TLS support

- **WordPress Integration**
  - Automatic link conversion
  - Shortcode support
  - Admin settings panel
  - Persian user interface
  - Connection testing
  - Debug mode

- **Configuration**
  - Flexible domain/IP switching
  - Customizable allowed hosts
  - Configurable file extensions
  - Logging options
  - Performance settings

### Technical Specifications
- **PHP Version**: 7.4+
- **Memory Limit**: 512MB (configurable)
- **Max File Size**: 10GB
- **Timeout**: 5 minutes
- **Buffer Size**: 1MB
- **Supported Protocols**: HTTP/HTTPS

### Documentation
- Complete README in Persian
- Installation guide
- Setup instructions
- Configuration examples
- Troubleshooting guide
- Security best practices

### Files Included
- `proxy.php` - Main proxy script
- `config.php` - Configuration file
- `link_rewriter.php` - Link conversion utility
- `wordpress-plugin/` - WordPress plugin
- `test_proxy.html` - Testing interface
- `README.md` - Main documentation
- `SETUP_GUIDE.md` - Setup instructions
- `QUICK_CONFIG.md` - Quick configuration
- `php_settings.ini` - PHP server settings
- `.htaccess` - Apache configuration

## [0.9.0] - 2023-12-15

### Added
- Beta version with basic proxy functionality
- Initial Persian language support
- Basic configuration system

### Changed
- Improved error handling
- Enhanced security measures

### Fixed
- Memory usage optimization
- Connection timeout issues

## [0.8.0] - 2023-12-01

### Added
- Alpha version with core features
- Basic file proxying
- Simple configuration

### Changed
- Initial development version

---

## Version History

- **1.0.0** - Production-ready release with full features
- **0.9.0** - Beta version with core functionality
- **0.8.0** - Alpha version for initial testing

## Release Notes

### Version 1.0.0
This is the first stable release of Auto Link Proxy. It includes all core features needed for production use:

- Complete proxy functionality
- WordPress plugin integration
- Comprehensive documentation
- Security features
- Performance optimization

### Upcoming Features
- Additional file format support
- Advanced caching system
- Multi-server load balancing
- API endpoints for external integration
- Mobile app support
- Advanced analytics and monitoring

## Support

For support and questions:
- Email: support@filmkhabar.space
- Website: https://filmkhabar.space
- Documentation: See README.md and SETUP_GUIDE.md

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project. 