# Security Policy

## Supported Versions

Use this section to tell people about which versions of your project are currently being supported with security updates.

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of Auto Link Proxy seriously. If you believe you have found a security vulnerability, please report it to us as described below.

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to **security@filmkhabar.space**.

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the requested information listed below (as much as you can provide) to help us better understand the nature and scope of the possible issue:

- Type of issue (buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the vulnerability
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

This information will help us triage your report more quickly.

## Preferred Languages

We prefer all communications to be in English or Persian.

## Policy

Auto Link Proxy follows the principle of [Responsible Disclosure](https://en.wikipedia.org/wiki/Responsible_disclosure).

## Security Best Practices

When using Auto Link Proxy, please follow these security recommendations:

### Server Configuration

1. **Use HTTPS**: Always use HTTPS for your proxy server
2. **Keep Software Updated**: Regularly update PHP, web server, and operating system
3. **Firewall Configuration**: Configure firewalls to allow only necessary traffic
4. **Access Logs**: Monitor access logs for suspicious activity

### Application Security

1. **Input Validation**: Always validate and sanitize user inputs
2. **Output Encoding**: Properly encode output to prevent XSS attacks
3. **Error Handling**: Don't expose sensitive information in error messages
4. **Rate Limiting**: Implement rate limiting to prevent abuse

### File Access

1. **File Permissions**: Set appropriate file permissions (644 for files, 755 for directories)
2. **Directory Listing**: Disable directory listing in web server configuration
3. **Sensitive Files**: Protect configuration files from public access

### Monitoring

1. **Log Monitoring**: Regularly check logs for unusual activity
2. **Performance Monitoring**: Monitor server performance and resource usage
3. **Security Scanning**: Use security scanning tools to identify vulnerabilities

## Security Features

Auto Link Proxy includes several security features:

- **Domain Whitelisting**: Only allows requests to specified domains
- **File Extension Filtering**: Restricts file types that can be proxied
- **Input Sanitization**: Sanitizes all user inputs
- **Error Handling**: Secure error handling without information disclosure
- **Rate Limiting**: Built-in rate limiting capabilities
- **SSL/TLS Support**: Full support for secure connections

## Updates

Security updates will be released as patch versions (e.g., 1.0.1, 1.0.2) and will be clearly marked as security updates in the release notes.

## Acknowledgments

We would like to thank all security researchers who responsibly disclose vulnerabilities to us. Your contributions help make Auto Link Proxy more secure for everyone.

## Contact

For security-related questions or concerns, please contact:

- **Security Email**: security@filmkhabar.space
- **General Support**: support@filmkhabar.space
- **Website**: https://filmkhabar.space 