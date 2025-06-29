#!/bin/bash

# Auto-Link-Proxy - Single Command Deployment Script
# برای استقرار خودکار پروکسی PHP روی Ubuntu VPS
# GitHub: https://github.com/ayroop/Auto-Link-Proxy
# Author: ayroop
# Version: 1.0

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration - UPDATE THESE VALUES
DOMAIN="filmkhabar.space"
EMAIL="your-email@example.com"
PROXY_DIR="/var/www/proxy"
GITHUB_REPO="https://github.com/ayroop/Auto-Link-Proxy"
GITHUB_RAW="https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root"
        print_error "Please run: sudo bash deploy.sh"
        exit 1
    fi
}

# Function to check system requirements
check_system() {
    print_status "Checking system requirements..."
    
    # Check if Ubuntu
    if ! grep -q "Ubuntu" /etc/os-release; then
        print_error "This script is designed for Ubuntu systems"
        exit 1
    fi
    
    # Check available memory
    MEMORY=$(free -m | awk 'NR==2{printf "%.0f", $2}')
    if [ "$MEMORY" -lt 512 ]; then
        print_warning "Low memory detected (${MEMORY}MB). Recommended: 1GB+"
    fi
    
    print_success "System requirements check passed"
}

# Function to update system
update_system() {
    print_status "Updating system packages..."
    
    export DEBIAN_FRONTEND=noninteractive
    apt update -qq
    apt upgrade -y -qq
    
    print_success "System updated successfully"
}

# Function to install required packages
install_packages() {
    print_status "Installing required packages..."
    
    # Add PHP repository for Ubuntu
    add-apt-repository ppa:ondrej/php -y
    
    apt update -qq
    
    apt install -y -qq \
        curl \
        wget \
        git \
        unzip \
        nginx \
        php8.1-fpm \
        php8.1-curl \
        php8.1-mbstring \
        php8.1-opcache \
        php8.1-zip \
        certbot \
        python3-certbot-nginx \
        ufw \
        fail2ban \
        htop \
        iftop \
        net-tools
    
    print_success "Packages installed successfully"
}

