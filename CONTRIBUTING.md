# Contributing to Auto Link Proxy

Thank you for your interest in contributing to Auto Link Proxy! This document provides guidelines for contributing to this project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- Use a clear and descriptive title
- Describe the exact steps which reproduce the problem
- Provide specific examples to demonstrate the steps
- Describe the behavior you observed after following the steps
- Explain which behavior you expected to see instead and why
- Include details about your configuration and environment

### Suggesting Enhancements

If you have a suggestion for a new feature or an improvement to an existing feature, please:

- Use a clear and descriptive title
- Provide a step-by-step description of the suggested enhancement
- Provide specific examples to demonstrate the steps
- Describe the current behavior and explain which behavior you expected to see instead

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- Apache/Nginx web server
- cURL extension enabled
- SSL certificate (for HTTPS)

### Local Development

1. Clone the repository:
   ```bash
   git clone https://github.com/ayroop/Auto-Link-Proxy.git
   cd Auto-Link-Proxy
   ```

2. Configure your web server to point to the project directory

3. Copy and configure the settings:
   ```bash
   cp config.php config.local.php
   # Edit config.local.php with your settings
   ```

4. Test the proxy functionality:
   ```bash
   # Open test_proxy.html in your browser
   # Or test directly: http://localhost/proxy.php?url=YOUR_TEST_URL
   ```

## Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Add tests if applicable
5. Ensure your code follows the existing style and conventions
6. Commit your changes (`git commit -m 'Add some amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

### Commit Message Guidelines

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

### Code Style Guidelines

- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Add comments for complex logic
- Keep functions small and focused
- Use proper error handling

## Testing

Before submitting a pull request, please ensure:

1. All existing tests pass
2. New functionality is tested
3. The proxy works with various file types and sizes
4. Error handling works correctly
5. Security measures are in place

## Security

If you discover a security vulnerability, please:

1. **Do NOT** create a public issue
2. Email us at security@filmkhabar.space
3. We will respond within 48 hours
4. We will work with you to fix the issue

## Documentation

When contributing, please ensure:

- README.md is updated if needed
- Code comments are clear and helpful
- Configuration examples are provided
- Installation instructions are accurate

## Questions?

If you have questions about contributing, please:

- Check the existing issues and discussions
- Email us at support@filmkhabar.space
- Create a new issue for general questions

Thank you for contributing to Auto Link Proxy! 