# Function to configure Nginx
configure_nginx() {
    print_status "Configuring Nginx..."
    
    # Remove default site
    rm -f /etc/nginx/sites-enabled/default
    
    # Create proxy site configuration
    cat > /etc/nginx/sites-available/proxy << 'EOF'
server {
    listen 80;
    server_name filmkhabar.space www.filmkhabar.space;
    root /var/www/proxy;
    index proxy.php;

    # Large file settings
    client_max_body_size 10G;
    client_body_timeout 300s;
    client_header_timeout 300s;
    proxy_connect_timeout 300s;
    proxy_send_timeout 300s;
    proxy_read_timeout 300s;

    # Performance settings
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
    
    # Security headers
    server_tokens off;
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;

    # CORS headers
    add_header Access-Control-Allow-Origin "*";
    add_header Access-Control-Allow-Methods "GET, HEAD, OPTIONS";
    add_header Access-Control-Allow-Headers "Range, If-Range";

    location / {
        try_files $uri $uri/ /proxy.php?$args;
    }

    # Handle proxy.php with path rewriting
    location ~ ^/proxy\.php/(.*)$ {
        rewrite ^/proxy\.php/(.*)$ /proxy.php?path=$1 last;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index proxy.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Large file settings
        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
        fastcgi_connect_timeout 300s;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Block sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(htaccess|htpasswd|ini|log|sh|sql|conf)$ {
        deny all;
    }

    # Cache video files
    location ~* \.(mp4|avi|mkv|mov|wmv|flv|webm)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
}
EOF

    # Enable site
    ln -sf /etc/nginx/sites-available/proxy /etc/nginx/sites-enabled/
    
    print_success "Nginx configured successfully"
}

# Function to configure PHP
configure_php() {
    print_status "Configuring PHP..."
    
    # Create proxy directory
    mkdir -p $PROXY_DIR
    
    # PHP settings for large files (based on your php_settings.ini)
    cat > /etc/php/8.1/fpm/conf.d/99-proxy.ini << 'EOF'
; PHP settings for large files
memory_limit = 2G
max_execution_time = 0
max_input_time = 0
post_max_size = 10G
upload_max_filesize = 10G
max_file_uploads = 100

; Buffer settings
output_buffering = 4096
implicit_flush = On

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 0
opcache.fast_shutdown = 1

; Realpath cache
realpath_cache_size = 4096K
realpath_cache_ttl = 600
EOF

    # Optimize PHP-FPM for low memory
    sed -i 's/pm = dynamic/pm = ondemand/' /etc/php/8.1/fpm/pool.d/www.conf
    sed -i 's/pm.max_children = 5/pm.max_children = 10/' /etc/php/8.1/fpm/pool.d/www.conf
    sed -i 's/pm.start_servers = 2/pm.start_servers = 1/' /etc/php/8.1/fpm/pool.d/www.conf
    sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 0/' /etc/php/8.1/fpm/pool.d/www.conf
    sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 1/' /etc/php/8.1/fpm/pool.d/www.conf
    
    print_success "PHP configured successfully"
}

# Function to download proxy files from GitHub
download_files() {
    print_status "Downloading proxy files from GitHub..."
    
    cd $PROXY_DIR
    
    # Download main files from your GitHub repository
    wget -q $GITHUB_RAW/proxy.php -O proxy.php
    wget -q $GITHUB_RAW/config.php -O config.php
    wget -q $GITHUB_RAW/test_proxy.html -O test_proxy.html
    wget -q $GITHUB_RAW/link_rewriter.php -O link_rewriter.php
    wget -q $GITHUB_RAW/php_settings.ini -O php_settings.ini
    
    # Download .htaccess for Apache compatibility
    wget -q $GITHUB_RAW/.htaccess -O .htaccess
    
    # Create logs directory
    mkdir -p $PROXY_DIR/logs
    touch $PROXY_DIR/logs/proxy_log.txt
    touch $PROXY_DIR/logs/proxy_stats.json
    
    # Set permissions
    chown -R www-data:www-data $PROXY_DIR
    chmod -R 755 $PROXY_DIR
    chmod 644 $PROXY_DIR/*.php
    chmod 644 $PROXY_DIR/*.html
    chmod 644 $PROXY_DIR/.htaccess
    chmod 644 $PROXY_DIR/php_settings.ini
    chmod 755 $PROXY_DIR/logs
    chmod 666 $PROXY_DIR/logs/proxy_log.txt
    chmod 666 $PROXY_DIR/logs/proxy_stats.json
    
    print_success "Proxy files downloaded successfully from GitHub"
}

# Function to configure SSL
configure_ssl() {
    print_status "Configuring SSL certificate..."
    
    # Get SSL certificate
    certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email $EMAIL --quiet
    
    # Setup auto-renewal
    echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
    
    print_success "SSL configured successfully"
}

# Function to configure firewall
configure_firewall() {
    print_status "Configuring firewall..."
    
    # Configure UFW
    ufw default deny incoming
    ufw default allow outgoing
    ufw allow ssh
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw --force enable
    
    # Configure Fail2ban
    cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 3

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
port = http,https
logpath = /var/log/nginx/access.log
maxretry = 2
EOF

    systemctl enable fail2ban
    systemctl start fail2ban
    
    print_success "Firewall configured successfully"
}

# Function to optimize system
optimize_system() {
    print_status "Optimizing system for unlimited bandwidth..."
    
    # Network optimizations
    cat >> /etc/sysctl.conf << 'EOF'
# TCP optimizations for unlimited bandwidth
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 87380 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.ipv4.tcp_congestion_control = bbr
net.core.default_qdisc = fq
net.core.netdev_max_backlog = 5000
net.ipv4.tcp_max_syn_backlog = 2048
EOF

    # Apply sysctl settings
    sysctl -p
    
    # Nginx optimizations
    cat >> /etc/nginx/nginx.conf << 'EOF'
# Worker settings
worker_processes auto;
worker_rlimit_nofile 65536;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    # Buffer settings
    client_body_buffer_size 128k;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
    
    # Timeout settings
    client_body_timeout 300s;
    client_header_timeout 300s;
    keepalive_timeout 65;
    send_timeout 300s;
    
    # Sendfile settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    
    # Gzip settings
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
}
EOF

    print_success "System optimized successfully"
}

# Function to create monitoring script
create_monitoring() {
    print_status "Creating monitoring script..."
    
    cat > /usr/local/bin/monitor-proxy.sh << 'EOF'
#!/bin/bash
echo "=== Auto-Link-Proxy Status Report $(date) ==="
echo "CPU Usage: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo "Memory Usage: $(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')"
echo "Disk Usage: $(df -h | grep '/dev/vda1' | awk '{print $5}')"
echo "Active Connections: $(netstat -an | grep :80 | wc -l)"
echo "Nginx Status: $(systemctl is-active nginx)"
echo "PHP-FPM Status: $(systemctl is-active php8.1-fpm)"
echo "SSL Certificate: $(certbot certificates | grep -c 'VALID')"
echo "Firewall Status: $(ufw status | grep -c 'active')"
echo ""
echo "Recent Proxy Logs:"
tail -5 /var/www/proxy/logs/proxy_log.txt 2>/dev/null || echo "No logs found"
echo ""
echo "Recent Nginx Errors:"
tail -5 /var/log/nginx/error.log 2>/dev/null || echo "No errors found"
EOF

    chmod +x /usr/local/bin/monitor-proxy.sh
    
    print_success "Monitoring script created"
}

# Function to start services
start_services() {
    print_status "Starting services..."
    
    systemctl enable php8.1-fpm
    systemctl start php8.1-fpm
    
    systemctl enable nginx
    systemctl start nginx
    
    print_success "Services started successfully"
}

# Function to test deployment
test_deployment() {
    print_status "Testing deployment..."
    
    sleep 5  # Wait for services to fully start
    
    # Test HTTP
    if curl -s -I http://$DOMAIN/proxy.php | grep -q "200 OK"; then
        print_success "HTTP test passed"
    else
        print_warning "HTTP test failed - check nginx configuration"
    fi
    
    # Test proxy functionality with a simple test
    if curl -s "http://$DOMAIN/proxy.php?url=https://httpbin.org/status/200" | grep -q "200"; then
        print_success "Proxy functionality test passed"
    else
        print_warning "Proxy functionality test failed - check proxy.php configuration"
    fi
    
    # Test test page
    if curl -s -I http://$DOMAIN/test_proxy.html | grep -q "200 OK"; then
        print_success "Test page accessible"
    else
        print_warning "Test page not accessible"
    fi
}

# Function to show final information
show_final_info() {
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}    Auto-Link-Proxy Deployment Complete!     ${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "${BLUE}Important URLs:${NC}"
    echo -e "  Proxy: https://$DOMAIN/proxy.php"
    echo -e "  Test Page: https://$DOMAIN/test_proxy.html"
    echo ""
    echo -e "${BLUE}Example Usage:${NC}"
    echo -e "  Method 1: https://$DOMAIN/proxy.php?url=https://sv1.cinetory.space/video.mp4"
    echo -e "  Method 2: https://$DOMAIN/proxy.php/path/to/video.mp4"
    echo ""
    echo -e "${BLUE}Useful Commands:${NC}"
    echo -e "  Monitor: /usr/local/bin/monitor-proxy.sh"
    echo -e "  Logs: tail -f /var/www/proxy/logs/proxy_log.txt"
    echo -e "  Status: systemctl status nginx php8.1-fpm"
    echo -e "  Firewall: ufw status"
    echo ""
    echo -e "${YELLOW}Next Steps:${NC}"
    echo -e "  1. Update config.php with your specific settings"
    echo -e "  2. Test with your actual video URLs from sv1.cinetory.space"
    echo -e "  3. Monitor performance with /usr/local/bin/monitor-proxy.sh"
    echo -e "  4. Consider installing WordPress plugin if needed"
    echo ""
    echo -e "${BLUE}GitHub Repository:${NC}"
    echo -e "  $GITHUB_REPO"
    echo ""
    echo -e "${GREEN}Deployment completed successfully!${NC}"
}

# Function to show configuration instructions
show_config_instructions() {
    echo ""
    echo -e "${YELLOW}Configuration Instructions:${NC}"
    echo -e "1. Edit the script variables at the top:"
    echo -e "   - DOMAIN: Your domain name"
    echo -e "   - EMAIL: Your email for SSL certificate"
    echo -e ""
    echo -e "2. Update config.php with your settings:"
    echo -e "   - ALLOWED_HOSTS: Add your source domains"
    echo -e "   - LOG_ENABLED: Set to true for logging"
    echo -e "   - MAX_FILE_SIZE: Adjust file size limit"
    echo -e ""
    echo -e "3. Test your proxy with:"
    echo -e "   curl 'https://$DOMAIN/proxy.php?url=https://sv1.cinetory.space/test.mp4'"
}

# Main deployment function
main() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}    Auto-Link-Proxy Auto-Deployment Script   ${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}GitHub: $GITHUB_REPO${NC}"
    echo ""
    
    # Check if configuration is updated
    if [ "$DOMAIN" = "filmkhabar.space" ] || [ "$EMAIL" = "your-email@example.com" ]; then
        print_warning "Please update DOMAIN and EMAIL variables in the script before running!"
        show_config_instructions
        exit 1
    fi
    
    check_root
    check_system
    update_system
    install_packages
    configure_nginx
    configure_php
    download_files
    configure_ssl
    configure_firewall
    optimize_system
    create_monitoring
    start_services
    test_deployment
    show_final_info
}

# Run main function
main "$@" 